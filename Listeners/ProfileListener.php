<?php

namespace Flute\Modules\FaceitInfo\Listeners;

use Exception;
use Flute\Core\Modules\Profile\Events\ProfileRenderEvent;
use Flute\Modules\FaceitInfo\Services\FaceitInfo;

class ProfileListener
{
    public static function handle(ProfileRenderEvent $event)
    {
        if ($event->getType() === 'mini') {
            return;
        }

        $steam = $event->getUser()->getSocialNetwork('Steam');
        if (!$steam || empty($steam->value)) {
            return;
        }

        $steam64 = (string) $steam->value;

        try {
            $cacheKey = 'faceit.render.' . $steam64;
            $cached = cache()->get($cacheKey);

            if (is_array($cached)) {
                if (!empty($cached['faceitInfo'])) {
                    template()->prependTemplateToSection('profile_sidebar', 'faceit-info::index', [
                        'faceitInfo' => $cached['faceitInfo'],
                        'playerStats' => $cached['playerStats'] ?? [],
                    ]);
                }

                return;
            }

            $api = app(FaceitInfo::class);
            $faceitInfo = $api->getFaceitInfo($steam64);

            if (!empty($faceitInfo) && !empty($faceitInfo['player_id'])) {
                $game = config('faceit.game', 'cs2');
                $playerStats = $api->getPlayerStats($faceitInfo['player_id'], $game);

                template()->prependTemplateToSection('profile_sidebar', 'faceit-info::index', [
                    'faceitInfo' => $faceitInfo,
                    'playerStats' => $playerStats,
                ]);

                cache()->set($cacheKey, ['faceitInfo' => $faceitInfo, 'playerStats' => $playerStats], 600);
            } else {
                cache()->set($cacheKey, ['faceitInfo' => [], 'playerStats' => []], 600);
            }
        } catch (Exception $e) {
            logs('modules')->error($e);

            if (is_debug()) {
                throw $e;
            }
        }
    }
}
