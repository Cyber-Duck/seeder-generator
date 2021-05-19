<?php

namespace CyberDuck\Seeder;

class Str
{
    /**
     * Convert camel cases, underscore and hyphen separated strings to human format.
     * Taken from: https://github.com/StydeNet/html/blob/master/src/Str.php
     *
     * @param $string
     * @return string
     */
    public static function readable($string)
    {
        $stringr = $string[0].preg_replace('@[_-]|([A-Z])@', ' $1', substr($string, 1));
        return ucfirst(strtolower($stringr));
    }
}
