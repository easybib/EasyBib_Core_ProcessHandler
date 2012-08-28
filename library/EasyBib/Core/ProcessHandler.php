<?php
namespace EasyBib\Core;

/**
 * A simple class to handle processes.
 *
 * @category Core
 * @package  ProcessHandler
 * @author   Till Klampaeckel <till@php.net>
 */
class ProcessHandler
{
    /**
     * The command!
     * @var string
     */
    protected $command;

    /**
     * Current working directory for the process to execute from.
     * @var string
     */
    protected $cwd;

    /**
     * @param string
     * @param string
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($command, $cwd)
    {
        $this->setCommand($command);
        $this->setCwd($cwd);
    }

    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        if (!is_dir($this->cwd)) {
            throw new \InvalidArgumentException("CWD is not a proper directory: $this->cwd");
        }

        return $this;
    }

    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * Colorful echo
     *
     * @param mixed $message Mixed signals? ;)
     * @param bool  $error   'true' enables red.
     *
     * @return void
     */
    public function echoShell($message, $error = false)
    {
        if ($message instanceof \Exception) {
            $message = $message->getMessage();
        }
        echo "\033[";
        if (false === $error) {
            echo "0;32";
        } else {
            echo "0;31";
        }
        echo "m";
        echo $message;
        echo "\033[0m" . PHP_EOL;
    }

    /**
     * This is a wrapper around proc_open() (etc.) to run shell commands.
     *
     * @param string
     * @param string
     *
     * @return string
     * @throws \RuntimeException
     */
    public function execute($echo = true)
    {
        static $descriptorSpec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "a"),
        );

        //echo "Running $this->command" . PHP_EOL;
        $resource = proc_open($this->command, $descriptorSpec, $pipes, $this->cwd);
        if (false === is_resource($resource)) {
            throw new \RuntimeException("Failed to start: $this->command");
        }
        while (false !== ($status = proc_get_status($resource))) {
            if (true === $status['running']) {
                if (true === $echo) {
                    echo ".";
                }
                sleep(1);
                continue;
            }
            if (true === $echo) {
                echo PHP_EOL;
            }

            fclose($pipes[0]);
            $returnValue = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stdErr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $error = "";
            if (!empty($stdErr)) {
                $error  = "stdErr:  ";
            }
            if (!empty($returnValue)) {
                $error .= "stdOut: " . $returnValue;
            }

            proc_close($resource);

            if (true === $status['signaled'] || true === $status['stopped']) {
                $kind = (true === $status['signaled'])?'term':'stop';
                if ('sig' == $kind) {
                    $signal = $status['termsig'];
                } else {
                    $signal = $status['stopsig'];
                }
                throw new \RuntimeException(sprintf(
                    "Command was stopped: %s / %s - %s", $kind, $signal, $error
                ));
            }

            if ($status['exitcode'] > 0) {
                $msg = sprintf(
                    "Command '%s' terminated with %s",
                    $this->command,
                    $status['exitcode']
                );
                if (!empty($error)) {
                    $msg .= " and said: $error";
                }
                throw new \RuntimeException($msg);
            }
            return $returnValue;
        }
        throw new \RuntimeException("Unable to get the status.");
    }
}

