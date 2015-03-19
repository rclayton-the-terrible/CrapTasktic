<?php

namespace CTask\Communicators;

use CTask\Communicator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DefaultCommunicator implements Communicator
{
    /**
     * @var InputFactory
     */
    private $inputFactory;

    /**
     * @var OutputFactory
     */
    private $outputFactory;

    /**
     * Instantiate with the correct factories for providing the Input and Output interfaces.
     * @param InputFactory $inputFactory
     * @param OutputFactory $outputFactory
     */
    function __construct(InputFactory $inputFactory, OutputFactory $outputFactory)
    {
        $this->inputFactory = $inputFactory;
        $this->outputFactory = $outputFactory;
    }

    /**
     * Output an un-formatted message.
     * @param $message
     */
    public function write($message)
    {
        $this->getOutput()->writeln($message);
    }

    /**
     * Tell the user something
     * @param $message
     * @return mixed
     */
    public function say($message)
    {
        $char = strncasecmp(PHP_OS, 'WIN', 3) == 0 ? '>' : '➜';
        $this->getOutput()->writeln("$char  $message");
    }

    /**
     * Very-excitedly tell the user something.
     * @param $message
     * @param int $length
     * @return mixed
     */
    public function yell($message, $length = 40)
    {
        $char = strncasecmp(PHP_OS, 'WIN', 3) == 0 ? ' ' : '➜';
        $format = "$char  <fg=white;bg=green;options=bold>%s</fg=white;bg=green;options=bold>";
        $text = str_pad($message, $length, ' ', STR_PAD_BOTH);
        $len = strlen($text) + 2;
        $space = str_repeat(' ', $len);
        $this->getOutput()->writeln(sprintf($format, $space));
        $this->getOutput()->writeln(sprintf($format, " $text "));
        $this->getOutput()->writeln(sprintf($format, $space));
    }

    /**
     * Ask the user a question.
     * @param $question
     * @return mixed
     */
    public function ask($question)
    {
        return $this->doAsk(new Question($this->formatQuestion($question)));
    }

    /**
     * Ask the user a question hide the answer on the input device.
     * @param $question
     * @return mixed
     */
    public function askHidden($question)
    {
        $question = new Question($this->formatQuestion($question));
        $question->setHidden(true);
        return $this->doAsk($question);
    }

    /**
     * Ask the user a question providing a default answer.
     * @param $question
     * @param $default
     * @return mixed
     */
    public function askDefault($question, $default)
    {
        return $this->doAsk(new Question($this->formatQuestion("$question [$default]"), $default));
    }

    /**
     * Have the user confirm an action.
     * @param $question
     * @return mixed
     */
    public function confirm($question)
    {
        return $this->doAsk(new ConfirmationQuestion($this->formatQuestion($question . ' (y/n)'), false));
    }

    private function formatQuestion($message)
    {
        return  "<question>?  $message</question> ";
    }

    private function doAsk(Question $question)
    {
        return $this->getDialog()->ask($this->getInput(), $this->getOutput(), $question);
    }

    protected function getDialog()
    {
        return new QuestionHelper();
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->inputFactory->getInput();
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->outputFactory->getOutput();
    }


    public static function getInstance()
    {
        return new DefaultCommunicator(new ArgvInputFactory(), new ConsoleOutputFactory());
    }
}

?>