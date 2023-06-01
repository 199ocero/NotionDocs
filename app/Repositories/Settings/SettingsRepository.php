<?php

namespace App\Repositories\Settings;

use App\Models\Team;
use App\Models\Settings;

class SettingsRepository
{
    public function saveSettings($result)
    {
        $team = Team::where('user_id', auth()->user()->id)->first();
        $settings = Settings::where('team_id', $team->id ?? 0)->first();

        if ($settings) {
            $settings->update([
                'team_id' => $team->id,
                'base_url' => $result['base_url'],
                'version' => $result['version'],
                'headers' => $result['headers'],
            ]);

            return true;
        } else {
            Settings::create([
                'team_id' => $team->id,
                'base_url' => $result['base_url'],
                'version' => $result['version'],
                'headers' => $result['headers'],
            ]);

            return true;
        }
    }

}
