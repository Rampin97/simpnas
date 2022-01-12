<?php

namespace Simpnas\Services;

class UnifiController extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "unifi-controller";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Unifi Controller";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Manage Ubiquiti network devices.";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Unifi Apps";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Unifi Apps";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "unifi-controller";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "unifi";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 8443;
    }

    /**
     * @inheritDoc
     */
    public function getProtocol(): string
    {
        return "https://";
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
