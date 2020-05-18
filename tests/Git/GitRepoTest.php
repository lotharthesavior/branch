<?php

use Git\Console;
use Git\Exceptions\GitException;
use Git\GitRepo;
use Git\Interfaces\ConsoleInterface;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../TestsHelper.php';

class GitRepoTest extends TestCase
{
    /** @test */
    public function testGitRepoThrowsExceptionForNonExistentDirectories()
    {
        $directory = 'some-not-existent-directory';
        $this->expectException( GitException::class );
        $this->expectExceptionMessage( '"' . $directory . '" does not exist' );
        $consoleMock = Mockery::mock( Console::class );
        new GitRepo( $consoleMock, $directory );
    }

    /** @test */
    public function testGitRepoInitExecutesGitInitCommmand()
    {
        $executed = false;

        $consoleMock = $this->mockConsoleForCommand( '/usr/bin/git init .', $executed );
        $gitRepo     = new GitRepo( $consoleMock, '' );
        $gitRepo->init();

        $this->assertTrue( $executed, 'Command was not executed!' );
    }

    /** @test */
    public function testGitRepoBranchesExecutesGitBranchesCommmand()
    {
        $executed = false;

        $consoleMock = $this->mockConsoleForCommand( '/usr/bin/git branch', $executed );
        $gitRepo     = new GitRepo( $consoleMock, '', true, true );

        $gitRepo->branchGet();

        $this->assertTrue( $executed, 'Command was not executed!' );
    }

    /** @test */
    public function testGitRepoCommitExecutesGitCommitCommand()
    {
        $executedAdd    = false;
        $executedCommit = false;

        $consoleMock = $this->mockConsoleForCommand( '/usr/bin/git add * -v', $executedAdd );
        $consoleMock->shouldReceive( 'runCommand' )
                    ->with( "/usr/bin/git commit -av -m ''" )
                    ->andReturnUsing( function ( $command ) use ( &$executedCommit ) {
                        $executedCommit = true;

                        return 'executed';
                    } );

        $gitRepo = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->add();
        $gitRepo->commit();

        $this->assertTrue( $executedAdd, 'Command Add was not executed!' );
        $this->assertTrue( $executedCommit, 'Command Commit was not executed!' );
    }

    /** @test */
    public function testGitRepoRemoteExecutesGitRemoteCommand()
    {
        $executed = false;

        $consoleMock = $this->mockConsoleForCommand( '/usr/bin/git remote -v', $executed );
        $gitRepo     = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->remote();

        $this->assertTrue( $executed, 'Command Add was not executed!' );
    }

    // -------------------------
    // Private Methods
    // -------------------------

    private function mockConsoleForCommand( string $command, &$executed ): ConsoleInterface
    {
        $consoleMock = Mockery::mock( Console::class );
        $consoleMock->shouldReceive( 'setCurrentPath' );
        $consoleMock->shouldReceive( 'runCommand' )
                    ->with( $command )
                    ->andReturnUsing( function ( $command ) use ( &$executed ) {
                        $executed = true;

                        return "executed item2 item3";
                    } );

        return $consoleMock;
    }
}
