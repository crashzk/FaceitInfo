<?php

use Flute\Core\Router\Router;
use Flute\Modules\FaceitInfo\Package\Screens\FaceitInfoScreen;

Router::screen('/admin/faceit-info', FaceitInfoScreen::class);
