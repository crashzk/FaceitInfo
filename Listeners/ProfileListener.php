<?php

namespace Flute\Modules\FaceitInfo\Listeners;

use Flute\Core\Modules\Profile\Events\ProfileRenderEvent;
use Flute\Modules\FaceitInfo\Services\FaceitInfo;

class ProfileListener
{
    public static function handle(ProfileRenderEvent $event)
    {
        if ($event->getType() === 'mini') {
            return;
        }

        if ($steam = $event->getUser()->getSocialNetwork('Steam')) {
            try {
                $api = app(FaceitInfo::class);
                $faceitInfo = $api->getFaceitInfo((int) $steam->value);

                if (!empty($faceitInfo)) {
                    $playerStats = $api->getPlayerStats($faceitInfo['player_id']);

                    template()->prependToSection('profile_sidebar', render('faceit-info::index', [
                        'faceitInfo' => $faceitInfo,
                        'playerStats' => $playerStats
                    ]));
                }
            } catch (\Exception $e) {
                logs('modules')->error($e);

                if(is_debug()) {
                    throw $e;
                }
            }
        }
    }
}
