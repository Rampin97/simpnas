<?php

namespace Simpnas\Utils;

use InvalidArgumentException;
use RuntimeException;
use Simpnas\Services\AbstractService;

class Share
{

    private const systemShares = ["users", "docker"];

    private string $name;

    private array $data;

    public function __construct(string $name, bool $checkExists = true)
    {
        $this->name = $name;

        if ($checkExists && !file_exists("/etc/samba/shares/$name")) {
            throw new InvalidArgumentException('Share not found');
        }

        $this->loadFile();
    }

    private static function generateSambaConfig(string $name, string $volume, string $group, string $comment, bool $readOnly): string {
        $sharePath = "/volumes/$volume/$name";

        $sambaConfig = [
            "[$name]",
            "   comment = " . $comment,
            "   path = " . $sharePath,
            "   browsable = yes",
            "   writable = yes",
            "   guest ok = yes",
            $readOnly ? "   read only = yes" : "   read only = no",
            "   valid users = @$group",
            "   force group = $group",
            "   create mask = 0660",
            "   directory mask = 0770",
        ];

        return implode(PHP_EOL, $sambaConfig);
    }

    /**
     * @param array $services
     * @param string $name
     * @param string $volume
     * @param string $group
     * @param string $comment
     * @param bool $readOnly
     * @return Share
     */
    public static function create(array $services, string $name, string $volume, string $group, string $comment, bool $readOnly): Share {
        $sharePath = "/volumes/$volume/$name";

        //Checks
        exec("ls /etc/samba/shares",$existingSharesList);
        exec("find /volumes/*/* -maxdepth 0 -type d -printf '%f\n'",$existingDirectoriesList);
        $mounted = exec("df | grep $volume");

        if (in_array($name, $existingSharesList, true)) {
            throw new RuntimeException("The share with the name $name already exists can not add share!");
        }

        if (in_array($name, $existingDirectoriesList, true)) {
            throw new RuntimeException("Directory $name already exists can not add share with the name $name, would you like to share the existing directory instead (Note this will update the permissions to user root with RWX and group to RWX and everyone else to or would you like to delete the directory and its contents and create a new directory?");
        }

        if (in_array($name, [...self::restrictedShares($services), ...self::systemShares], true)) {
            throw new RuntimeException("Can not create the share $name as it shares the same share name as an app. The followng share names are forbiddon media, downloads, docker and users!");
        }

        if (empty($mounted)) {
            throw new RuntimeException("Can not create the share $name because the volume $volume is not mounted");
        }

        exec("mkdir $sharePath");
        exec("chgrp $group $sharePath");
        exec("chmod 0770 $sharePath");

        $fh = fopen("/etc/samba/shares/$name", 'wb') or die("not able to write to file");
        fwrite($fh, self::generateSambaConfig($name, $volume, $group, $comment, $readOnly));
        fclose($fh);

        $fh = fopen("/etc/samba/shares.conf", 'ab') or die("not able to write to file");
        fwrite($fh, PHP_EOL . "include = /etc/samba/shares/$name");
        fclose($fh);

        exec("systemctl restart smbd");
        exec("systemctl restart nmbd");

        return new self($name, false);
    }

    /**
     * @return void
     */
    private function loadFile(): void {
        $this->data = parse_ini_file("/etc/samba/shares/" . $this->name) ?? [];
    }

    /**
     * @return Share[]
     */
    public static function getList(): array {
        exec("ls /etc/samba/shares", $shareList);

        return array_map(static function ($name) {
            return new Share($name, false);
        }, $shareList);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getComment(): string {
        $comment = $this->data['comment'];

        return empty($comment) ? '' : $comment;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool {
        return in_array((string) $this->data['read only'], ['yes', '1', 'y', 'true'], true);
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->data['path'];
    }

    /**
     * @return string
     */
    public function getVolume(): string {
        return basename(dirname($this->getPath()));
    }

    /**
     * @return string
     */
    public function getGroup(): string {
        $group = $this->data['force group'];

        return empty($group) ? '' : $group;
    }

    /**
     * @return string
     */
    public function getUsedSpace(): string {
        return exec(sprintf("du -sh %s | awk '{print $1}'", $this->getPath()));
    }

    /**
     * @param AbstractService[] $services
     * @return string[]
     */
    private static function restrictedShares(array $services): array {
        $dockerShares = [];

        foreach ($services as $service) {
            $shareName = $service->getShareName();
            if ($shareName !== null && $service->isInstalled()) {
                $dockerShares[] = $shareName;
            }
        }

        return $dockerShares;
    }

    /**
     * @param AbstractService[] $services
     * @throws RuntimeException
     * @return void
     */
    public function delete(array $services): void {
        $name = $this->name;

        if (in_array($name, self::restrictedShares($services), true)) {
            throw new RuntimeException("Can not delete the share $name as it shares the same share name as an app thats using it, try deleting the app then deleting the share");
        }

        if (in_array($name, self::systemShares, true)) {
            throw new RuntimeException("Can not delete the share $name as it is a system share!");
        }

        $path = exec(sprintf("find %s -name %s", "/volumes/*/$name", $name));

        exec("rm -rf " . escapeshellarg($path));
        exec("rm -f " . escapeshellarg("/etc/samba/shares/$name"));

        Functions::deleteLineInFile("/etc/samba/shares.conf", $name);

        exec("systemctl restart smbd");
        exec("systemctl restart nmbd");
    }

    /**
     * @param array $services
     * @param string $name
     * @param string $volume
     * @param string $group
     * @param string $comment
     * @param bool $readOnly
     * @throws RuntimeException
     * @return void
     */
    public function edit(array $services, string $name, string $volume, string $group, string $comment, bool $readOnly): void {
        $currentName = $this->name;

        $sharePath = "/volumes/$volume/$name";
        $currentSharePath = sprintf("/volumes/%s/%s", $this->getVolume(), $currentName);

        if ($name !== $currentName || $volume !== $this->getVolume()) {

            //Name Checks
            exec("ls /etc/samba/shares",$existingSharesList);
            exec("find /volumes/*/* -maxdepth 0 -type d -printf '%f\n'",$existingDirectoriesList);

            if (in_array($name, $existingSharesList, true)) {
                throw new RuntimeException("The share with the name $name already exists can not rename share $currentName!");
            }

            if (in_array($name, $existingDirectoriesList, true)) {
                throw new RuntimeException("Directory $name already exists can not rename share $currentName to $name!");
            }

            if(in_array($name, [...self::restrictedShares($services), ...self::systemShares], true)) {
                throw new RuntimeException("Can not rename share $currentName to $name the share $name shares the same share name as an app. The followng share names are forbiddon media, downloads, docker and users!");
            }

            exec(sprintf("mv %s %s", escapeshellarg($currentSharePath), escapeshellarg($sharePath)));
            exec(sprintf("mv %s %s", escapeshellarg("/etc/samba/shares/$currentName"), escapeshellarg("/etc/samba/shares/$name")));
            Functions::deleteLineInFile("/etc/samba/shares.conf", $currentName);

            $fh = fopen("/etc/samba/shares.conf", 'ab') or die("not able to write to file");

            fwrite($fh, PHP_EOL . "include = /etc/samba/shares/$name");
            fclose($fh);

        }

        //Update User Group Permssions no matter what
        exec("chown -R root:$group $sharePath");

        $fh = fopen("/etc/samba/shares/$name", 'wb') or die("not able to write to file");
        fwrite($fh, self::generateSambaConfig($name, $volume, $group, $comment, $readOnly));
        fclose($fh);

        exec("systemctl restart smbd");
        exec("systemctl restart nmbd");

        // Reload
        $this->name = $name;
        $this->loadFile();
    }

}
