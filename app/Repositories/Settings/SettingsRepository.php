<?php

namespace App\Repositories\Settings;

use App\Models\Settings;

class SettingsRepository
{
    public function saveSettings($result)
    {
        $settings = Settings::first();

        if ($settings) {
            $settings->update([
                'base_url' => $result['base_url'],
                'version' => $result['version'],
                'headers' => $result['headers'],
            ]);

            return true;
        } else {
            Settings::create([
                'base_url' => $result['base_url'],
                'version' => $result['version'],
                'headers' => $result['headers'],
            ]);

            return true;
        }

        return false;
    }

}
