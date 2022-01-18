<?php

namespace Simpnas\Utils;

class StorageInfo
{

    private string $id;
    private string $prefix;

    public function __construct(string $id, string $prefix)
    {
        $this->id = $id;
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullId(): string {
        return sprintf('/%s/%s', $this->prefix, $this->getId());
    }

}
