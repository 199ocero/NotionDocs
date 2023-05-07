<?php


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