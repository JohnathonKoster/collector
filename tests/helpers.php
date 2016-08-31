<?php

if (!function_exists('normalize_line_endings')) {
    /**
     * Normalizes line endings.
     *
     * Adapted from
     * https://www.darklaunch.com/2009/05/06/php-normalize-newlines-line-endings-crlf-cr-lf-unix-windows-mac
     *
     * @param $string
     */
    function normalize_line_endings($string) {
        // Convert all line-endings to UNIX format
        $string = str_replace("\r\n", "\n", $string);
        $string = str_replace("\r", "\n", $string);
        // Don't allow out-of-control blank lines
        $string = preg_replace("/\n{2,}/", "\n" . "\n", $string);
        return $string;
    }
}