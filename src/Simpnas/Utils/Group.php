<?php

namespace Simpnas\Utils;

use InvalidArgumentException;
use RuntimeException;

class Group
{

    public string $name;

    public function __construct(string $name, bool $checkExists = true)
    {
        $this->name = $name;

        if ($checkExists) {
            exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $groupList);

            if (!in_array($name, $groupList, true)) {
                throw new InvalidArgumentException('Group not found');
            }
        }
    }

    /**
     * @return string[]
     */
    private static function dockerGroupsList(): array {
        return ["media", "downloads", "docker"];
    }

    /**
     * @return Group[]
     */
    public static function getList(): array {
        exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $groupList);
        asort($groupList);

        return array_map(static function ($name) {
            return new Group($name, false);
        }, $groupList);
    }

    /**
     * @param string $name
     * @return Group
     */
    public static function create(string $name): Group {
        //check if group exists
        exec("awk -F: '$3 > 999 {print $1}' /etc/group", $groupList);
        exec("awk -F: '$3 < 999 {print $1}' /etc/group", $systemGroupsList);

        if (in_array($name, $groupList, true)) {
            throw new InvalidArgumentException("Group $name already exists!");
        }

        if (in_array($name, $systemGroupsList, true)) {
           throw new InvalidArgumentException("Can not add group $name because the group is a system group!");
        }

        if (in_array($name, self::dockerGroupsList())) {
            throw new InvalidArgumentException("Can not add group $name because the group $name is reserved for an App, the following group names are forbiddon media and downloads!");
        }

        exec("addgroup " . escapeshellarg($name));

        return new Group($name, false);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array {
        $cmd = exec(sprintf("awk -F: '/^%s/ {print $4;}' /etc/group", $this->name));

        if (empty($cmd)) {
            return [];
        }

        return array_map(static function ($username) {
            return new User($username, false);
        }, explode(',', $cmd));
    }

    /**
     * @return bool
     */
    public function canEdit(): bool {
        return $this->getName() !== 'admins';
    }

    /**
     * @param string $newName
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return void
     */
    public function setName(string $newName): void {
        if (!$this->canEdit()) {
            throw new RuntimeException("You can't edit or delete this group");
        }

        //check if group exists
        exec("awk -F: '$3 > 999 {print $1}' /etc/group", $groupList);
        exec("awk -F: '$3 < 999 {print $1}' /etc/group", $systemGroupsList);
        exec("find /volumes/*/* -maxdepth 0 -type d -group ". escapeshellarg($this->name) ." -printf '%f\n'",$groupOwnedDirectoriesList);

        $oldName = $this->name;

        if (in_array($newName, $groupList, true)) {
            throw new InvalidArgumentException("Can not rename group $oldName to $newName because group $newName already exists!");
        }

        if (in_array($this->name, $systemGroupsList, true)) {
            throw new InvalidArgumentException("Can not rename group $oldName to $newName because the group is a system group!");
        }

        if (!empty($groupOwnedDirectoriesList)) {
           throw new InvalidArgumentException("Can not rename group $oldName to $newName because the group is a currently being used by a File Share, to rename this group assign the file share a different group and try again!");
        }

        if (in_array($newName, self::dockerGroupsList())) {
            throw new InvalidArgumentException("Can not rename group $oldName to $newName because the group $newName is reserved for an App, the following group names are forbiddon media and downloads!");
        }

        exec(sprintf("groupmod -n %s %s", escapeshellarg($newName), escapeshellarg($oldName)));

        $this->name = $newName;
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return void
     */
    public function delete(): void {
        if (!$this->canEdit()) {
            throw new RuntimeException("You can't edit or delete this group");
        }

        exec("find /volumes/*/* -maxdepth 0 -type d -group ". $this->name ." -printf '%f\n'",$groupOwnedDirectoriesList);

        if(!empty($groupOwnedDirectoriesList)){
           throw new InvalidArgumentException("Can not delete group ". $this->name ." as its currently being used by a file share, to delete this group, delete the file share or change the group on the share to another group and try again!");
        }

        exec("delgroup " . escapeshellarg($this->name));
    }

}
