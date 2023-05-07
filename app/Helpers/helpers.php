<?php


if (! function_exists('snakeCase')) {
    function snakeCase($string)
    {
        $lowercase = strtolower($string);
        $snakeCase = str_replace(' ', '_', $lowercase);
        return $snakeCase;
    }
}