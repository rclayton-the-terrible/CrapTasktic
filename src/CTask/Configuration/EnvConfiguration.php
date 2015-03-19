<?php

namespace CTask\Configuration;


use CTask\Configuration;

class EnvConfiguration implements Configuration
{
    public function has($key)
    {
        return getenv($key) !== null;
    }

    public function get($key, $default)
    {
        if ($this->has($key)) return getenv($key);
        return $default;
    }

    public function set($key, $value)
    {
        putenv("$key=" . print_r($value, true));
    }
}

?>