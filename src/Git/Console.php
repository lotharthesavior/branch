<?php

namespace Git;

use Exception;
use Git\Exceptions\ConsoleException;
use Git\Interfaces\ConsoleInterface;

class Console implements ConsoleInterface
{
    /**
     * Environment options
     *
     * @var array
     */
    protected $envopts = [];

    /** @var string */
    protected $currentPath = '';

    /** @return string */
    public function getCurrentPath(): string
    {
        return $this->currentPath;
    }

    /**
     * @param string $currentPath
     *
     * @return ConsoleInterface
     */
    public function setCurrentPath(string $currentPath): ConsoleInterface
    {
        $this->currentPath = $currentPath;
        return $this;
    }

    /**
     * Sets custom environment options for calling a command
     *
     * @param string $key key
     * @param string $value value
     */
    public function setEnv(string $key, string $value): void
    {
        $this->envopts[$key] = $value;
    }

    /**
     * Run a command in the git repository
     *
     * Accepts a shell command to run
     *
     * @access protected
     *
     * @param string $command command to run
     *
     * @return string
     *
     * @throws Exception
     */
    public function runCommand(string $command): string
    {
        $descriptorSpec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();

        /**
         * Depending on the value of variables_order, $_ENV may be empty.
         * In that case, we have to explicitly set the new variables with
         * putenv, and call proc_open with env=null to inherit the reset
         * of the system.
         *
         * This is kind of crappy because we cannot easily restore just those
         * variables afterwards.
         *
         * If $_ENV is not empty, then we can just copy it and be done with it.
         */
        if (count($_ENV) === 0) {
            $env = NULL;
            foreach ($this->envopts as $k => $v) {
                putenv(sprintf("%s=%s", $k, $v));
            }
        } else {
            $env = array_merge($_ENV, $this->envopts);
        }
        $cwd = $this->currentPath;
        $resource = proc_open($command, $descriptorSpec, $pipes, $cwd, $env);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        
        // TODO: review the exceptions check
        // $status = trim(proc_close($resource));
        // if ($status) throw new ConsoleException($stderr . PHP_EOL . $stdout);

        return $stderr . $stdout;
    }
}
