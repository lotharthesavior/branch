<?php

namespace Git\Interfaces;

use Exception;
use Git\Console;
use Git\DTO\Branch;
use Git\DTO\Remote;
use Git\DTO\Status;
use Git\Exceptions\BranchNotFoundException;
use Git\GitRepo;

interface GitRepoInterface
{
    /**
     * @return string
     *
     * @throws Exception
     */
    public function init(): string;

    // ------------------------------
    // Get Properties
    // ------------------------------

    /**
     * @return ConsoleInterface
     */
    public function getConsole(): ConsoleInterface;

    /**
     * Get the path to the git repo directory
     *
     * @return string
     */
    public function getRepoPath(): string;

    /**
     * Get the path to the git repo directory (eg. the ".git" directory)
     *
     * @return string
     */
    public function getGitDirectoryPath(): string;

    // ------------------------------
    // .git Files
    // ------------------------------

    /**
     * Sets the project description.
     *
     * @param string $new
     *
     * @return int|false
     */
    public function setDescription( string $new );

    /**
     * Gets the project description.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Sets custom environment options for calling Git
     *
     * @param string $key key
     * @param string $value value
     *
     * @return void
     */
    public function setEnv( string $key, string $value ): void;

    /**
     * List log entries.
     *
     * @param string $format
     * @param string $file
     * @param string $startHash
     * @param string $endHash
     *
     * @return string
     *
     * @throws Exception
     */
    public function logFileRevisionRange( string $startHash, string $endHash, $format = null, string $file = '' ): string;

    // ------------------------------
    // Run CLI Commands
    // ------------------------------

    // status

    /**
     * Runs a 'git status' call
     *
     * Accept a convert to HTML bool
     *
     * @param bool $html return string with <br />
     *
     * @return Status
     *
     * @throws Exception
     */
    public function status( $html = false ): Status;

    // add

    /**
     * Runs a `git add` call
     *
     * Accepts a list of files to add
     *
     * @param array|string $files files to add
     *
     * @return string
     *
     * @throws Exception
     */
    public function add( $files = "*" ): string;

    // rm

    /**
     * Runs a `git rm` call
     *
     * Accepts a list of files to remove
     *
     * @param string|array $files files to remove
     * @param boolean $cached use the --cached flag?
     *
     * @return string
     *
     * @throws Exception
     */
    public function rm( $files = "*", $cached = false ): string;

    // commit

    /**
     * Runs a `git commit` call
     *
     * Accepts a commit message string
     *
     * @param string $message commit message
     * @param boolean $commit_all should all files be committed automatically (-a flag)
     *
     * @return string
     *
     * @throws Exception
     */
    public function commit( string $message = "", bool $commit_all = true ): string;

    // clean

    /**
     * Runs a `git clean` call
     *
     * Accepts a remove directories flag
     *
     * @param bool $dirs delete directories?
     * @param bool $force force clean?
     *
     * @return string
     *
     * @throws Exception
     */
    public function clean( bool $dirs = false, bool $force = false ): string;


    // branch

    /**
     * Runs a `git branch` call
     *
     * Accepts a name for the branch
     *
     * @param string $branch branch name
     *
     * @return Branch
     *
     * @throws Exception
     */
    public function branchNew( string $branch ): Branch;

    /**
     * Runs a `git branch -[d|D]` call
     *
     * Accepts a name for the branch
     *
     * @param string $branch branch name
     * @param bool $force
     *
     * @return string
     *
     * @throws Exception
     */
    public function branchDelete( string $branch, bool $force = false ): string;

    /**
     * Returns array of branch names
     *
     * @param string $mode
     *
     * @return array - Branch[]
     *
     * @throws Exception
     *
     */
    public function branchGet( string $mode = GitRepo::BRANCH_LIST_MODE_LOCAL ): array;

    /**
     * Returns name of active branch
     *
     * @return Branch
     *
     * @throws Exception
     */
    public function getActiveBranch(): Branch;

    /**
     * Clears the local repository from the branches, which were deleted from the remote repository.
     *
     * @return string
     *
     * @throws Exception
     */
    public function remotePruneOrigin(): string;

    /**
     * Gets remote branches by pattern
     *
     * @param string $pattern
     *
     * @return array - Branch[]
     */
    public function getRemoteBranchesByPattern( string $pattern ): array;

    /**
     * Gets remote branches count
     *
     * @return int
     */
    public function getRemoteBranchesCount(): int;

    /**
     * Deletes remote branches
     *
     * @param array $branches
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteRemoteBranches( array $branches ): void;

    // checkout

    /**
     * Runs a `git checkout` call
     *
     * Accepts a name for the branch
     *
     * @access  public
     *
     * @param string $branch branch name
     *
     * @return string
     *
     * @throws BranchNotFoundException
     */
    public function checkout( string $branch ): string;

    // merge

    /**
     * Runs a `git merge` call
     *
     * Accepts a name for the branch to be merged
     *
     * @access  public
     *
     * @param string $branch branch name
     * @param string $message commit message (optional)
     *
     * @return  string
     *
     * @throws Exception
     */
    public function merge( string $branch, string $message = null ): string;

    /**
     * Runs a `git merge --abort`
     *
     * Reverts last merge
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function mergeAbort(): string;

    // reset

    /**
     * Runs a `git reset` with params
     *
     * @access  public
     *
     * @param string $resetStr
     *
     * @return  string
     *
     * @throws Exception
     */
    public function reset( string $resetStr ): string;

    // fetch

    /**
     * Runs a `git fetch` on the current branch
     *
     * @param bool $dry
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function fetch( bool $dry = false ): string;

    // stash

    /**
     * Runs a `git stash`
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function stash(): string;

    /**
     * Runs a `git stash pop`
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function stashPop(): string;

    // tag

    /**
     * Add a new tag on the current position
     *
     * Accepts the name for the tag and the message
     *
     * @param string $tag
     * @param string $message
     *
     * @return string
     *
     * @throws Exception
     */
    public function tag( string $tag, $message = null ): string;

    /**
     * List all the available repository tags.
     *
     * Optionally, accept a shell wildcard pattern and return only tags matching it.
     *
     * @param string $pattern Shell wildcard pattern to match tags against.
     *
     * @return array Available repository tags.
     *
     * @throws Exception
     */
    public function tags( string $pattern = null ): array;

    // push

    /**
     * Push specific branch to a remote
     *
     * Accepts the name of the remote and local branch
     *
     * @param Remote $remote
     * @param Branch $branch
     * @param bool $force
     *
     * @return string
     *
     * @throws Exception
     */
    public function push( Remote $remote, Branch $branch, bool $force = false ): string;

    //pull

    /**
     * Pull specific branch from remote
     *
     * Accepts the name of the remote and local branch
     *
     * @param Remote $remote
     * @param Branch $branch
     *
     * @return string
     *
     * @throws Exception
     */
    public function pull( Remote $remote, Branch $branch ): string;

    // log

    /**
     * List log entries.
     *
     * @param string $format
     * @param string $file
     * @param string|null $limit
     *
     * @return string
     *
     * @throws Exception
     */
    public function logFormatted( string $format = null, string $file = '', $limit = null ): string;

    /**
     * List log entries with `--grep`
     *
     * @param string $grep grep by ...
     * @param string $format
     *
     * @return string
     *
     * @throws Exception
     */
    public function logGrep( string $grep, $format = null ): string;

    /**
     * @param string $params
     *
     * @return string
     *
     * @throws Exception
     */
    public function log( string $params = '' ): string;

    // diff

    /**
     * Runs a `git diff`
     *
     * @param string $params
     *
     * @return string
     *
     * @throws Exception
     */
    public function diff( string $params = '' ): string;

    /**
     * Runs a `git diff --cached`
     *
     * @return string
     */
    public function diffCached(): string;

    // show

    /**
     * @param string $params
     *
     * @return mixed
     */
    public function show( string $params = '' ): string;

    // remote

    /**
     * Run `git remote -v`
     *
     * @return array Remote[]
     *
     * @throws Exception
     */
    public function remote(): array;

    /**
     * Run `git add`
     *
     * @param string $name
     * @param string $address
     *
     * @return void
     *
     * @throws Exception
     */
    public function remoteAdd( string $name, string $address ): void;

    // gc

    /**
     * Runs `git gc` command
     *
     * @param string $command
     *
     * @return bool
     */
    public function gc( string $command = '' ): bool;

    // rev-parse

    /**
     * Runs a `rev-parse HEAD`
     *
     * @return string
     *
     * @throws Exception
     */
    public function revParseHead(): string;
}
