<?php

namespace Git;

use Git\DTO\Branch;
use Git\DTO\Remote;
use Git\DTO\Status;
use Git\Exceptions\BranchNotFoundException;
use Exception;
use Git\Exceptions\ConsoleException;
use Git\Exceptions\GitException;
use Git\Interfaces\ConsoleInterface;
use Git\Interfaces\GitRepoInterface;

/**
 * Git Repository Interface Class
 *
 * This class enables the creating, reading, and manipulation
 * of a git repository
 *
 * @class  GitRepo
 */
class GitRepo implements GitRepoInterface
{
    /** @var string */
    const BRANCH_LIST_MODE_LOCAL = 'local';

    /** @var string */
    const BRANCH_LIST_MODE_REMOTE = 'remote';

    /** @var string */
    const BRANCH_LIST_MODE_All = 'all';

    /** @var string */
    const CONFLICT_FILE_MARK = '/CONFLICT.*?([\w\/\.]*)$/';

    /** @var null */
    protected $repoPath = null;

    /** @var bool */
    protected $bare = false;

    /** @var ConsoleInterface */
    protected $console;

    /**
     * Constructor
     *
     * Accepts a repository path
     *
     * @access  public
     *
     * @param ConsoleInterface $console
     * @param string $repo_path repository path
     * @param bool $create_new create if not exists?
     * @param bool $init
     *
     * @throws Exception
     */
    public function __construct(
        ConsoleInterface $console,
        string $repo_path,
        bool $create_new = false,
        bool $init = true
    ) {
        $this->console = $console;
        $this->setRepoPath( $repo_path, $create_new, $init );
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function init(): string
    {
        if ( $this->repoPath === null ) {
            throw new Exception( 'Repository Path not set.' );
        }

        return $this->run( 'init .' );
    }

    /**
     * @return ConsoleInterface
     */
    public function getConsole(): ConsoleInterface
    {
        return $this->console;
    }

    /**
     * Set the repository's path
     *
     * Accepts the repository path
     *
     * @param string $repo_path repository path
     * @param bool $create_new create if not exists?
     * @param bool $init initialize new Git repo if not exists?
     *
     * @return void
     *
     * @throws Exception
     */
    private function setRepoPath( string $repo_path, bool $create_new = false, bool $init = true )
    {
        $real_repo_path = realpath( $repo_path );

        // path doesn't exists AND not to create one
        if ( ! $real_repo_path && ! $create_new ) {
            throw new GitException( sprintf( '"%s" does not exist', $repo_path ) );
        }

        // path doesn't exists AND to create one
        if ( ! $real_repo_path && $create_new ) {
            $parent = realpath( dirname( $repo_path ) );
            if ( ! $parent ) {
                throw new GitException( 'Cannot create repository in non-existent directory' );
            }
            mkdir( $repo_path );
            $this->repoPath = realpath( $repo_path );
        }

        // path exists AND is not a directory
        if ( $real_repo_path && ! is_dir( $real_repo_path ) ) {
            throw new GitException( sprintf( '"%s" is not a directory', $real_repo_path ) );
        }

        if ( $real_repo_path && $this->repoPath === null ) {
            $this->repoPath = $real_repo_path;
        }

        // is not a repository
        if ( ! Helpers::isGitRepo( $real_repo_path ) && ! $init ) {
            throw new GitException( sprintf( '"%s" is not a git repository', $real_repo_path ) );
        }

        // is a working repository
        if ( Helpers::isWorkingRepository( $real_repo_path ) ) {
            $this->repoPath = $real_repo_path;
            $this->bare     = false;
        }

        // is a bare repository
        if ( Helpers::isBareRepository( $real_repo_path ) ) {
            $this->repoPath = $real_repo_path;
            $this->bare     = true;
        }

        if (
            ! Helpers::isWorkingRepository( $real_repo_path )
            && ! Helpers::isBareRepository( $real_repo_path )
            && $create_new
            && ! $this->repoPath
        ) {
            $this->repoPath = $real_repo_path;
        }

        $this->console->setCurrentPath( $this->repoPath );

        if (
            ! Helpers::isWorkingRepository( $real_repo_path )
            && ! Helpers::isBareRepository( $real_repo_path )
            && $init
        ) {
            $this->init();
        }
    }

    /**
     * Get the path to the git repo directory (eg. the ".git" directory)
     *
     * @return string
     */
    public function getGitDirectoryPath(): string
    {
        return ( $this->bare ) ? $this->repoPath : $this->repoPath . "/.git";
    }

    /**
     * Get the path to the git repo directory
     *
     * @return string
     */
    public function getRepoPath(): string
    {
        return $this->repoPath;
    }

    /**
     * Run a git command in the git repository
     *
     * Accepts a git command to run
     *
     * @param string $command command to run
     * @param array $parameters
     *
     * @return string
     *
     * @throws Exception
     */
    private function run( string $command, array $parameters = null ): string
    {
        if ( is_null( $parameters ) ) {
            $parameters = [];
        }

        return $this->console->runCommand( vsprintf( Git::getBin() . " " . $command, $parameters ) );
    }

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
     * @todo implement Status return
     */
    public function status( $html = false ): Status
    {
        $result = $this->run( "status" );
        $result = new Status($result);

        return $result;
    }

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
    public function add( $files = "*" ): string
    {
        if ( is_array( $files ) ) {
            $files = '"' . implode( '" "', $files ) . '"';
        }

        return $this->run( "add %s -v", [ $files ] );
    }

    /**
     * Runs a `git rm` call
     *
     * Accepts a list of files to remove
     *
     * @param array|string $files files to remove
     * @param boolean $cached use the --cached flag?
     *
     * @return string
     *
     * @throws Exception
     */
    public function rm( $files = "*", $cached = false ): string
    {
        if ( is_array( $files ) ) {
            $files = '"' . implode( '" "', $files ) . '"';
        }

        return $this->run( "rm %s %s", [ ( $cached ? '--cached ' : '' ), $files ] );
    }

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
    public function commit( string $message = "", bool $commit_all = true ): string
    {
        $flags = $commit_all ? '-av' : '-v';

        return $this->run( "commit %s -m %s", [ $flags, escapeshellarg( $message ) ] );
    }

    /**
     * Runs a `git clone` call to clone a remote repository
     * into a targeted directory
     *
     * @param string $repository remote repository
     * @param string $target target directory
     *
     * @return string
     * @throws Exception
     *
     */
    public function clone( string $repository, string $target ): string
    {
        return $this->run( "clone %s %s", [ $repository, $target ] );
    }

    /**
     * Runs a `git clone` call to clone the current repository
     * into a different directory
     *
     * Accepts a target directory
     *
     * @param string $target target directory
     *
     * @return string
     * @throws Exception
     *
     */
    public function cloneTo( string $target ): string
    {
        return $this->run( "clone --local %s %s", [ $this->repoPath, $target ] );
    }

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
    public function clean( bool $dirs = false, bool $force = false ): string
    {
        return $this->run( "clean %s %s", [ ( ( $force ) ? " -f" : "" ), ( ( $dirs ) ? " -d" : "" ) ] );
    }

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
     * @todo implement Branch
     */
    public function branchNew( string $branch ): Branch
    {
        $response = $this->run( "checkout -b $branch" );

        preg_match( '/\'([^"]+)\'/', $response, $matches );
        $branchName = $matches[ count( $matches ) - 1 ];

        return new Branch( $branchName );
    }

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
    public function branchDelete( string $branch, bool $force = false ): string
    {
        return $this->run( "branch %s %s", [ ( ( $force ) ? '-D' : '-d' ), $branch ] );
    }

    /**
     * Returns array of branch names
     *
     * @param string $mode
     *
     * @return array - Branch[]
     *
     * @throws Exception
     */
    public function branchGet( string $mode = GitRepo::BRANCH_LIST_MODE_LOCAL ): array
    {
        $branches = $this->branchGetRaw( $mode );
        $branches = array_filter( $branches );
        $branches = array_map( function ( $branch ) {
            $branch_name = str_replace( '* ', '', trim( $branch ) );

            return new Branch( $branch_name );
        }, $branches );

        return $branches;
    }

    /**
     * @param string $mode
     *
     * @return array
     *
     * @throws Exception
     */
    private function branchGetRaw( string $mode = GitRepo::BRANCH_LIST_MODE_LOCAL ): array
    {
        if ( $mode === GitRepo::BRANCH_LIST_MODE_LOCAL ) {
            $branches = explode( "\n", $this->run( "branch" ) );
        } elseif ( $mode === GitRepo::BRANCH_LIST_MODE_REMOTE ) {
            $branches = explode( "\n", $this->run( "branch -r" ) );
        } else {
            $branches = explode( "\n", $this->run( "branch -a" ) );
        }

        return array_map( 'trim', array_filter( $branches ) );
    }

    /**
     * Returns name of active branch
     *
     * @return Branch
     *
     * @throws Exception
     */
    public function getActiveBranch(): Branch
    {
        $branch_array  = $this->branchGetRaw( GitRepo::BRANCH_LIST_MODE_LOCAL );
        $active_branch = preg_grep( "/^\\*/", $branch_array );
        reset( $active_branch );
        $branch = str_replace( "* ", "", current( $active_branch ) );

        return new Branch( $branch );
    }

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
    public function checkout( string $branch ): string
    {
        try {
            return $this->run( "checkout %s", [ $branch ] );
        } catch ( ConsoleException $e ) {
            throw new BranchNotFoundException( sprintf( 'Branch %s not found', $branch ), 0, $e );
        }
    }


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
    public function merge( string $branch, string $message = null ): string
    {
        $branch = escapeshellarg( $branch );
        if ( null !== $message ) {
            $message = sprintf( '-m "%s"', trim( $message ) );
        }

        return $this->run( "merge %s --no-ff %s", [ $branch, $message ] );
    }

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
    public function mergeAbort(): string
    {
        return $this->run( 'merge --abort' );
    }

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
    public function reset( string $resetStr ): string
    {
        return $this->run( "reset %s", [ $resetStr ] );
    }

    /**
     * Runs a git fetch on the current branch
     *
     * @param bool $dry
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function fetch( bool $dry = false ): string
    {
        $dry = $dry ? ' --dry-run' : '';

        return $this->run( "fetch%s", [ $dry ] );
    }

    /**
     * Runs a git stash
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function stash(): string
    {
        return $this->run( "stash" );
    }

    /**
     * Runs a git stash pop
     *
     * @access  public
     *
     * @return  string
     *
     * @throws Exception
     */
    public function stashPop(): string
    {
        return $this->run( "stash pop" );
    }

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
    public function tag( string $tag, $message = null ): string
    {
        if ( $message === null ) {
            $message = $tag;
        }

        return $this->run( "tag -a %s -m %s", [ $tag, escapeshellarg( $message ) ] );
    }

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
    public function tags( string $pattern = null ): array
    {
        $tagArray = explode( "\n", $this->run( "tag -l %s", [ $pattern ] ) );
        foreach ( $tagArray as $i => &$tag ) {
            $tag = trim( $tag );
            if ( $tag == '' ) {
                unset( $tagArray[ $i ] );
            }
        }

        return $tagArray;
    }

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
     * @todo implement params and return
     */
    public function push( Remote $remote, Branch $branch, bool $force = false ): string
    {
        return $this->run( "push %s %s %s", [ $force ? '--force' : '', $remote->getName(), $branch->getName() ] );
    }

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
     * @todo implement params and return
     */
    public function pull( Remote $remote, Branch $branch ): string
    {
        return $this->run( "pull %s %s", [ $remote->getName(), $branch->getName() ] );
    }

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
    public function logFormatted( $format = null, string $file = '', $limit = null ): string
    {
        if ( $limit === null ) {
            $limitArg = "";
        } else {
            $limitArg = "-{$limit}";
        }

        if ( $format === null ) {
            return $this->run( "log %s %s", [ $limitArg, $file ] );
        } else {
            return $this->run( "log %s --pretty=format:'%s' %s", [ $limitArg, $format, $file ] );
        }
    }

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
    public function logGrep( string $grep, $format = null ): string
    {
        if ( $format === null ) {
            return $this->run( "log --grep='%s'", [ $grep ] );
        } else {
            return $this->run( "log --grep='%s' --pretty=format:'%s'", [ $grep, $format ] );
        }
    }

    /**
     * @param string $params
     *
     * @return string
     *
     * @throws Exception
     */
    public function log( string $params = '' ): string
    {
        return $this->run( 'log %s', [ $params ] );
    }

    /**
     * Runs a `git diff`
     *
     * @param string $params
     *
     * @return string
     *
     * @throws Exception
     */
    public function diff( string $params = '' ): string
    {
        return $this->run( "diff %s", [ $params ] );
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws Exception
     */
    public function show( string $params = '' ): string
    {
        return $this->run( "show %s", [ $params ] );
    }

    /**
     * Runs a `git diff --cached`
     *
     * @return string
     */
    public function diffCached(): string
    {
        return $this->run( 'diff --cached' );
    }

    /**
     * Sets the project description.
     *
     * @param string $new
     *
     * @return int|false
     */
    public function setDescription( string $new )
    {
        $path = $this->getGitDirectoryPath();

        return file_put_contents( $path . "/description", $new );
    }

    /**
     * Gets the project description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        $path = $this->getGitDirectoryPath();

        return file_get_contents( $path . "/description" );
    }

    /**
     * Sets custom environment options for calling Git
     *
     * @param string $key key
     * @param string $value value
     */
    public function setEnv( string $key, string $value ): void
    {
        $this->console->setEnv( $key, $value );
    }

    /**
     * Clears the local repository from the branches, which were deleted from the remote repository.
     *
     * @return string
     *
     * @throws Exception
     */
    public function remotePruneOrigin(): string
    {
        return $this->run( 'remote prune origin' );
    }

    /**
     * Gets remote branches by pattern
     *
     * @param string $pattern
     *
     * @return array - Branch[]
     *
     * @throws Exception
     *
     */
    public function getRemoteBranchesByPattern( string $pattern ): array
    {
        try {
            $response = $this->run( "branch -r | grep '%s'", [ $pattern ] );
        } catch ( Exception $e ) {
            $message = $e->getMessage();
            if ( ! empty( trim( $message ) ) ) {
                throw $e;
            }

            return [];
        }

        $response = explode( "\n", $response );
        $response = array_filter( $response );
        $response = array_map( function ( $branch ) {
            $branch = trim( $branch );

            return new Branch( $branch );
        }, $response );

        return $response;
    }

    /**
     * Gets remote branches count
     *
     * @return int
     */
    public function getRemoteBranchesCount(): int
    {
        try {
            return (int) $this->run( "branch -r | wc -l" );
        } catch ( \Exception $ex ) {
            return 0;
        }
    }

    /**
     * Deletes remote branches
     *
     * @param array $branches
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteRemoteBranches( array $branches ): void
    {
        $this->run( "push origin --delete %s", [ implode( " ", $branches ) ] );
    }

    /**
     * Run "git remote -v"
     *
     * @return array
     *
     * @throws Exception
     */
    public function remote(): array
    {
        $remotes = $this->run( "remote -v" );
        $remotes = explode( "\n", $remotes );
        $remotes = array_filter( $remotes );
        $remotes = array_map( function ( $remote ) {
            $remote = preg_split( '/\s+/', $remote );

            return new Remote( $remote[0], $remote[1], $remote[2] );
        }, $remotes );

        return $remotes;
    }

    /**
     * Run `git add`
     *
     * @param Remote $remote
     *
     * @return void
     *
     * @throws Exception
     */
    public function remoteAdd( Remote $remote ): void
    {
        $this->run( sprintf( 'remote add %s %s %s', $remote->getName(), $remote->getUrl(), $remote->getType() ) );
    }

    /**
     * Runs git gc command
     *
     * @param string $command
     *
     * @return bool
     */
    public function gc( string $command = '' ): bool
    {
        try {
            $this->run( "gc %s", [ $command ] );

            return true;
        } catch ( \Exception $ex ) {
            /**  @todo handle exceptions right */
            return false;
        }
    }

    /**
     * Runs a `rev-parse HEAD`
     *
     * @return string
     *
     * @throws Exception
     */
    public function revParseHead(): string
    {
        return trim( $this->run( 'rev-parse HEAD' ) );
    }

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
    public function logFileRevisionRange( string $startHash, string $endHash, $format = null, string $file = '' ): string
    {
        if ( $format === null ) {
            return $this->run( "log %s..%s %s", [ $startHash, $endHash, $file ] );
        } else {
            return $this->run( "log %s..%s --pretty=format:'%s' %s", [ $startHash, $endHash, $format, $file ] );
        }
    }
}
