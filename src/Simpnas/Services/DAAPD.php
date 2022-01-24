<?php

namespace Simpnas\Services;

class DAAPD extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "daapd";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "DAAPD";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "iTunes Server Music Streaming App";
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
        return "daapd";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 3689;
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
        return "media";
    }
}
