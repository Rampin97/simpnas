<?php

namespace Simpnas\Utils;


class Disk extends StorageInfo
{

    /**
     * @return string
     */
    private static function getOsDiskId(): string {
        return exec("lsblk -n -o pkname,MOUNTPOINT | grep -w / | awk '{print $1}'");
    }

    public function __construct(string $id)
    {
        parent::__construct($id, 'dev');
    }

    /**
     * @return Disk
     */
    public static function getOsDisk(): Disk {
        return new Disk(self::getOsDiskId());
    }

    /**
     * @return Disk[]
     */
    public static function getStorageDisks(): array {
        exec(sprintf("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | grep -v %s | awk '{print $1}'", escapeshellarg(self::getOsDiskId())), $disksList);

        return array_map(static function (string $id) {
            return new Disk($id);
        }, $disksList);
    }

    /**
     * @return string
     */
    public function getVendor(): string {
        $diskVendor = exec(sprintf("smartctl -i %s | grep 'Model Family:' | awk '{print $3,$4,$5}'", $this->getFullId()));

        if(empty($diskVendor)) {
            $diskVendor = exec(sprintf("smartctl -i %s | grep 'Device Model:' | awk '{print $3,$4,$5}'", $this->getFullId()));
        }

        if(empty($diskVendor)) {
            $diskVendor = exec(sprintf("smartctl -i %s | grep 'Model Number:' | awk '{print $3,$4,$5,$6}'", $this->getFullId()));
        }

        if(empty($diskVendor)) {
            $diskVendor = exec(sprintf("lsblk -n -o kname,type,vendor %s | grep disk  | awk '{print $3}'", $this->getFullId()));
        }

        if(empty($diskVendor)) {
            $diskVendor = exec(sprintf("lsblk -n -o kname,type,model %s | grep disk  | awk '{print $3}'", $this->getFullId()));
        }

        return $diskVendor;
    }

    /**
     * @return string
     */
    public function getSerial(): string {
        $disk_serial = exec(sprintf("smartctl -i %s | grep 'Serial Number:' | awk '{print $3}'", $this->getFullId()));

        if (empty($disk_serial)) {
            return exec(sprintf("lsblk -n -o kname,type,serial %s | grep disk  | awk '{print $3}'", $this->getFullId()));
        }

        return $disk_serial;
    }

    /**
     * @return string
     */
    public function getSize(): string {
        return exec(sprintf("lsblk -n -o kname,type,size %s | grep disk | awk '{print $3}'", $this->getFullId()));
    }

    /**
     * @return bool
     */
    public function isSmart(): bool {
        return empty(exec(sprintf("smartctl -i %s | grep 'Unavailable'", $this->getFullId())));
    }

    /**
     * @return string
     */
    public function getRawInfo(): string {
        $data1 = shell_exec(sprintf("smartctl -i %s | grep -v 'smartctl' | grep -v 'Copyright' | grep -v '=== START'", $this->getFullId()));
        $data2 = shell_exec(sprintf("smartctl -A %s | grep -v 'smartctl' | grep -v 'Copyright' | grep -v '=== START' | grep -v 'revision number' | grep -v 'Vendor Specific SMART'", $this->getFullId()));

        return sprintf('%s%s%s%s', $data1 ?? '', PHP_EOL, PHP_EOL, $data2 ?? '');
    }

    /**
     * @return string
     */
    public function getType(): string {
        $diskType = exec(sprintf("smartctl -i %s | grep 'Rotation Rate:' | awk '{print $3,$4,$5}'", $this->getFullId()));

        switch ($diskType) {
            case '7200 rpm':
            case '5400 rpm':
                return sprintf('HDD (%s)', $diskType);
            case 'Solid State Device':
                return 'SSD';
            default:
                if (empty(exec(sprintf("smartctl -i %s | grep -i 'nvme'", $this->getFullId())))) {
                    return '-';
                }

                return 'NVMe';
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s %s (%sB)', $this->getId(), $this->getVendor(), $this->getSize());
    }

}
