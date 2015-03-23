<?php

namespace CTask;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Essentially the IO trait of Robo.
 * Interface Communicator
 * @package CTask
 */
interface Communicator
{
    /******** DISPLAY MESSAGES ************************/

    /**
     * Output an un-formatted message.
     * @param $message
     */
    public function write($message);

    /**
     * Tell the user something
     * @param $message
     */
    public function say($message);

    /**
     * Very-excitedly tell the user something.
     * @param $message
     * @param int $length
     */
    public function yell($message, $length = 40);

    /******** INPUT-BASED ACTIONS *********************/

    /**
     * Ask the user a question.
     * @param $question
     * @return mixed
     */
    public function ask($question);

    /**
     * Ask the user a question hide the answer on the input device.
     * @param $question
     * @return mixed
     */
    public function askHidden($question);

    /**
     * Ask the user a question providing a default answer.
     * @param $question
     * @param $default
     * @return mixed
     */
    public function askDefault($question, $default);

    /**
     * Have the user confirm an action.
     * @param $question
     * @return mixed
     */
    public function confirm($question);

    /**
     * Return the raw output interface.
     * @return OutputInterface
     */
    public function getOutput();
}

?>