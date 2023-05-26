<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class UserWidget extends Widget
{
    use HasWidgetShield;
    
    protected static string $view = 'filament.widgets.user-widget';

    protected int | string | array $columnSpan = 'full';
}
