<?php

class TestsHelper
{
    /**
     * @param string $dir
     */
    public static function rmdirRecursive( string $dir )
    {
        $it = new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS );
        $it = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
        foreach ( $it as $file ) {
            if ( $file->isDir() ) {
                rmdir( $file->getPathname() );
            } else {
                unlink( $file->getPathname() );
            }
        }
        rmdir( $dir );
    }
}
