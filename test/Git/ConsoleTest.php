<?php

use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{
    public function testCurrentPath()
    {
        $console = new \Git\Console();
        $console->setCurrentPath( __DIR__ );
        self::assertEquals( __DIR__, trim( $console->runCommand( 'pwd' ) ) );
    }
}
