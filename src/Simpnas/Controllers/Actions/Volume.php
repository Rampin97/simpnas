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

    public function addSimple(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        $volumeName = trim($data['volumeName']);
        $disk = $data['disk'];
        $password = $data['password'];

        exec("ls /volumes/",$volumesList);

        if (in_array($volumeName, $volumesList, true)) {
            $messages->addMessage('error', "Can not add volume $volumeName as it already exists!");

            return Functions::redirect(
                $request,
                $response,
                'account.volumes.add.simple',
                303
            );
        }

        if (!Functions::isFakeEnabled()) {

            exec("wipefs -a /dev/$disk");
            exec("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
            $diskpart = exec("lsblk -o PKNAME,KNAME,TYPE /dev/$disk | grep part | awk '{print $2}'");
            //WIPE out any superblocks
            exec("mdadm --zero-superblock /dev/$diskpart");
            exec("e2label /dev/$diskpart $volumeName");
            exec("mkdir /volumes/$volumeName");

            if (count($password) > 0) {
                exec("echo $password | cryptsetup -q luksFormat /dev/$diskpart");
                exec("echo $password | cryptsetup open /dev/$diskpart $volumeName");
                exec("mkfs.ext4 -F /dev/mapper/$volumeName");
                $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");
                exec("echo $uuid > /volumes/$volumeName/.uuid_map");
                exec("mount /dev/mapper/$volumeName /volumes/$volumeName");
            } else {
                exec("mkfs.ext4 -F /dev/$diskpart");
                exec("mount /dev/$diskpart /volumes/$volumeName");

                $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");

                $myFile = "/etc/fstab";
                $fh = fopen($myFile, 'ab') or die("can't open file");
                $stringData = "UUID=$uuid /volumes/$volumeName ext4 defaults 0 1\n";
                fwrite($fh, $stringData);
                fclose($fh);
            }

        }

        return Functions::redirect(
            $request,
            $response,
            'account.volumes',
            303
        );
    }

    public function addRaid(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        $volumeName = trim($data['volumeName']);
        $disks = $data['disks'];
        $raid = $data['raid'];

        exec("ls /volumes/",$volumesList);

        if (in_array($volumeName, $volumesList, true)) {
            $messages->addMessage('error', "Can not add volume $volumeName as it already exists!");

            return Functions::redirect(
                $request,
                $response,
                'account.volumes.add.simple',
                303
            );
        }

        $disksCount = count($disks);

        if ($disksCount < 2) {
            $messages->addMessage('error', 'Select at least 2 disks');
            return Functions::redirect(
                $request,
                $response,
                'account.volumes.add.raid',
                303
            );
        }

        if (!Functions::isFakeEnabled()) {

            // Remove Superblocks on selected disks and wipe any partition info
            foreach ($disks as $disk) {
                exec("mdadm --zero-superblock /dev/$disk");
                exec("wipefs -a /dev/$disk");
            }

            //prefix /dev/ to each var in the array so instead of sda it would be /dev/sda
            $fullDiskList = implode(' ', preg_filter('/^/', '/dev/', $disks));

            //get the last md#
            $currentMd = exec("ls /dev/md*");
            //Generate the next /dev/mdX Number
            $newMd = ((int) preg_replace('/\D/', '', $currentMd)) + 1;

            exec("yes | mdadm --create --verbose /dev/md$newMd --level=$raid --raid-devices=$disksCount " . $fullDiskList);

            exec("mkdir /volumes/$volumeName");

            exec("mkfs.ext4 -F /dev/md$newMd");

            exec("mount /dev/md$newMd /volumes/$volumeName");

            // To make sure that the array is reassembled automatically at boot
            // exec ("mdadm --detail --scan | tee -a /etc/mdadm/mdadm.conf");

            $uuid = exec("blkid -o value --match-tag UUID /dev/md$newMd");

            $fh = fopen("/etc/fstab", 'ab') or die("can't open file");
            fwrite($fh, "UUID=$uuid /volumes/$volumeName ext4 defaults 0 0\n");
            fclose($fh);
        }

        return Functions::redirect(
            $request,
            $response,
            'account.volumes',
            303
        );
    }

}
