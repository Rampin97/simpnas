<?php

namespace Simpnas\Services;

class NginxProxyManager extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "nginx-proxy-manager";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Nginx Proxy Manager";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Proxy services from the outside in using LetsEncrypt Certs";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Proxy";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Proxy";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "nginx-proxy-manager";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "proxy";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 83;
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
     * @return string|null
     */
    public function getShareName(): ?string
    {
        return null;
    }
}
