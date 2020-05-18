<?php

namespace Git;

use Exception;
use Git\Exceptions\GitException;
use Git\Interfaces\ConsoleInterface;
use Git\Interfaces\GitInterface;
use Git\Interfaces\GitRepoInterface;

/**
 * Git repo factory class
 * This class enables the creating, reading, and manipulation
 * of git repositories.
 * @package Git
 * @class  Git
 */
class Git implements GitInterface
{

    const ZERO_REVISION = '0000000000000000000000000000000000000000';

    /**
     * Git executable location
     *
     * @var string
     */
    protected static $bin = '/usr/bin/git';

    /**
     * Sets git executable path
     *
     * @param string $path executable location
     */
    public static function setBin( string $path )
    {
        self::$bin = $path;
    }

    /**
     * Gets git executable path
     *
     * @return string
     */
    public static function getBin(): string
    {
        return self::$bin;
    }

    /**
     * Sets up library for use in a default Windows environment
     */
    public static function windowsMode()
    {
        self::setBin( 'git' );
    }

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
    public static function create( ConsoleInterface $console, string $repoPath, $source = null ): GitRepoInterface
    {
        return self::createRepo( $console, $repoPath, $source );
    }

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
    public static function open( ConsoleInterface $console, string $repoPath ): GitRepoInterface
    {
        return new GitRepo( $console, $repoPath );
    }

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
    ): GitRepoInterface {
        return self::createRepo( $console, $repoPath, $remote, true, $reference );
    }

    /**
     * Checks if a variable is an instance of GitRepoInterface
     *
     * Accepts a variable
     *
     * @param mixed $var variable
     *
     * @return  bool
     */
    public static function isRepo( $var ): bool
    {
        return $var instanceof GitRepoInterface;
    }

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
    ): GitRepoInterface {
        if ( Helpers::isGitRepo( $repoPath ) ) {
            throw new GitException( sprintf( '"%s" is already a git repository', $repoPath ) );
        } else {
            $repo = new GitRepo( $console, $repoPath, true, true );
            if ( is_string( $source ) ) {
                if ( $remoteSource ) {
                    if (
                        $reference === null
                        || ! is_dir( $reference )
                        || ! is_dir( $reference . '/.git' )
                    ) {
                        throw new GitException( '"' . $reference . '" is not a git repository. Cannot use as reference.' );
                    } else if ( strlen( $reference ) ) {
                        $reference = realpath( $reference );
                        $reference = "--reference $reference";
                    }
                    $repo->cloneRemote( $source, $reference );
                } else {
                    $repo->cloneFrom( $source, $commandString );
                }
            } else {
                $repo->init();
            }

            return $repo;
        }
    }

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
    ): GitRepoInterface {
        $command    = "clone --local %s %s %s";
        $sourceRealPath = realpath($source);
        $parameters = [ $sourceRealPath, $destiny, $commandString ];
        $console->setCurrentPath( $destiny );
        $console->runCommand( vsprintf( self::getBin() . " " . $command, $parameters ) );
        $realPath = realpath($destiny);

        return new GitRepo( $console, $realPath, false, false );
    }

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
    ): GitRepoInterface {
        $command    = "clone %s %s %s";
        $parameters = [ $source, $destiny, $commandString ];
        $console->setCurrentPath( $destiny );
        $console->runCommand( vsprintf( self::getBin() . " " . $command, $parameters ) );
        $realPath = realpath($destiny);

        return new GitRepo( $console, $realPath, false, false );
    }

}
