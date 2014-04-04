<?php
// src/KK/JobeetBundle/Utils/Jobeet.php

namespace KK\JobeetBundle\Utils;

class Jobeet
{
    static public function slugify($text)
    {
        // Replace all non letters or digits by -
        $text = preg_replace('/\W+/', '-', $text);

        // Trim and lowercase
        $text = strtolower(trim($text, '-'));

//        // Transliterate
//        if (function_exists('iconv')) {
//            $text = iconv('utf-8', 'us-ascii/TRANSLIT', $text);
//        }

        // Replace empty string with n-a
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}