<?php

use Git\Console;
use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{
    public function testCurrentPath()
    {
        $console = new Console();
        $console->setCurrentPath( __DIR__ );
        self::assertEquals( __DIR__, trim( $console->runCommand( 'pwd' ) ) );
    }
}
