<?php

namespace Git;

use Git\DTO\Branch;
use Git\DTO\Tag;
use Git\Exceptions\InvalidArgumentException;

class RepoUtils
{
    /**
     * @param $reference
     *
     * @return Branch|Tag
     */
    public static function getReferenceInfo( $reference )
    {
        preg_match( '(refs/(heads|tags)/(.*))', $reference, $info );
        if ( empty( $info[1] ) || empty( $info[2] ) ) {
            throw new InvalidArgumentException( sprintf( 'Could not get branch or tag name from reference %s', $reference ) );
        }
        if ( $info[1] === 'heads' ) {
            return new Branch( $info[2] );
        } else {
            return new Tag( $info[2] );
        }
    }

}
