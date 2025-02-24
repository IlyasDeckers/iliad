<?php

if (!function_exists('decimalTime')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string $key
     * @param  mixed $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function decimalTime($value)
    {
        $hrs = intval($value);
        $min = round(($value - $hrs) * 60);
        $hrs = str_pad($hrs, 2, '0', STR_PAD_LEFT);
        $min = str_pad($min, 2, '0', STR_PAD_LEFT);

        return $hrs . ':' . $min;
    }
}
if (!function_exists('ddd')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     */
    function ddd(...$args)
    {
        http_response_code(500);
        call_user_func_array('dd', $args);
    }
}
if (!function_exists('carbon')) {

    /**
     * @param $value
     * @return \Illuminate\Support\Carbon
     */
    function carbon($value)
    {
        return \Illuminate\Support\Carbon::parse($value);
    }
}
if (!function_exists('format')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     */
    function format($value, $format = 'Y-m-d')
    {
        return $value instanceof \Carbon\Carbon ? $value->format($format) : \Illuminate\Support\Carbon::parse($value)->format($format);
    }
}

if (!function_exists('timeToDecimal')) {
    function timeToDecimal($value)
    {
        $hm = explode(":", $value);
        return ($hm[0] + ($hm[1] / 60));
    }
}

if (!function_exists('generatePassword')) {
    function generatePassword()
    {
        return substr(
            str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*+?"), 0, 8 
        );
    }
}

