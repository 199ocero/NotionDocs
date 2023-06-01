<?php

use App\Models\Team;
use App\Models\Member;


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
        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
        return Team::where('user_id', $member->invited_by_id)->first();
    }
}