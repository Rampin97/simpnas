<?php

namespace Simpnas\Services;

class Nextcloud extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "nextcloud";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Nextcloud";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Groupware, file sharing platform";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Groupware";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Groupware";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "nextcloud";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "cloud";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 6443;
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
