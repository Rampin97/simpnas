<?php

namespace Simpnas;


use JsonException;
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
use Twig\TwigFunction;

class SimpleVars extends AbstractExtension
{

    private const dbFile = __DIR__ . '/../config.json';

    private array $databaseData = [];

    public const DBKEY_SETUP = 'setupComplete';

    public function __construct()
    {
        $this->loadDatabase();
    }

    private function loadDatabase(): void {
        if (file_exists(self::dbFile)) {
            try {
                $this->databaseData = json_decode(
                    file_get_contents(self::dbFile),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                $this->databaseData = [];
            }
        } else {
            $this->databaseData = [];
        }
    }

    private function saveDatabase(): void {
        try {
            file_put_contents(self::dbFile, json_encode($this->databaseData, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function emptyString($data): string {
        return ($data === false) ? "" : $data;
    }

    /**
     * @param string $cmd
     * @return string
     */
    private function execute(string $cmd): string {
        return $this->emptyString(exec($cmd));
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function getDatabaseKey(string $key, $default = null) {
        if (array_key_exists($key, $this->databaseData)) {
            return $this->databaseData[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function setDatabaseKey(string $key, $data): void {
        $this->databaseData[$key] = $data;
        $this->saveDatabase();
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        $list = [
            'getHostname',
            'getPrimaryIp',
            'getDockerVolume',
            'getHomeVolume',
            'getOsDisk',
            'getApps',
            'getServerMemoryUsage',
            'getServerCpuUsage',
            'getDatabaseKey',
            'setDatabaseKey'
        ];

        return array_map(function ($name) {
            return new TwigFunction($name, [$this, $name]);
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
     * @return float
     */
    public function getServerMemoryUsage(): float {
        $free = shell_exec('free');
        $free = trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);

        return $mem[2]/$mem[1]*100;
    }

    /**
     * @return float
     */
    public function getServerCpuUsage(): float {
        $load = sys_getloadavg();
        return $load[0];
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
