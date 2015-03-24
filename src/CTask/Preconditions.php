<?php

namespace CTask;

use CTask\Exceptions\IllegalStateException;

class Preconditions
{
    public static function assertClassExists($var)
    {
        if (!is_string($var) || !class_exists($var))
            throw new IllegalStateException('Class ' . print_r($var, true). ' does not exist.');
    }

    public static function assertNumeric($var)
    {
        if (!is_numeric($var))
            throw new IllegalStateException('Value ' . print_r($var, true). ' should be a number, but is not.');
    }

    public static function assertArrayContainsKey($array, $key)
    {
        if (!array_key_exists($key, $array))
            throw new IllegalStateException("Key $key does not exist in the array.");
    }

    public static function assert($result, $message = 'Expected condition not met.')
    {
        if (!$result) throw new IllegalStateException($message);
    }

    public static function assertNotNull($argument, $message = 'Argument must not be null.')
    {
        if ($argument === null)  throw new IllegalStateException($message);
    }
}

?>