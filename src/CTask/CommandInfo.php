<?php


/**
The MIT License (MIT)

Copyright (c) 2014 Codegyre developers team

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace CTask;

class CommandInfo
{
    const PARAM_IS_REQUIRED = '__param_is_required__';

    protected static $annotationRegex = '/@%s(?:[ \t]+(.*?))?[ \t]*\r?$/m';

    /**
     * @var \ReflectionMethod
     */
    protected $reflection;

    /**
     * @var array
     */
    protected $parsedDocBlock;

    public function __construct($className, $methodName)
    {
        $this->reflection = new \ReflectionMethod($className, $methodName);
    }

    public function getDescription()
    {
        $desc = $this->getAnnotation('description');
        if (!$desc) {
            $desc = $this->getAnnotation('desc');
        }
        if (!$desc) {
            $parsed = $this->parseDocBlock();
            $desc = $parsed['description'];
        }
        return $desc;
    }

    public function getName()
    {
        $name = $this->getAnnotation('name');
        if (!$name) {
            $name = $this->reflection->getName();
        }
        $name = $this->convertName($name);
        return $name;
    }

    public function getArguments()
    {
        $args = array();
        $params = $this->reflection->getParameters();
        foreach ($params as $key => $param) {
            // last array value is option, not argument
            if (($key == count($params)-1) and $param->isDefaultValueAvailable()) {
                if ($this->isAssoc($param->getDefaultValue())) break;
            }
            // arrays are array arguments
            if ($param->isArray()) {
                if ($param->isDefaultValueAvailable()) {
                    if (!$this->isAssoc($param->getDefaultValue())) $args[$param->getName()] = $param->getDefaultValue();
                } else {
                    $args[$param->getName()] = array();
                }
                continue;
            }
            // default values are optional arguments
            $val = $param->isDefaultValueAvailable()
                ? $param->getDefaultValue()
                : self::PARAM_IS_REQUIRED;
            $args[$param->getName()] = $val;
        }
        return $args;
    }

    public function getOptions()
    {
        $params = $this->reflection->getParameters();
        if (empty($params)) return array();
        $param = end($params);
        if (!$param->isDefaultValueAvailable()) return array();
        if (!$this->isAssoc($param->getDefaultValue())) return array();
        return $param->getDefaultValue();
    }

    public function getHelp()
    {
        $parsed = $this->parseDocBlock();
        return $parsed['help'];
    }

    public function getArgumentDescription($name)
    {
        $parsed = $this->parseDocBlock();
        if (array_key_exists($name, $parsed['param'])) {
            return $parsed['param'][$name];
        }
        return '';
    }

    public function getOptionDescription($name)
    {
        $parsed = $this->parseDocBlock();
        if (array_key_exists($name, $parsed['option'])) {
            return $parsed['option'][$name];
        }
        return '';
    }

    protected function isAssoc($arr)
    {
        if (!is_array($arr)) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected function getAnnotation($annotation)
    {
        $docBlock = $this->reflection->getDocComment();
        $matched = array();
        $res = preg_match(sprintf(self::$annotationRegex, $annotation), $docBlock, $matched);
        if (!$res) return null;
        return $matched[1];
    }

    private function convertName($camel)
    {
        $splitter="-";
        $camel=preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $camel));
        $camel = preg_replace("/$splitter/", ':', $camel, 1);
        return strtolower($camel);
    }

    private function parseDocBlock()
    {
        if (!$this->parsedDocBlock) {
            $parsed = array(
                'description' => array(),
                'help' => array(),
                'param' => array(),
                'option' => array(),
            );
            $tag = '@(?P<tag>[^ \t]+)[ \t]+';
            $name = '\\$(?P<name>[^ \t]+)[ \t]+';
            $type = '(?P<type>[^ \t]+)[ \t]+';
            $description = '(?P<description>.*)';
            $isTag = '/^\*[* \t]+@/';
            $option = "/{$tag}{$name}{$description}/";
            $argument1 = "/{$tag}{$type}{$name}{$description}/";
            $argument2 = "/{$tag}{$name}{$type}{$description}/";
            $null = array();
            $doc = $this->reflection->getDocComment();
            if ($doc) {
                $current =& $parsed['description'];
                foreach (explode("\n", $doc) as $row) {
                    $row = trim($row);
                    if ($row == '/**' || $row == '*/') {
                        continue;
                    }
                    // @option definitions
                    if (stripos($row, '@option') !== false && preg_match($option, $row, $match)) {
                        $parsed[$match['tag']][$match['name']] = array($match['description']);
                        $current =& $parsed[$match['tag']][$match['name']];
                    }
                    // @param definitions where type is specified before the variable name
                    elseif (stripos($row, '@param') !== false && preg_match($argument1, $row, $match)) {
                        $parsed[$match['tag']][$match['name']] = array($match['description']);
                        $current =& $parsed[$match['tag']][$match['name']];
                    }
                    // @param definitions where type is specified after the variable name
                    elseif (stripos($row, '@param') !== false && preg_match($argument2, $row, $match)) {
                        $parsed[$match['tag']][$match['name']] = array($match['description']);
                        $current =& $parsed[$match['tag']][$match['name']];
                    }
                    // If no tag is defined is it treated as part of the last definition
                    elseif (!preg_match($isTag, $row)) {
                        $current[] = substr(trim($row, '*/'), 1);
                        if ($current === $parsed['description']) {
                            $current =& $parsed['help'];
                        }
                    }
                    // Anything else is discarded
                    else {
                        $current =& $null;
                    }
                }
            }
            $parsed['description'] = $this->combineParsedComment($parsed['description']);
            $parsed['help'] = trim($this->combineParsedComment($parsed['help'], true));
            foreach ($parsed['param'] as &$param) {
                $param = $this->combineParsedComment($param);
            }
            foreach ($parsed['option'] as &$option) {
                $option = $this->combineParsedComment($option);
            }
            if (empty($parsed['description'])) {
                $parsed['description'] = null;
            }
            if (empty($parsed['help'])) {
                $parsed['help'] = null;
            }
            $this->parsedDocBlock = $parsed;
        }
        return $this->parsedDocBlock;
    }

    private function combineParsedComment(array $doc, $keepFormatting = false)
    {
        if ($keepFormatting) {
            return implode(PHP_EOL, $doc);
        }
        return trim(implode(' ', array_filter(array_map('trim', $doc))));
    }
}