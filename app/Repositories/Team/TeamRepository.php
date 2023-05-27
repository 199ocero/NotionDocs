<?php

namespace App\Repositories\Team;

use App\Models\Team;

class TeamRepository
{
    public function saveTeam($result)
    {
        $team = Team::where('user_id', auth()->id())->first();

        if ($team) {
            $team->update([
                'name' => $result['name'],
                'description' => $result['description'],
            ]);

            return true;
        } else {
            Team::create([
                'user_id' => auth()->id(),
                'name' => $result['name'],
                'description' => $result['description'],
            ]);

            return true;
        }
    }
}
