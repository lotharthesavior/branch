<?php

namespace Git\Interfaces;

use Exception;
use Git\Exceptions\GitException;

interface GitInterface
{
    /**
     * Sets git executable path
     *
     * @param string $path executable location
     */
    public static function setBin( string $path );

    /**
     * Gets git executable path
     *
     * @return string
     */
    public static function getBin(): string;

    /**
     * Create a new git repository
     *
     * Accepts a creation path, and, optionally, a source path
     *
     * @param ConsoleInterface $console
     * @param string $repoPath repository path
     * @param string $source directory to source
     *
     * @return  GitRepoInterface
     *
     * @throws Exception
     */
    public static function create( ConsoleInterface $console, string $repoPath, $source = null ): GitRepoInterface;

    /**
     * Open an existing git repository
     *
     * Accepts a repository path
     *
     * @param ConsoleInterface $console
     * @param string $repoPath repository path
     *
     * @return GitRepoInterface
     *
     * @throws Exception
     */
    public static function open( ConsoleInterface $console, string $repoPath ): GitRepoInterface;

    /**
     * Clones a remote repo into a directory and then returns a GitRepo object
     * for the newly created local repo
     *
     * Accepts a creation path and a remote to clone from
     *
     * @param ConsoleInterface $console
     * @param string $repoPath repository path
     * @param string $remote remote source
     * @param string $reference reference path
     *
     * @return  GitRepoInterface
     *
     * @throws Exception
     **/
    public static function cloneRemote(
        ConsoleInterface $console,
        string $repoPath,
        string $remote,
        $reference = null
    ): GitRepoInterface;

    /**
     * Checks if a variable is an instance of GitRepoInterface
     *
     * Accepts a variable
     *
     * @param mixed $var variable
     *
     * @return  bool
     */
    public static function isRepo( $var ): bool;

    /**
     * Create a new git repository
     *
     * Accepts a creation path, and, optionally, a source path
     *
     * @param ConsoleInterface $console
     * @param string $repoPath repository path
     * @param string|null $source directory to source
     * @param bool $remoteSource reference path
     * @param string|null $reference - https://git-scm.com/docs/git-clone#Documentation/git-clone.txt---reference-if-ableltrepositorygt
     * @param string $commandString
     *
     * @return GitRepoInterface
     *
     * @throws GitException|Exception
     */
    public static function createRepo(
        ConsoleInterface $console,
        string $repoPath,
        $source = null,
        $remoteSource = false,
        $reference = null,
        $commandString = ""
    ): GitRepoInterface;

    /**
     * Runs a `git clone` call to clone a different repository
     * into the current repository
     *
     * Accepts a source directory
     *
     * @param ConsoleInterface $console
     * @param string $source source directory
     * @param string $destiny path to clone to
     * @param string $commandString
     *
     * @return GitRepoInterface
     * @throws Exception
     *
     */
    public static function cloneFrom(
        ConsoleInterface $console,
        string $source,
        string $destiny,
        string $commandString = ""
    ): GitRepoInterface;

    /**
     * Runs a `git clone` call to clone a different repository
     * into the current repository
     *
     * Accepts a source directory
     *
     * @param ConsoleInterface $console
     * @param string $source source directory
     * @param string $destiny path to clone to
     * @param string $commandString
     *
     * @return GitRepoInterface
     * @throws Exception
     *
     */
    public static function cloneFromRemote(
        ConsoleInterface $console,
        string $source,
        string $destiny,
        string $commandString = ""
    ): GitRepoInterface;
}
