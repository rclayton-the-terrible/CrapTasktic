<?php

namespace CTask;

use CTask\Tasks\Tasks;

/**
 * You need to extend this dude if with your "Commands" class, where each
 * method is a "Command" that can be executed.
 *
 * Class BaseCommands
 * @package CTask
 */
class BaseCommands
{
    const CT_VERSION = "0.0.1";

    /**
     * @var Communicator
     */
    protected $communicator;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Tasks
     */
    protected $task;

    /**
     * Output an un-formatted message.
     * @param $message
     */
    protected function writeln($message)
    {
        $this->communicator->write($message);
    }

    /**
     * Tell the user something
     * @param $message
     */
    protected function say($message)
    {
        $this->communicator->say($message);
    }

    /**
     * Very-excitedly tell the user something.
     * @param $message
     * @param int $length
     */
    protected function yell($message, $length = 40)
    {
        $this->communicator->yell($message, $length);
    }

    /**
     * Ask the user a question.
     * @param $question
     * @return mixed
     */
    protected function ask($question)
    {
        return $this->communicator->ask($question);
    }

    /**
     * Ask the user a question hide the answer on the input device.
     * @param $question
     * @return mixed
     */
    protected function askHidden($question)
    {
        return $this->communicator->askHidden($question);
    }

    /**
     * Ask the user a question providing a default answer.
     * @param $question
     * @param $default
     * @return mixed
     */
    protected function askDefault($question, $default)
    {
        return $this->communicator->askDefault($question, $default);
    }

    /**
     * Have the user confirm an action.
     * @param $question
     * @return mixed
     */
    protected function confirm($question)
    {
        return $this->communicator->confirm($question);
    }

    /**
     * Get the application name and version.  Override this
     * to get a custom name and version.
     * @return array [name, version]
     */
    function getAppInfo()
    {
        return array('CrapTasktic', self::CT_VERSION);
    }

    /**
     *
     * @param Configuration $configuration
     * @param Communicator $communicator
     */
    function init(Configuration $configuration, Communicator $communicator)
    {
        $this->configuration = $configuration;
        $this->communicator = $communicator;
        $this->task = new Tasks($this->configuration, $this->communicator);
    }
}