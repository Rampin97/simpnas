<?php

namespace Simpnas;

use Simpnas\Utils\Functions;
use Slim\Flash\Messages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class User extends AbstractExtension
{

    private Messages $messages;

    public function __construct(Messages $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isLoggedIn', [$this, 'isLoggedIn']),
            new TwigFunction('getUsername', [$this, 'getUsername'])
        ];
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool {
        return array_key_exists('logged', $_SESSION) && $_SESSION['logged'];
    }

    public function getUsername(): string {
        return $this->isLoggedIn() ? $_SESSION['username'] : '';
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login(string $username, string $password): bool {
        $file = __DIR__ . '/../verify.sh';
        $cmd = sprintf('bash %s %s %s', $file, escapeshellarg($username), escapeshellarg($password));
        $result = exec($cmd);

        if ($result === "ok" || Functions::isFakeEnabled()) {
            $_SESSION['logged'] = true;
            $_SESSION['username'] = $username;

            return true;
        }

        $_SESSION['logged'] = false;

        switch ($result) {
            case "user_not_found":
                $this->messages->addMessage('error', 'User not found');
                break;
            case "wrong_password":
                $this->messages->addMessage('error', 'Wrong password');
                break;
            default:
                $this->messages->addMessage('error', 'Something went wrong!');
                break;
        }

        return false;
    }

    /**
     * @return void
     */
    public function logout(): void {
        // $_SESSION['logged'] = false;
        // $_SESSION['username'] = '';

        session_unset();
        session_destroy();
        // session_start();
    }

}
