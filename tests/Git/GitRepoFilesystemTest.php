<?php

use Git\Console;
use Git\DTO\Branch;
use Git\DTO\Remote;
use Git\Exceptions\GitException;
use Git\Git;
use Git\GitRepo;
use Git\Helpers;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../TestsHelper.php';

/**
 * Class GitRepoFilesystemTest
 *
 * Tests the git actions upon a test directory, creating repositories,
 * commits, cloning into new directories...
 */
class GitRepoFilesystemTest extends TestCase
{
    /** @var string */
    const TEST_REPO_DIR = __DIR__ . '/../test-repository';

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        self::removeDirectory();
        parent::setUp();
    }

    /**
     * @beforeClass
     */
    public static function prepareDirectory(): void
    {
        if ( ! file_exists( self::TEST_REPO_DIR ) ) {
            mkdir( self::TEST_REPO_DIR );
        }
    }

    /**
     * @after
     */
    public static function removeDirectory(): void
    {
        TestsHelper::rmdirRecursive( self::TEST_REPO_DIR );
        if ( ! file_exists( self::TEST_REPO_DIR ) ) {
            mkdir( self::TEST_REPO_DIR );
        }
    }

    /** @test */
    public function testGitRepoInitStartsRepo()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true );
        $gitRepo->init();
        $this->assertDirectoryExists( $repo );
        $this->assertTrue( Helpers::isWorkingRepository( $repo ) );
    }

    /** @test */
    public function testGitRepoBranchesReturnArray()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console          = new Console;
        $gitRepo          = new GitRepo( $console, $repo, true, true );
        $branchesResponse = $gitRepo->branchGet();
        $this->assertIsArray( $branchesResponse );
    }

    /** @test */
    public function testGitRepoCanCommitAndCreateBranch()
    {
        $newBranch = 'my-new-branch';
        $repo      = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo );

        // make current branch's initial commit
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );

        $gitRepo->branchNew( $newBranch );
        $createdBranch    = $gitRepo->getActiveBranch();
        $existentBranches = $gitRepo->branchGet();

        $this->assertEquals( $newBranch, $createdBranch->getName() );
        foreach ( $existentBranches as $branch ) {
            $this->assertInstanceOf( Branch::class, $branch );
        }
    }

    /** @test */
    public function testGitRepoCanGetRemotes()
    {
        $repo  = self::TEST_REPO_DIR . '/repo';
        $repo2 = self::TEST_REPO_DIR . '/repo2';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );

        $gitRepo2 = Git::cloneFrom( $console, $repo, $repo2 );

        // check if the remote exists in the remotes list of the clone repo
        $repo1Path = $gitRepo->getRepoPath();
        $remotes   = $gitRepo2->remote();
        foreach ($remotes as $remote) {
            $this->assertInstanceOf(Remote::class, $remote);
        }
        $remotes   = array_map( function ( $remote ) {
            return $remote->getUrl();
        }, $remotes );
        $this->assertTrue( in_array( $repo1Path, $remotes ), 'Remote not found in the clone repository.' );
    }

    /**
     * @test
     */
    public function testGitRepoCanAddRemoteAndRemoteBranchesCount()
    {
        $newBranch = 'my-new-branch';

        $repo  = self::TEST_REPO_DIR . '/repo';
        $repo2 = self::TEST_REPO_DIR . '/repo2';
        $repo3 = self::TEST_REPO_DIR . '/repo3';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );
        $gitRepo->branchNew($newBranch);

        $gitRepo2 = Git::cloneFrom( $console, $repo, $repo2 );
        $gitRepo3 = Git::cloneFrom( $console, $repo, $repo3 );

        // add remote
        $gitRepo2->remoteAdd('my-remote-name', 'my-custom-address');
        $remotes2 = $gitRepo2->remote();
        foreach ($remotes2 as $remote) {
            $this->assertInstanceOf(Remote::class, $remote);
        }
        $this->assertCount(4, $remotes2);

        // count remote branches
        $remoteBranchesCount = $gitRepo3->getRemoteBranchesCount();
        $this->assertIsInt($remoteBranchesCount);
    }

    /**
     * @test
     */
    public function testGitRepoCanSearchRemoteBranches()
    {
        $newBranch = 'my-new-branch';

        $repo  = self::TEST_REPO_DIR . '/repo';
        $repo2 = self::TEST_REPO_DIR . '/repo2';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );
        $gitRepo->branchNew($newBranch);

        $gitRepo2 = Git::cloneFrom( $console, $repo, $repo2 );

        // search branch on remotes
        $searchedBranches = $gitRepo2->getRemoteBranchesByPattern( 'new' );
        foreach ($searchedBranches as $remoteBranch) {
            $this->assertInstanceOf(Branch::class, $remoteBranch);
        }
    }

    /** @test */
    public function testGitRepoCanSetDescription()
    {
        $repo            = self::TEST_REPO_DIR . '/repo';
        $descriptionText = 'This is a new description';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );
        $gitRepo->setDescription( $descriptionText );

        $descriptionContent  = file_get_contents( $gitRepo->getGitDirectoryPath() . '/description' );
        $descriptionContent2 = $gitRepo->getDescription();
        $this->assertEquals( $descriptionText, $descriptionContent );
        $this->assertEquals( $descriptionText, $descriptionContent2 );
    }

    /** @test */
    public function testGitRepoCanGetStatusForEmptyRepo()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $status = $gitRepo->status();
        $this->assertEquals('master', $status->getBranch());
        $this->assertEmpty($status->getNewAssets());
        $this->assertEmpty($status->getChanges());
    }

    /** @test */
    public function testGitRepoCanGetStatusForOnlyUntrackedAssetsRepo()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo );

        $status = $gitRepo->status();
        $this->assertEquals('master', $status->getBranch());
        $this->assertEquals(1, count($status->getNewAssets()));
        $this->assertEmpty($status->getChanges());
    }

    /** @test */
    public function testGitRepoCanGetStatusForOnlyOneCommitAndCleanRepo()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );

        $status = $gitRepo->status();
        $this->assertEquals('master', $status->getBranch());
        $this->assertEmpty($status->getNewAssets());
        $this->assertEmpty($status->getChanges());
    }

    /** @test */
    public function testGitRepoCanGetStatusForOneCommitAndOneExtraAssetRepo()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo, 'file 1' );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );

        $this->updateFileInRepo( $repo, 'file 2' );

        $status = $gitRepo->status();
        $this->assertEquals('master', $status->getBranch());
        $this->assertEquals(1, count($status->getNewAssets()));
        $this->assertEmpty($status->getChanges());
    }

    /** @test */
    public function testGitRepoCanGetStatusForOneCommitAndOneChangeRepo()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = new GitRepo( $console, $repo, true, true );

        $this->updateFileInRepo( $repo, 'file 1' );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );

        $this->updateFileInRepo( $repo, 'file 2', 'file2.txt' );
        $gitRepo->add();
        $gitRepo->commit( 'Commit file 2.' );
        $this->updateFileInRepo( $repo, 'file 2 ...', 'file2.txt' );

        $status = $gitRepo->status();
        $this->assertEquals('master', $status->getBranch());
        $this->assertEmpty($status->getNewAssets());
        $this->assertEquals(1, count($status->getChanges()));
    }

    // -------------------------
    // Private Methods
    // -------------------------

    /**
     * @param string $path
     * @param string $txt
     * @param string $newFileName
     * @return void
     */
    private function updateFileInRepo( string $path, string $txt = "John Doe\n", string $newFileName = '' )
    {
        $existentFiles = glob( $path . '/*' );

        if ($newFileName === '') {
            $existentFilesCount = count($existentFiles);
            $newFileName = 'file-' . $existentFilesCount . '.txt';
        }

        $newFile = fopen( $path . '/' . $newFileName, 'w' ) or die( 'Unable to open file!' );

        fwrite( $newFile, $txt );
        fclose( $newFile );
    }
}
