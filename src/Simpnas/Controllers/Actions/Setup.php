<?php

namespace Simpnas\Controllers\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\SimpleVars;
use Simpnas\Utils\Functions;
use Slim\Flash\Messages;


class Setup
{

    public function step1(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            exec(sprintf('timedatectl set-timezone %s', escapeshellarg($data['timezone'])));
        }

        return Functions::redirect(
            $request,
            $response,
            'setup.step2',
            303
        );
    }

    public function step2(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            $hostname = $data['hostname'];
            $interface = $data['interface'];
            $method = strtolower($data['method']);

            $currentHostname = exec("hostname");

            exec("sed -i 's/$currentHostname/$hostname/g' /etc/hosts");
            exec("hostnamectl set-hostname " . escapeshellarg($hostname));

            exec ("mv /etc/network/interfaces /etc/network/interfaces.save");
            exec ("systemctl enable systemd-networkd");

            if ($method === 'dhcp') {
                $myFile = "/etc/systemd/network/$interface.network";
                $fh = fopen($myFile, 'wb') or die("not able to write to file");
                $stringData = "[Match]\nName=$interface\n\n[Network]\nDHCP=ipv4\n";
                fwrite($fh, $stringData);
                fclose($fh);
                exec("echo '127.0.0.1      localhost' > /etc/hosts");
                exec("echo '127.0.0.2     $hostname' >> /etc/hosts");
                // exec("systemctl restart systemd-networkd > /dev/null &");
            }

            if ($method === 'static') {
                // Only for static option
                $address = $data['address'] ?? '';
                $netmask = $data['netmask'] ?? '';
                $gateway = $data['gateway'] ?? '';
                $dns = $data['dns'] ?? '';

                $myFile = "/etc/systemd/network/$interface.network";
                $fh = fopen($myFile, 'wb') or die("not able to write to file");
                $stringData = "[Match]\nName=$interface\n\n[Network]\nAddress=$address$netmask\nGateway=$gateway\nDNS=$dns\n";
                fwrite($fh, $stringData);
                fclose($fh);
                exec("echo '127.0.0.1      localhost' > /etc/hosts");
                exec("echo '$address     $hostname' >> /etc/hosts");
                exec("systemctl restart systemd-networkd > /dev/null &");
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'setup.step3.simple',
            303
        );
    }

    public function step3simple(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            $volumeName = $data['volumeName'];
            $disk = $data['disk'];

            exec ("wipefs -a /dev/$disk");
            exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
            $diskpart = exec("lsblk -o PKNAME,KNAME,TYPE /dev/$disk | grep part | awk '{print $2}'");
            // WIPE out any superblocks
            exec("mdadm --zero-superblock /dev/$diskpart");
            exec ("mkdir /volumes/$volumeName");
            exec ("mkfs.ext4 -F /dev/$diskpart");
            exec ("e2label /dev/$diskpart $volumeName");
            exec ("mount /dev/$diskpart /volumes/$volumeName");

            $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");
            $myFile = "/etc/fstab";
            $fh = fopen($myFile, 'ab') or die("can't open file");
            $stringData = "UUID=$uuid /volumes/$volumeName ext4 defaults 0 1\n";
            fwrite($fh, $stringData);
            fclose($fh);
        }

        return Functions::redirect(
            $request,
            $response,
            'setup.step4',
            303
        );
    }

    public function step3raid(Request $request, Response $response, Messages $messages): Response
    {
        $data = $request->getParsedBody();

        $volumeName = $data['volumeName'];
        $disks = $data['disks'] ?? [];
        $raid = $data['raid'];

        $disksCount = count($disks);

        if ($disksCount < 2) {
            $messages->addMessage('error', 'Select at least 2 disks');
            return Functions::redirect(
                $request,
                $response,
                'setup.step3.raid',
                303
            );
        }

        if (!Functions::isFakeEnabled()) {

            //find and stop any arrays
            exec("ls /dev/md*",$md_array);

            foreach($md_array as $md){
                exec("mdadm --stop $md");
            }

            // Remove Superblocks on selected disks and wipe any partition info
            foreach($disks as $disk){
                exec("mdadm --zero-superblock /dev/$disk");
                exec ("wipefs -a /dev/$disk");
            }

            // prefix /dev/ to each var in the array so instead of sda it would be /dev/sda
           $fullDiskList = implode(' ', preg_filter('/^/', '/dev/', $disks));

            exec("yes | mdadm --create --verbose /dev/md1 --level=$raid --raid-devices=$disksCount " . $fullDiskList);

            exec ("mkdir /volumes/$volumeName");

            exec ("mkfs.ext4 -F /dev/md1");

            exec ("mount /dev/md1 /volumes/$volumeName");

            // To make sure that the array is reassembled automatically at boot
            // exec ("mdadm --detail --scan | tee -a /etc/mdadm/mdadm.conf");

            $uuid = exec("blkid -o value --match-tag UUID /dev/md1");

            $myFile = "/etc/fstab";
            $fh = fopen($myFile, 'ab') or die("can't open file");
            $stringData = "UUID=$uuid /volumes/$volumeName ext4 defaults 0 0\n";
            fwrite($fh, $stringData);
            fclose($fh);
        }

        return Functions::redirect(
            $request,
            $response,
            'setup.step4',
            303
        );
    }

    public function step4(Request $request, Response $response, SimpleVars $simpleVars): Response {

        $data = $request->getParsedBody();

        $simpleVars->setDatabaseKey(SimpleVars::DBKEY_SETUP, true);

        if (!Functions::isFakeEnabled()) {
            $volumeName = exec("ls /volumes");
            $username = $data['username'];
            $password = $data['password'];

            $myFile = "/etc/samba/shares/share";
            $fh = fopen($myFile, 'wb') or die("not able to write to file");
            $stringData = "[share]\n   comment = Shared files\n   path = /volumes/$volumeName/share\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @users\n   force group = users\n   create mask = 0660\n   directory mask = 0770";
            fwrite($fh, $stringData);
            fclose($fh);

            exec ("mkdir /volumes/$volumeName/docker");
            exec ("mkdir /volumes/$volumeName/users");
            exec ("mkdir /volumes/$volumeName/share");
            exec ("chmod 770 /volumes/$volumeName/share");


            $fh = fopen("/etc/samba/shares/users", 'wb') or die("not able to write to file");
            $stringData = "[users]\n   comment = Users Home Folders\n   path = /volumes/$volumeName/users\n   read only = no\n   create mask = 0600\n   directory mask = 0700\n";
            fwrite($fh, $stringData);
            fclose($fh);

            $fh = fopen("/etc/samba/shares.conf", 'ab') or die("not able to write to file");
            fwrite($fh, "\ninclude = /etc/samba/shares/users");
            fwrite($fh, "\ninclude = /etc/samba/shares/share");
            fclose($fh);

            //Check to see if theres already a user added and delete that user
            $existingUsername = exec("cat /etc/passwd | grep 1000 | awk -F: '{print $1}'");
            if (!empty($existingUsername)){
                exec("deluser --remove-home $existingUsername");
            }

            exec ("mkdir /volumes/$volumeName/users/$username");
            exec ("chmod -R 700 /volumes/$volumeName/users/$username");

            exec ("chgrp users /volumes/$volumeName/share");
            //Create the new user UNIX way
            exec ("useradd -g users -d /volumes/$volumeName/users/$username $username");
            exec ("echo '$password\n$password' | passwd $username");
            exec ("usermod -a -G admins $username");
            exec ("echo '$password\n$password' | smbpasswd -a $username");
            exec ("chown -R $username /volumes/$volumeName/users/$username");


            //Create the user under file browser
            $fb = sprintf("filebrowser -d /usr/local/etc/filebrowser.db users add %s %s --perm.admin=true", escapeshellarg($username), escapeshellarg($password));
            exec("systemctl stop filebrowser");
            exec ($fb);
            exec("systemctl start filebrowser");
        }

        return Functions::redirect(
            $request,
            $response,
            'setup.complete',
            303
        );
    }

}
