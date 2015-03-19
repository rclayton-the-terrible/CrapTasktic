<?php

namespace CTask;

class Result
{
    const SUCCESS = 0;
    const FAIL = 1;

    private $exit;
    private $data;
    private $error;

    function __construct($exit, $data, $error)
    {
        Preconditions::assertNumeric($exit);

        $this->exit = $exit;
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getExit()
    {
        return $this->exit;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    public function hasError()
    {
        return $this->$exit > 0;
    }

    public function deconstruct()
    {
        return array($this->exit, $this->data, $this->error);
    }

    public static function success($data = null)
    {
        return new Result(self::SUCCESS, $data, null);
    }

    public static function error($error)
    {
        return new Result(self::FAIL, null, $error);
    }
}

?>