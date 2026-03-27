<?php

namespace Flute\Modules\FaceitInfo\Package\Screens;

use Flute\Admin\Platform\Actions\Button;
use Flute\Admin\Platform\Fields\Input;
use Flute\Admin\Platform\Fields\Select;
use Flute\Admin\Platform\Layouts\LayoutFactory;
use Flute\Admin\Platform\Screen;
use Flute\Core\Database\Entities\ApiKey;

class FaceitInfoScreen extends Screen
{
    public ?string $name = null;

    public ?string $description = null;

    public ?string $permission = 'admin.boss';

    public $apiKeys;

    public function mount(): void
    {
        $this->name = __('faceitinfo.title.list');
        $this->description = __('faceitinfo.title.description');

        breadcrumb()->add(__('def.admin_panel'), url('/admin'))->add(__('faceitinfo.title.list'));

        $this->apiKeys = rep(ApiKey::class)->select();
    }

    public function commandBar(): array
    {
        return [
            Button::make(__('faceitinfo.buttons.save'))->method('save'),
        ];
    }

    public function layout(): array
    {
        return [
            LayoutFactory::blank([
                LayoutFactory::block([
                    LayoutFactory::columns([
                        LayoutFactory::field(
                            Input::make('api_key')
                                ->value(config('faceit.api_key'))
                                ->placeholder(__('faceitinfo.placeholders.api_key')),
                        )
                            ->label(__('faceitinfo.labels.api_key'))
                            ->popover(__('faceitinfo.popovers.api_key')),

                        LayoutFactory::field(
                            Select::make('game')
                                ->options([
                                    'cs2' => 'CS2',
                                    'csgo' => 'CS:GO',
                                    'dota2' => 'Dota 2',
                                    'lol' => 'League of Legends',
                                ])
                                ->aligned()
                                ->value(config('faceit.game'))
                                ->placeholder(__('faceitinfo.placeholders.game')),
                        )
                            ->label(__('faceitinfo.labels.game'))
                            ->popover(__('faceitinfo.popovers.game')),
                    ]),
                ]),
            ]),
        ];
    }

    public function save(): void
    {
        $config = config('faceit');

        $config['api_key'] = request()->input('api_key');
        $config['game'] = request()->input('game');

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";

        $path = path('app/Modules/FaceitInfo/Resources/config/faceit.php');

        if (file_put_contents($path, $content) === false) {
            $this->flashMessage(__('faceitinfo.messages.failed'), 'error');

            return;
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }

        $this->flashMessage(__('faceitinfo.messages.saved'), 'success');
    }
}
