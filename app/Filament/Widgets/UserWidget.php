<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UserWidget extends Widget
{
    protected static string $view = 'filament.widgets.user-widget';

    protected int | string | array $columnSpan = 'full';
}
