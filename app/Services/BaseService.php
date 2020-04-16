<?php

namespace App\Services;

class BaseService
{
    protected function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    protected function removeTrailingCharacters($text)
    {
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
