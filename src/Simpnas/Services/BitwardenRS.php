<?php

namespace Simpnas\Services;

class BitwardenRS extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "bitwarden";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Bitwarden RS";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Password Manager -- Note: Bitwarden will not work properly unless remote access is enabled because Bitwarden requires HTTPS.";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Password Manager";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Password Manager";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "bitwarden";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "vault";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 88;
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
