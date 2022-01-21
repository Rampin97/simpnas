<?php

namespace Simpnas\Utils;


class Volume extends StorageInfo
{

    public const volumesPath = 'volumes';

    public function __construct(string $id)
    {
        parent::__construct($id, self::volumesPath);
    }



    /**
     * @return Volume[]
     */
    public static function getVolumeList(): array {
        exec("ls /volumes", $volumeList);

        return array_map(static function (string $id) {
            return new Volume($id);
        }, $volumeList);
    }

    /**
     * @return bool
     */
    public function isMounted(): bool {
        return !empty(exec("df | grep " . $this->getId()));
    }

    /**
     * @return Disk
     */
    public function getDisk(): Disk {
        if ($this->isMounted()) {
            return new Disk(basename(exec("findmnt -n -o SOURCE --target " . $this->getFullId())));
        }

        $file = sprintf("%s/.uuid_map", $this->getFullId());

        if (file_exists($file)) {
            return new Disk(exec("cat " . $file));
        }

        return new Disk(basename(exec(sprintf("cat /etc/fstab | grep %s | awk '{print $1}'", $this->getId()))));
    }

    /**
     * @return int
     */
    public function getRaid(): int {
        $isRaid = !empty(exec(sprintf("lsblk -o PKNAME,PATH,TYPE | grep %s | grep raid", $this->getDisk()->getId())));

        if ($isRaid) {
            switch (exec(sprintf("lsblk -o PKNAME,PATH,TYPE | grep %s | grep raid | awk '{print $3}'", $this->getDisk()->getId()))) {
                case 'raid0':
                    return 0;
                case 'raid1':
                    return 1;
                case 'raid5':
                    return 5;
                case 'raid6':
                    return 6;
                case 'raid10':
                    return 10;
            }
        }

        return -1;
    }

    /**
     * @return string
     */
    public function getRaidRawInfo(): string {
        if ($this->getRaid() >= 0) {
            return shell_exec("mdadm -D " . escapeshellarg($this->getDisk()->getFullId()));
        }

        return '';
    }

    /**
     * @return Disk[]
     */
    public function getRaidDisks(): array {
        if ($this->getRaid() === -1) {
            return [];
        }

        exec(sprintf("lsblk -o PKNAME,PATH,TYPE | grep %s | awk '{print $1}'", $this->getDisk()->getId()),$diskList);

        return array_map(static function ($id) {
            return new Disk($id);
        }, $diskList);
    }

    /**
     * @param int $print
     * @param bool $formatted
     * @return string
     */
    private function getSpaceInfo(int $print, bool $formatted = true): string {
        $cmd = $formatted ? 'df -h' : 'df';
        return exec(sprintf("%s | grep -w %s | awk '{print $%s}'", $cmd, $this->getFullId(), $print));
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getTotalSpace(bool $formatted = true): string {
        return $this->getSpaceInfo(2, $formatted);
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getUsedSpace(bool $formatted = true): string {
        return $this->getSpaceInfo(3, $formatted);
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getFreeSpace(bool $formatted = true): string {
        return $this->getSpaceInfo(4, $formatted);
    }

    /**
     * @return float
     */
    public function getUsedSpacePercent(): float {
        return (float) str_replace('%', '', exec(sprintf("df | grep -w %s | awk '{print $5}'", $this->getFullId())));
    }

    /**
     * @return bool
     */
    public function isCrypt(): bool {
        return !empty(exec(sprintf("lsblk -o PKNAME,PATH,TYPE | grep %s | grep crypt", $this->getDisk()->getId())));
    }

    /**
     * @return bool
     */
    public function canUnlock(): bool {
        return file_exists($this->getFullId() . "/.uuid_map");
    }

}
