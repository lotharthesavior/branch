<?php

namespace Git;

class Helpers
{
    /**
     * Tests if the given path is a git repository
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isGitRepo( string $path ): bool
    {
        return self::isWorkingRepository( $path ) || self::isBareRepository( $path );
    }

    /**
     * Tests if the given path is a git working repository
     *
     * @return bool
     */
    public static function isWorkingRepository( string $path ): bool
    {
        return is_dir( $path )
               && file_exists( $path . "/.git" )
               && is_dir( $path . "/.git" );
    }

    /**
     * Tests if the given path is a git bare repository
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isBareRepository( string $path ): bool
    {
        if ( ! is_file( $path . "/config" ) ) {
            return false;
        }

        $parse_ini = parse_ini_file( $path . "/config" );
        if ( $parse_ini['bare'] ) {
            return true;
        }

        return false;
    }

    /**
     * Tests if git is installed
     *
     * @access public
     *
     * @return bool
     */
    public function testGit(): bool
    {
        $descriptorspec = array(
            1 => array( 'pipe', 'w' ),
            2 => array( 'pipe', 'w' ),
        );
        $pipes          = array();
        $resource       = proc_open( Git::getBin(), $descriptorspec, $pipes );

        $stdout = stream_get_contents( $pipes[1] );
        $stderr = stream_get_contents( $pipes[2] );
        foreach ( $pipes as $pipe ) {
            fclose( $pipe );
        }

        $status = trim( proc_close( $resource ) );

        return ( $status != 127 );
    }
}
