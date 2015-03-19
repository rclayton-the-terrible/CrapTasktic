<?php

namespace CTask;

use CTask\Communicators\DefaultCommunicator;
use CTask\Configuration\EnvConfiguration;
use CTask\Tasks\DefaultTaskRegistry;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Very similar to Robo's Runner.  In CrapTasktic, a "Command" represents the execution of a sequence
 * of tasks to accomplish some objective.  Tasks, on the other hand, represent the individual units in
 * the sequence.
 *
 * Also, I don't like the requirement of having to name the Commands file (RoboFile), nor having to use
 * a specific class name.  This is now configurable via the "run" function.
 *
 * Class Runner
 * @package CTask
 */
class Runner
{
    private static $INVALID_METHOD_NAMES = array('setCommunicator', 'setTaskRegistry', 'setConfiguration', 'getAppInfo');

    /**
     * @var TaskRegistry
     */
    private $taskRegistry;

    /**
     * @var Communicator
     */
    private $communicator;

    /**
     * @var Configuration
     */
    private $configuration;

    protected $currentDir = '.';
    protected $passThroughArgs = null;

    public function __construct(Configuration $configuration = null, Communicator $communicator = null, TaskRegistry $taskRegistry = null)
    {
        $this->configuration = ($configuration)? $configuration : new EnvConfiguration();
        $this->communicator = ($communicator)? $communicator : DefaultCommunicator::getInstance();
        $this->taskRegistry = ($taskRegistry)? $taskRegistry : new DefaultTaskRegistry();
    }

    public function run($input = null, $commandsClass = 'Commands', $commandsFile = null)
    {
        register_shutdown_function(array($this, 'shutdown'));

        $input = $this->prepareInput($input ? $input : $_SERVER['argv']);

        $this->requireCommands($commandsClass, $commandsFile);

        $app = $this->createApplication($commandsClass);

        $app->run($input);
    }

    protected function requireCommands($commandsClass, $commandsFile)
    {
        if ($commandsFile == null) $commandsFile = getcwd() . '/Commands.php';

        if (!file_exists($commandsFile)) {
            $this->communicator->say("<comment>  $commandsFile not found in this dir </comment>");
            exit;
        }

        require_once $commandsFile;

        if (!class_exists($commandsClass)) {
            $this->communicator->write("<error>Class $commandsClass was not loaded</error>");
            return false;
        }
        return true;
    }

    protected function prepareInput($argv)
    {
        $pos = array_search('--', $argv);
        if ($pos !== false) {
            $this->passThroughArgs = implode(' ', array_slice($argv, $pos+1));
            $argv = array_slice($argv, 0, $pos);
        }
        return $this->communicator->getInput($argv);
    }

    /**
     * Initialize an instance of the commands class.
     * @param $class ReflectionClass Instance of the Reflection class.
     * @return BaseCommands
     */
    protected function initializeCommandsClass(ReflectionClass $class)
    {
        Preconditions::assert($class->isSubclassOf('CTask\BaseCommands'));

        /**
         * @var $commands BaseCommands
         */
        $commands = $class->newInstance();

        $commands->setCommunicator($this->communicator);
        $commands->setConfiguration($this->configuration);
        $commands->setTaskRegistry($this->taskRegistry);

        return $commands;
    }

    /**
     * Get a list of command names from the public methods on the class.
     * @param ReflectionClass $class
     * @return String[] names of methods serving as commands.
     */
    protected function getCommandMethodNames(ReflectionClass $class)
    {
        $commands = array();

        foreach($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            if (!$method->isConstructor()
                && !in_array($method->getName(), self::$INVALID_METHOD_NAMES)
                && strpos($method->getName(), '__') !== 0)
                $commands[] = $method->getName();
        }

        return $commands;
    }

    public function createApplication($className)
    {
        $class = new \ReflectionClass($className);

        $commands = $this->initializeCommandsClass($class);

        list($name, $version) = $commands->getAppInfo();

        $app = new Application($name, $version);

        $commandNames = $this->getCommandMethodNames($class);

        $passThrough = $this->passThroughArgs;

        foreach ($commandNames as $commandName) {
            $command = $this->createCommand(new CommandInfo($className, $commandName));
            $command->setCode(function(InputInterface $input) use ($commands, $commandName, $passThrough) {
                // get passthru args
                $args = $input->getArguments();
                array_shift($args);
                if ($passThrough) {
                    $args[key(array_slice($args, -1, 1, TRUE))] = $passThrough;
                }
                $args[] = $input->getOptions();
                // Execute the command
                $res = call_user_func_array(array($commands, $commandName), $args);
                if (is_int($res)) exit($res);
                if (is_bool($res)) exit($res ? 0 : 1);
                if ($res instanceof Result) exit($res->getExit());
            });
            $app->add($command);
        }
        return $app;
    }

    public function createCommand(CommandInfo $taskInfo)
    {
        $task = new Command($taskInfo->getName());
        $task->setDescription($taskInfo->getDescription());
        $task->setHelp($taskInfo->getHelp());
        $args = $taskInfo->getArguments();
        foreach ($args as $name => $val) {
            $description = $taskInfo->getArgumentDescription($name);
            if ($val === CommandInfo::PARAM_IS_REQUIRED) {
                $task->addArgument($name, InputArgument::REQUIRED, $description);
            } elseif (is_array($val)) {
                $task->addArgument($name, InputArgument::IS_ARRAY, $description, $val);
            } else {
                $task->addArgument($name, InputArgument::OPTIONAL, $description, $val);
            }
        }
        $opts = $taskInfo->getOptions();
        foreach ($opts as $name => $val) {
            $description = $taskInfo->getOptionDescription($name);
            $fullname = $name;
            $shortcut = '';
            if (strpos($name, '|')) {
                list($fullname, $shortcut) = explode('|', $name, 2);
            }
            if (is_bool($val)) {
                $task->addOption($fullname, $shortcut, InputOption::VALUE_NONE, $description);
            } else {
                $task->addOption($fullname, $shortcut, InputOption::VALUE_OPTIONAL, $description, $val);
            }
        }
        return $task;
    }

    public function shutdown()
    {
        $error = error_get_last();
        if (!is_array($error)) return;
        $this->communicator->write(sprintf("<error>ERROR: %s \nin %s:%d\n</error>", $error['message'], $error['file'], $error['line']));
    }
}

?>