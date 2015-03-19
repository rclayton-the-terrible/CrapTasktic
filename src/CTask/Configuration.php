<?php

namespace CTask;


interface Configuration
{
    public function has($key);

    public function get($key, $default);

    public function set($key, $value);
}

?>