<?php

namespace Simpnas;

use Simpnas\Services\AbstractService;
use Simpnas\Services\BitwardenRS;
use Simpnas\Services\DAAPD;
use Simpnas\Services\HomeAssistant;
use Simpnas\Services\Jellyfin;
use Simpnas\Services\Nextcloud;
use Simpnas\Services\NginxProxyManager;
use Simpnas\Services\PhotoPrism;
use Simpnas\Services\Transmission;
use Simpnas\Services\UnifiController;
use Twig\Extension\AbstractExtension;

class SimpleVars extends AbstractExtension
{

    /**
     * @param mixed $data
     * @return string
     */
    private function emptyString($data): string {
        return ($data === false) ? "" : $data;
    }

    private function execute(string $cmd): string {
        return $this->emptyString(exec($cmd));
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        $list = ['getHostname', 'getPrimaryIp', 'getDockerVolume', 'getHomeVolume', 'getOsDisk', 'getApps'];

        return array_map(function ($name) {
            return [$name, [$this, $name]];
        }, $list);
    }

    /**
     * @return string
     */
    public function getHostname(): string {
        return $this->emptyString(gethostname());
    }

    /**
     * @return string
     */
    public function getPrimaryIp(): string {
        return $this->execute("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");
    }

    /**
     * @return string
     */
    public function getDockerVolume(): string {
        return $this->execute("find /volumes/*/docker -name docker | awk -F/ '{print $3}'");
    }

    /**
     * @return string
     */
    public function getHomeVolume(): string {
        return $this->execute("find /volumes/*/users -name users | awk -F/ '{print $3}'");
    }

    /**
     * @return string
     */
    public function getOsDisk(): string {
        return $this->execute("findmnt -n -o SOURCE --target / | cut -c -8");
    }

    /**
     * @return AbstractService[]
     */
    public function getApps(): array {
        return [
            new Nextcloud(),
            new Jellyfin(),
            new PhotoPrism(),
            new NginxProxyManager(),
            new DAAPD(),
            new Transmission(),
            new BitwardenRS(),
            new HomeAssistant(),
            new UnifiController()
        ];
    }

}
