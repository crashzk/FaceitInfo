<?php

namespace Flute\Modules\FaceitInfo\Package;

use Flute\Admin\Support\AbstractAdminPackage;

class FaceitInfoPackage extends AbstractAdminPackage
{
    /**
     * {@inheritdoc}
     */
    public function initialize() : void
    {
        parent::initialize();

        $this->loadRoutesFromFile('routes.php');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions() : array
    {
        return ['admin', 'admin.boss'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuItems() : array
    {
        return [
            [
                'title' => __('faceitinfo.title.list'),
                'icon' => 'ph.bold.key-bold',
                'url' => url('/admin/faceit-info'),
            ],
        ];
    }

    public function getPriority(): int
    {
        return 110;
    }
}
