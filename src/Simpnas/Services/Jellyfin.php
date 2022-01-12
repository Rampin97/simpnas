<?php

namespace Simpnas\Services;

class Jellyfin extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "jellyfin";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Jellyfin";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc";
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
        return "jellyfin";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "jellyfin";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 8096;
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
