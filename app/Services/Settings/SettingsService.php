<?php

namespace App\Services\Settings;

use App\Repositories\Settings\SettingsRepository;

class SettingsService
{
    public function saveSettings($result)
    {
        $settings = new SettingsRepository;
        return $settings->saveSettings($result);
    }
}
