<?php

namespace Simpnas\Services;

class HomeAssistant extends AbstractService
{

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "homeassistant";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Home Assistant";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return "Home Automation (Control Lights, switches, smart devices etc)";
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return "Smart Home";
    }

    /**
     * @inheritDoc
     */
    public function getWebsite(): string
    {
        return "Smart Home";
    }

    /**
     * @inheritDoc
     */
    public function getContainerName(): string
    {
        return "homeassistant";
    }

    /**
     * @inheritDoc
     */
    public function getExternalHostname(): string
    {
        return "homeassistant";
    }

    /**
     * @inheritDoc
     */
    public function getLocalPort(): int
    {
        return 8123;
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
