<?php

namespace Simpnas\Controllers\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\Utils;


class Setup
{

    public function step1(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        if (!Utils::isFakeEnabled()) {
            exec(sprintf('timedatectl set-timezone %s', escapeshellarg($data['timezone'])));
        }

        return Utils::redirect(
            $request,
            $response,
            'setup.step2',
            303
        );
    }

    public function step2(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        if (!Utils::isFakeEnabled()) {
            $hostname = $data['hostname'];
            $interface = $data['interface'];
            $method = strtolower($data['method']);
            $address = $data['address'];
            $netmask = $data['netmask'];
            $gateway = $data['gateway'];
            $dns = $data['dns'];

            $currentHostname = exec("hostname");

            exec("sed -i 's/$currentHostname/$hostname/g' /etc/hosts");
            exec("hostnamectl set-hostname $hostname");

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

        return Utils::redirect(
            $request,
            $response,
            'setup.step3',
            303
        );
    }

}
