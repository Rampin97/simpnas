<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\Utils\Group;
use Simpnas\Utils\User;
use Slim\Views\Twig;

class Shares
{

    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function users(Response $response): Response {
        return $this->twig->render($response, 'account/users/index.twig', [
            'title' => ['Users'],
            'userList' => User::getList()
        ]);
    }

    public function createUser(Response $response): Response {
        return $this->twig->render($response, 'account/users/create.twig', [
            'title' => ['Users', 'Create user'],
            'groupList' => Group::getList()
        ]);
    }

    public function editUser(string $username, Response $response): Response {
        return $this->twig->render($response, 'account/users/edit.twig', [
            'title' => ['Users', 'Edit user'],
            'groupList' => Group::getList(),
            'editUser' => new User($username)
        ]);
    }

    public function groups(Response $response): Response {
        return $this->twig->render($response, 'account/groups/index.twig', [
            'title' => ['Groups'],
            'groupList' => Group::getList()
        ]);
    }

    public function createGroup(Response $response): Response {
        return $this->twig->render($response, 'account/groups/create.twig', [
            'title' => ['Groups', 'Create group']
        ]);
    }

    public function editGroup(string $name, Response $response): Response {
        return $this->twig->render($response, 'account/groups/edit.twig', [
            'title' => ['Groups', 'Edit group'],
            'editGroup' => new Group($name)
        ]);
    }

}
