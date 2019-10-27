<?php
/**
 * Git.php
 *
 * A PHP library based on kbjr/Git.php and it's forks.
 *
 * @author     James Brumond, coylOne
 * @copyright  Copyright 2013 James Brumond
 * @repo       http://github.com/kbjr/Git.php, https://github.com/coyl/Git.php
 */

namespace Git;

use Exception;

/**
 * Git repo factory class
 * This class enables the creating, reading, and manipulation
 * of git repositories.
 * @package Git
 * @class  Git
 */
class Git
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
     * @access  public
     *
     * @param string $repoPath repository path
     * @param string $source directory to source
     *
     * @return  GitRepo
     *
     * @throws Exception
     */
    public static function create( $repoPath, $source = null ): GitRepo
    {
        return GitRepo::create( $repoPath, $source );
    }

    /**
     * Open an existing git repository
     *
     * Accepts a repository path
     *
     * @access  public
     *
     * @param string $repoPath repository path
     *
     * @return GitRepo
     *
     * @throws Exception
     */
    public static function open( string $repoPath ): GitRepo
    {
        return new GitRepo( $repoPath );
    }

    /**
     * Clones a remote repo into a directory and then returns a GitRepo object
     * for the newly created local repo
     *
     * Accepts a creation path and a remote to clone from
     *
     * @access  public
     *
     * @param string $repoPath repository path
     * @param string $remote remote source
     * @param string $reference reference path
     *
     * @return  GitRepo
     *
     * @throws Exception
     **/
    public static function cloneRemote( string $repoPath, string $remote, $reference = null ): GitRepo
    {
        return GitRepo::create( $repoPath, $remote, true, $reference );
    }

    /**
     * Checks if a variable is an instance of GitRepo
     *
     * Accepts a variable
     *
     * @access  public
     *
     * @param mixed $var variable
     *
     * @return  bool
     */
    public static function isRepo( $var ): bool
    {
        return $var instanceof GitRepo;
    }

}
