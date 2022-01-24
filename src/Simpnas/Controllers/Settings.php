<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class Settings
{

    public function __construct(private Twig $twig)
    {
    }

    public function dateTime(Response $response): Response {
        $currentTimezone = exec("timedatectl show -p Timezone --value");
        $currentLocalDate = exec("timedatectl show -p TimeUSec --value | awk '{print $2}'");
        $currentLocalTime = exec("timedatectl show -p TimeUSec --value | awk '{print $3}'");
        exec("timedatectl list-timezones", $timezoneList);

        $data = [
            'title' => ['Settings', 'Date & Time'],
            'timezoneList' => $timezoneList,
            'currentTimezone' => $currentTimezone,
            'currentLocalDate' => $currentLocalDate,
            'currentLocalTime' => $currentLocalTime
        ];

        return $this->twig->render($response, 'account/settings/datetime.twig', $data);
    }

    public function network(Response $response): Response {
        return $this->twig->render($response, 'account/settings/network.twig', [
            'title' => ['Settings', 'Network']
        ]);
    }

}
