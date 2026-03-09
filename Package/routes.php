<?php

use Flute\Modules\FaceitInfo\Package\Screens\FaceitInfoScreen;
use Flute\Core\Router\Router;

Router::screen('/admin/faceit-info', FaceitInfoScreen::class);
