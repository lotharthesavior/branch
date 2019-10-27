<?php

namespace Git\DTO;

class Reference
{
    protected $name;

    public function __construct( string $name )
    {
        $this->name = $name;
    }
}
