<?php

class Commands extends \CTask\BaseCommands
{
    function getAppInfo()
    {
        return array('Test Commands', '1.0.1');
    }

    /**
     * Super awesome test command.
     * @return int
     */
    public function testCommand()
    {
        $this->say("Hi mom!");

        $answer = $this->ask("What is 3 + 3?");

        $this->say($answer);

        $this->copyDir(array('a' => 'b'))->run();

        $this->write('test.txt')->line('hello')->run();

        return 0;
    }
}

?>