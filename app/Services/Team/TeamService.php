<?php

namespace App\Services\Team;

use App\Repositories\Team\TeamRepository;

class TeamService
{
    public function saveTeam($result)
    {
        $team = new TeamRepository;
        return $team->saveTeam($result);
    }
}
