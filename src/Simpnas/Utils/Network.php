<?php

namespace Simpnas\Utils;

use InvalidArgumentException;

class Network
{

    private string $name;

    private array $data;

    public function __construct(string $name, bool $checkExists = true)
    {
        $this->name = basename($name, '.network') . '.network';

        if ($checkExists && !file_exists("/etc/systemd/network/$name")) {
            throw new InvalidArgumentException('Network not found');
        }

        $this->loadFile();
    }

    /**
     * @return void
     */
    private function loadFile(): void {
        $this->data = parse_ini_file("/etc/systemd/network/" . $this->name) ?? [];
    }

    /**
     * @return Share[]
     */
    public static function getList(): array {
        exec("ls /etc/systemd/network", $shareList);

        return array_map(static function ($name) {
            return new Network($name, false);
        }, $shareList);
    }

    /**
     * @return string[]
     */
    public static function getInterfaces(): array {
        exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth | grep -v br", $netDevicesList);

        return $netDevicesList;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->data['Name'];
    }

    /**
     * @return string
     */
    public function getAddress(): string {
        if (!$this->isStatic()) {
            return "DHCP";
        }

        return $this->data['Address'];
    }

    /**
     * @return string
     */
    public function getGateway(): string {
        if (!$this->isStatic()) {
            return "DHCP";
        }

        return $this->data['Gateway'];
    }

    /**
     * @return string
     */
    public function getDNS(): string {
        if (!$this->isStatic()) {
            return "DHCP";
        }

        return $this->data['DNS'];
    }

    /**
     * @return string
     */
    public function getDHCP(): string {
        return $this->data['DHCP'];
    }

    /**
     * @return bool
     */
    public function isStatic(): bool {
        return $this->getDHCP() !== "ipv4";
    }

}
