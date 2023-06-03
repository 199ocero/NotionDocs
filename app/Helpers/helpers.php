<?php

use App\Models\Team;
use App\Models\Member;
use App\Models\Settings;


if (! function_exists('snakeCase')) {
    function snakeCase($string)
    {
        $lowercase = strtolower($string);
        $snakeCase = str_replace(' ', '_', $lowercase);
        return $snakeCase;
    }
}

if(!function_exists('generateUrl')){
    function generateUrl($base_url, $version, $endpoint)
    {  
        return rtrim($base_url, '/') . '/' . trim($version, '/') . '/' . trim($endpoint, '/');
    }
}

if(!function_exists('getTeam')){
    function getTeam()
    {  
        if(auth()->user()->hasRole('collaborator')){
            $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
            $team = Team::where('user_id', $member->invited_by_id)->first();
        }else{
            $team = Team::where('user_id', auth()->user()->id)->first();
        }

        return $team;
    }
}

if(!function_exists('getHeaders')){
    function getHeaders()
    {  
        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();

        if($member && auth()->user()->hasRole('collaborator')){
            $team = Team::where('user_id', $member->invited_by_id)->first();
            $headers = Settings::where('team_id', $team->id ?? 0)->first();
        }else{
            $team = Team::where('user_id', auth()->user()->id)->first();
            $headers = Settings::where('team_id', $team->id ?? 0)->first();
        }

        return $headers;
    }
}