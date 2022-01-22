<?php

namespace Simpnas\Utils;

use InvalidArgumentException;
use RuntimeException;

class User
{

    private string $username;

    public function __construct(string $username, bool $checkExists = true)
    {
        $this->username = $username;

        if ($checkExists) {
            exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $usersList);

            if (!in_array($username, $usersList, true)) {
                throw new InvalidArgumentException('User not found');
            }
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $homeVolume
     * @param array $groups
     * @param string $comment
     * @throws InvalidArgumentException
     * @return User
     */
    public static function create(string $username, string $password, string $homeVolume, array $groups = [], string $comment = ''): User {
        exec("awk -F: '$3 > 999 {print $1}' /etc/passwd", $usersList);
        exec("awk -F: '$3 < 999 {print $1}' /etc/passwd", $systemUsersList);

        if (in_array($username, $usersList, true)) {
            throw new InvalidArgumentException("User $username already exists!");
        }

        if (in_array($username, $systemUsersList, true)) {
            throw new InvalidArgumentException("Can not add user $username because the user is a system user!");
        }

        $homeDir = "/volumes/$homeVolume/users/";
        $userDir = "$homeDir/$username";

        if (
            !file_exists($homeDir)
            && !mkdir($homeDir)
            && !is_dir($homeDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $homeDir));
        }

        exec("mkdir $userDir");
        exec("chmod -R 700 $userDir");

        $comment = escapeshellarg($comment);

        exec ("useradd -g users -d $userDir $username -c $comment -s /bin/false");
        exec ("echo '$password\n$password' | passwd " . escapeshellarg($username));
        exec ("echo '$password\n$password' | smbpasswd -a " . escapeshellarg($username));

        //Create the user under file browser
        exec("systemctl stop filebrowser");
        exec ("filebrowser -d /usr/local/etc/filebrowser.db users add $username $password --scope $userDir");
        exec("systemctl start filebrowser");

        exec ("chown -R $username $userDir");

        $user = new User($username, false);
        $user->setGroups($groups);

        return $user;
    }

    /**
     * @return User[]
     */
    public static function getList(): array {
        exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $usersList);
        asort($usersList);

        return array_map(static function ($username) {
            return new User($username, false);
        }, $usersList);
    }

    /**
     * @return string
     */
    public function getComment(): string {
        return exec(sprintf("cat /etc/passwd | grep %s | awk -F: '{print $5}'", escapeshellarg($this->username))) ?? '';
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool {
        return !empty(exec(sprintf("cat /etc/shadow | grep %s | grep '!'", escapeshellarg($this->username))));
    }

    /**
     * @param string $volumeName
     * @return string
     */
    public function getHomeDirUsage(string $volumeName): string {
        $path = escapeshellarg(sprintf("/volumes/%s/users/%s", $volumeName, $this->username));
        return exec(sprintf("du -sh %s | awk '{print $1}'", $path));
    }

    /**
     * @return string
     */
    public function getUsername(): string {
        return $this->username;
    }

    /**
     * @return string[]
     */
    public function getGroups(): array {
        $cmd = exec(sprintf("groups %s | sed 's/users //g' | sed 's/users//g' | sed 's/\(%s\| : \)//g'", escapeshellarg($this->username), $this->username));

        if (empty($cmd)) {
            return [];
        }

        return explode(' ', $cmd);
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setDisabled(bool $value): void {
        $isDisabled = $this->isDisabled();
        $user = escapeshellarg($this->username);

        if ($isDisabled && !$value) {
            // Enable user
            exec("usermod -U " . $user);
            exec("smbpasswd -e " . $user);
        }

        if (!$isDisabled && $value) {
            // Disable user
            exec("usermod -L " . $user);
            exec("smbpasswd -d " . $user);
        }
    }

    /**
     * @param string $comment
     * @return void
     */
    public function setComment(string $comment): void {
        exec(sprintf("usermod -c %s %s", escapeshellarg($comment), escapeshellarg($this->username)));
    }

    /**
     * @param string[] $groups
     * @return void
     */
    public function setGroups(array $groups): void {
        $groups[] = 'users';
        $list = implode(',', array_unique($groups));

        exec("usermod -G $list " . escapeshellarg($this->username));
    }

    /**
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void {
        exec ("echo '$password\n$password' | passwd " . escapeshellarg($this->username));
        exec ("echo '$password\n$password' | smbpasswd " . escapeshellarg($this->username)); //May not be needed
        // Modify user password under file browser
        exec("systemctl stop filebrowser");
        exec(sprintf("filebrowser -d /usr/local/etc/filebrowser.db users update %s -p %s", escapeshellarg($this->username), escapeshellarg($password)));
        exec("systemctl start filebrowser");
    }

    /**
     * @return void
     */
    public function delete(): void {
        $user = escapeshellarg($this->username);

        exec("smbpasswd -x " . $user);

        exec("deluser --remove-home " . $user);
        //Delete the user under file browser
        exec("systemctl stop filebrowser");
        exec("filebrowser -d /usr/local/etc/filebrowser.db users rm " . $user);
        exec("systemctl start filebrowser");
    }

}
