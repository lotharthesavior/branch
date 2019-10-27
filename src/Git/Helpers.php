<?php

namespace Git;

class Helpers
{
    public static function isGitRepo(string $path): bool
    {
        return is_dir( $path )
               && file_exists( $path . "/.git" )
               && is_dir( $path . "/.git" );
    }
}
