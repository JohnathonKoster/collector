<?php

if (! function_exists('config')) {
    /**
     * Gets the value for the provided configuration key.
     *
     * @param  string
     * @return mixed
     */
    function config($key)
    {
        return Collector\Support\Config::getInstance()->get($key);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && mb_strpos($value, '"') === 0 && (mb_substr($value, -mb_strlen($value), null, 'UTF-8')) === $value) {
            return substr($value, 1, -1);

        }
        return $value;
    }
}