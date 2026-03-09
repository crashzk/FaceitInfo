<?php

namespace Flute\Modules\FaceitInfo\Services;

use GuzzleHttp\Client;

class FaceitInfo
{
    protected const CACHE_TIME = 60 * 60 * 24;
    protected const CACHE_KEY = 'faceit_info_';

    /**
     * Get faceit info
     *
     * @param int $steamid
     * @return array
     */
    public function getFaceitInfo(int $steamid): array
    {
        if (cache()->has(self::CACHE_KEY . $steamid)) {
            return cache()->get(self::CACHE_KEY . $steamid);
        }

        $url = "https://open.faceit.com/data/v4/players?game=" . config('faceit.game') . "&game_player_id={$steamid}&offset=0&limit=1";

        try {
            $client = new Client();

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('faceit.api_key')
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            cache()->set(self::CACHE_KEY . $steamid, $data, self::CACHE_TIME);

            return $data;
        } catch (\Exception $e) {
            logs('modules')->error($e);

            cache()->set(self::CACHE_KEY . $steamid, [], self::CACHE_TIME);

            return [];
        }
    }

    /**
     * Get player stats from Faceit API
     *
     * @param string $playerId Faceit player ID
     * @param string $game Game ID (default: cs2)
     * @return array
     */
    public function getPlayerStats(string $playerId, string $game = 'cs2'): array
    {
        $cacheKey = self::CACHE_KEY . "stats_{$playerId}_{$game}";

        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $url = "https://open.faceit.com/data/v4/players/{$playerId}/stats/{$game}";

        try {
            $client = new Client();

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('faceit.api_key')
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            cache()->set($cacheKey, $data, self::CACHE_TIME);

            return $data;
        } catch (\Exception $e) {
            logs('modules')->error($e);

            cache()->set($cacheKey, [], self::CACHE_TIME);

            return [];
        }
    }
}
