<?php

namespace Simpnas\Services;

class Transmission extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "transmission";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Transmission";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Web based BitTorrent Download Client";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Downloads";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Downloads";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "transmission";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "transmission";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 9091;
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

    /**
     * @return string
     */
    public function getShareName(): string
    {
        return "downloads";
    }
}
