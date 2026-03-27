<?php

namespace Flute\Modules\FaceitInfo\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class FaceitInfo
{
    protected const CACHE_TIME = 86400;

    protected const CACHE_KEY = 'faceit_info_';

    protected const API_BASE = 'https://open.faceit.com/data/v4';

    protected const REQUEST_TIMEOUT = 5;

    protected const CONNECT_TIMEOUT = 3;

    protected const CONCURRENCY = 5;

    protected ?Client $client = null;

    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client([
                'base_uri' => self::API_BASE,
                'timeout' => self::REQUEST_TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'headers' => [
                    'Authorization' => 'Bearer ' . config('faceit.api_key'),
                    'Accept' => 'application/json',
                ],
                'http_errors' => true,
            ]);
        }

        return $this->client;
    }

    /**
     * @param string $steamid Steam64 ID as string
     */
    public function getFaceitInfo(string $steamid): array
    {
        $cacheKey = self::CACHE_KEY . $steamid;

        $cached = cache()->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $game = config('faceit.game', 'cs2');

        try {
            $response = $this->getClient()->get('/data/v4/players', [
                'query' => [
                    'game' => $game,
                    'game_player_id' => $steamid,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true) ?: [];

            cache()->set($cacheKey, $data, self::CACHE_TIME);

            return $data;
        } catch (Exception $e) {
            if (!$this->is404($e)) {
                logs('modules')->warning("Faceit API error for steam {$steamid}: " . $e->getMessage());
            }

            cache()->set($cacheKey, [], 3600);

            return [];
        }
    }

    /**
     * Fetch Faceit info for multiple Steam64 IDs concurrently.
     * Returns array keyed by steam64 => faceit data.
     *
     * @param string[] $steam64Ids
     */
    public function getBulkFaceitInfo(array $steam64Ids): array
    {
        if (empty($steam64Ids)) {
            return [];
        }

        $result = [];
        $uncached = [];

        foreach ($steam64Ids as $steam64) {
            $cacheKey = self::CACHE_KEY . $steam64;
            $cached = cache()->get($cacheKey);

            if ($cached !== null) {
                $result[$steam64] = $cached;
            } else {
                $uncached[] = $steam64;
            }
        }

        if (empty($uncached)) {
            return $result;
        }

        $game = config('faceit.game', 'cs2');
        $client = $this->getClient();

        $requests = static function () use ($uncached, $game) {
            foreach ($uncached as $steam64) {
                yield $steam64 => new Request(
                    'GET',
                    self::API_BASE . '/players?' . http_build_query([
                        'game' => $game,
                        'game_player_id' => $steam64,
                    ]),
                );
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => self::CONCURRENCY,
            'fulfilled' => function (Response $response, $steam64) use (&$result) {
                $data = json_decode($response->getBody()->getContents(), true) ?: [];
                $result[$steam64] = $data;
                cache()->set(self::CACHE_KEY . $steam64, $data, self::CACHE_TIME);
            },
            'rejected' => function ($reason, $steam64) use (&$result) {
                if (!$this->is404($reason)) {
                    logs('modules')->warning("Faceit API error for steam {$steam64}: " . $reason->getMessage());
                }
                $result[$steam64] = [];
                cache()->set(self::CACHE_KEY . $steam64, [], 3600);
            },
        ]);

        $pool->promise()->wait();

        return $result;
    }

    /**
     * @param string $playerId Faceit player ID
     * @param string|null $game Game ID (null = use config)
     */
    public function getPlayerStats(string $playerId, ?string $game = null): array
    {
        $game ??= config('faceit.game', 'cs2');
        $cacheKey = self::CACHE_KEY . "stats_{$playerId}_{$game}";

        $cached = cache()->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = $this->getClient()->get("/data/v4/players/{$playerId}/stats/{$game}");

            $data = json_decode($response->getBody()->getContents(), true) ?: [];

            cache()->set($cacheKey, $data, self::CACHE_TIME);

            return $data;
        } catch (Exception $e) {
            if (!$this->is404($e)) {
                logs('modules')->warning("Faceit stats API error for player {$playerId}: " . $e->getMessage());
            }

            cache()->set($cacheKey, [], 3600);

            return [];
        }
    }

    protected function is404($exception): bool
    {
        return $exception instanceof \GuzzleHttp\Exception\ClientException
            && $exception->getResponse()
            && $exception->getResponse()->getStatusCode() === 404;
    }
}
