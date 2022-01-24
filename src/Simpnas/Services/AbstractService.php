<?php

namespace Simpnas\Services;

abstract class AbstractService
{

    public function __construct(protected $dockerVolume)
    {
    }

    /**
     * @return string
     */
    abstract public function getId(): string;

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string {
        return "";
    }

    /**
     * @return string
     */
    abstract public function getCategory(): string;

    /**
     * @return string
     */
    public function getWebsite(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getImage(): string {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getContainerName(): string {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getFolder(): string {
        return sprintf("/volumes/%s/docker/%s", $this->dockerVolume, $this->getContainerName());
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool {
        return file_exists($this->getFolder());
    }

    /**
     * @return string|null
     */
    abstract public function getShareName(): ?string;

    /**
     * @return string
     */
    abstract public function getExternalHostname(): string;

    /**
     * @return int
     */
    abstract public function getLocalPort(): int;

    /**
     * @return string
     */
    abstract public function getProtocol(): string;

    /**
     * @return bool
     */
    abstract public function install(): bool;

    /**
     * @return bool
     */
    abstract public function update(): bool;

    /**
     * @return bool
     */
    abstract public function uninstall(): bool;

    /**
     * @return array
     */
    public function getConfig(): array {
        return [];
    }

}
