<?php

namespace Git\DTO;

class Reference
{
    /** @var string */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct( string $name )
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
