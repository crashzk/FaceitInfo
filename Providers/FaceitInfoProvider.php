<?php

namespace Flute\Modules\FaceitInfo\Providers;

use Flute\Core\Modules\Profile\Events\ProfileRenderEvent;
use Flute\Core\Support\ModuleServiceProvider;
use Flute\Modules\FaceitInfo\Listeners\ProfileListener;
use Flute\Modules\FaceitInfo\Package\FaceitInfoPackage;

class FaceitInfoProvider extends ModuleServiceProvider
{
    public array $extensions = [];

    public function boot(\DI\Container $container): void
    {
        $this->loadConfigs();

        $this->loadTranslations();

        $this->loadPackage(new FaceitInfoPackage());

        if (!config('faceit.api_key') || !config('faceit.game')) {
            return;
        }

        $this->bootstrapModule();

        $this->loadViews('Resources/views', 'faceit-info');

        $this->loadScss('Resources/assets/scss/main.scss');

        events()->addListener(ProfileRenderEvent::NAME, [ProfileListener::class, 'handle']);
    }

    public function register(\DI\Container $container): void
    {
    }
}
