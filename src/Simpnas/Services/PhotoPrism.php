<?php

namespace Simpnas\Services;

class PhotoPrism extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "photoprism";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "PhotoPrism";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Manage Photos.";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Media";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Media";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "photoprism";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "photos";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 2342;
    }

    /**
     * @inheritDoc
     */
    public function getProtocol(): string
    {
        return "http://";
    }

    /**
     * @inheritDoc
     */
    public function install(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function update(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): bool
    {
        return true;
    }
}
