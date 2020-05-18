<?php

namespace Git\DTO;

class Remote
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $url;

    /** @var string */
    protected $type;

    public function __construct( string $name, string $url, string $type )
    {
        $this->name = $name;
        $this->url  = $url;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

}
