<?php

use Git\Console;
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
class GitFilesystemTest extends TestCase
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
    public function testGitCanCloneRepository()
    {
        $repo  = self::TEST_REPO_DIR . '/repo';
        $repo2 = self::TEST_REPO_DIR . '/repo2';

        $console = new Console;
        $gitRepo = Git::create( $console, $repo );

        $this->createNewFileInRepo( $repo );
        $gitRepo->add();
        $gitRepo->commit( 'Initial commit.' );

        $gitRepo2 = Git::cloneFrom( $console, $repo, $repo2 );

        $this->assertTrue( Helpers::isWorkingRepository( $repo ) );
        $this->assertTrue( Helpers::isWorkingRepository( $repo2 ) );
    }

    /** @test */
    public function testGitCanOpenExistentRepository()
    {
        $repo = self::TEST_REPO_DIR . '/repo';

        $console = new Console;
        $gitRepo = Git::create( $console, $repo );

        $openedRepo = Git::open( $console, $repo );

        $this->assertInstanceOf(GitRepo::class, $openedRepo);
        $this->assertEquals($gitRepo->getRepoPath(), $openedRepo->getRepoPath());
    }

    // -------------------------
    // Private Methods
    // -------------------------

    private function createNewFileInRepo( string $path )
    {
        $existentFiles      = glob( $path . '/*' );
        $existentFilesCount = count( $existentFiles );
        $txt                = "John Doe\n";
        $newFile = fopen( $path . '/file-' . $existentFilesCount . '.txt', 'w' ) or die( 'Unable to open file!' );

        fwrite( $newFile, $txt );
        fclose( $newFile );
    }
}
