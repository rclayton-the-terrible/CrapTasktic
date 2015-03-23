<?php

namespace CTask\Tasks;


class ParamsHelper
{
    public static function callDelegate($that, $property, $args)
    {
        if (!property_exists($that, $property)) {
            throw new \RuntimeException("Property $property in task ".get_class($that).' does not exists');
        }
        // toggle boolean values
        if (!isset($args[0]) and (is_bool($that->$property))) {
            $that->$property = !$that->$property;
            return $that;
        }
        // append item to array
        if (is_array($that->$property)) {
            if (is_array($args[0])) {
                $that->$property = $args[0];
            } else {
                array_push($that->$property, $args[0]);
            }
            return $that;
        }
        $that->$property = $args[0];
        return $that;
    }
}