<?php

namespace Simpnas\Utils;

use InvalidArgumentException;

class Share
{

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
        return (int) $this->data['read only'] === 1;
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

}
