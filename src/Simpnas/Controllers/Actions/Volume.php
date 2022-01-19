<?php

namespace Simpnas\Controllers\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\Utils\Functions;
use Slim\Flash\Messages;


class Volume
{

    public function delete(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            $volumeName = $data['volume'];
            //check to make sure no shares are linked to the volume
            //if so then choose cancel or give the option to move them to a different volume if another one exists and it will fit onto the new volume
            //the code to do that here
            $diskpart = exec("findmnt -o SOURCE --target /volumes/$volumeName");
            $disk = exec("lsblk -o pkname $diskpart");
            $uuid = exec("blkid -o value --match-tag UUID $diskpart");

            exec("ls /volumes/$volumeName | grep -v lost+found", $directoryList);

            if(!empty($directoryList)) {
                $messages->addMessage('error', "Can not delete volume $volumeName as there are files shares, please delete the file shares accociated to volume $volumeName and try again!");
            } else {
                //UNMOUNTED CRYPT
                //Check to see if its an unmounted crypt volume if so replace $disk with new $disk
                if (file_exists("/volumes/$volumeName/ -name .uuid_map")){
                    $diskPartUuid = exec("cat /volumes/$volumeName/.uuid_map");
                    $disk = exec("lsblk -o PKNAME,NAME,UUID | grep $diskPartUuid | awk '{print $1}'");
                }

                exec("umount -l /volumes/$volumeName");
                exec("cryptsetup close $volumeName");
                exec("rm -rf /volumes/$volumeName");

                //RAID Remove
                //Get Disks and Partition number in the array 
                exec("lsblk -o PKNAME,PATH,TYPE | grep $diskpart | awk '{print \"/dev/\"$1}'",$diskPartList);

                exec("mdadm --stop $diskpart");
                exec("mdadm --zero-superblock " . implode(' ', $diskPartList));

                foreach($diskPartList as $dp){
                    $disk = exec("lsblk -n -o PKNAME,PATH | grep $dp | awk '{print $1}'");
                    exec ("wipefs -a /dev/$disk");
                }

                //END RAID Remove

                exec ("wipefs -a /dev/$disk");

                Functions::deleteLineInFile("/etc/fstab", $uuid);

            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.volumes',
            303
        );
    }

    public function unlock(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            $disk = $data['disk'];
            $volume = $data['volume'];
            $password = $data['password'];

            exec("echo $password | cryptsetup luksOpen /dev/disk/by-uuid/$disk $volume");
            $cryptStatus = exec("cryptsetup status $volume | grep inactive");

            if (empty($cryptStatus)){
                exec("mount /dev/mapper/$volume /volumes/$volume");
                $messages->addMessage('success', "Unlocked Encrypted volume $volume successfully!");
            } else {
                $messages->addMessage('error', "Wrong Secret Key Given!");
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.volumes',
            303
        );
    }

    public function addSimple(Request $request, Response $response): Response {
        return $response;
    }

    public function addRaid(Request $request, Response $response): Response {
        return $response;
    }

}
