<?php

namespace Git\Interfaces;

use Exception;

interface ConsoleInterface
{
    /** @return string */
    public function getCurrentPath(): string;

    /**
     * @param string $currentPath
     *
     * @return ConsoleInterface
     */
    public function setCurrentPath(string $currentPath): ConsoleInterface;

    /**
     * Sets custom environment options for calling a command
     *
     * @param string $key key
     * @param string $value value
     */
    public function setEnv(string $key, string $value): void;

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
    public function runCommand(string $command): string;
}
