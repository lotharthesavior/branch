<?php

use Git\Console;
use Git\DTO\Branch;
use Git\DTO\Remote;
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
        $gitRepo = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->remote();

        $this->assertTrue( $executed, 'Command Add was not executed!' );
    }

    /** @test */
    public function testCanAddRemoteToRepo()
    {
        $executed = false;

        $name = 'origin';
        $address = 'https://github.com/lotharthesavior/branch.git';
        $remote = new Remote($name, $address, '(fetch)');
        $command = sprintf( '/usr/bin/git remote add %s %s %s', $remote->getName(), $remote->getUrl(), $remote->getType() );
        $consoleMock = $this->mockConsoleForCommand( $command, $executed );
        $gitRepo = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->remoteAdd( $remote );

        $this->assertTrue( $executed, 'Command Add was not executed!' );
    }

    /** @test */
    public function testCanCloneRemoteRepo()
    {
        $executed = false;

        $localPath = __DIR__ . DIRECTORY_SEPARATOR . 'test-local-repo';
        $repo = 'https://github.com/lotharthesavior/branch.git';
        $command = sprintf( '/usr/bin/git clone %s %s', $repo, $localPath );
        $consoleMock = $this->mockConsoleForCommand( $command, $executed );
        $gitRepo = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->clone( $repo, $localPath );

        $this->assertTrue( $executed, 'Command Add was not executed!' );
    }

    /** @test */
    public function testCanPushToRemoteRepo()
    {
        $executed = false;

        $branch = new Branch('master');
        $remote = new Remote('origin', 'https://github.com/lotharthesavior/branch.git', '(push)');
        $command = sprintf( '/usr/bin/git push %s %s %s', '', $remote->getName(), $branch->getName() );
        $consoleMock = $this->mockConsoleForCommand( $command, $executed );
        $gitRepo = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->push( $remote, $branch );

        $this->assertTrue( $executed, 'Command Add was not executed!' );
    }

    /** @test */
    public function testCanPullFromRemoteRepo()
    {
        $executed = false;

        $branch = new Branch('master');
        $remote = new Remote('origin', 'https://github.com/lotharthesavior/branch.git', '(fetch)');
        $command = sprintf( '/usr/bin/git pull %s %s', $remote->getName(), $branch->getName() );
        $consoleMock = $this->mockConsoleForCommand( $command, $executed );
        $gitRepo = new GitRepo( $consoleMock, '', true, true );
        $gitRepo->pull( $remote, $branch );

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
