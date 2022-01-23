<?php

namespace Simpnas\Controllers\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\Utils\Functions;
use Simpnas\Utils\Group as SingleGroup;
use Slim\Flash\Messages;


class Group
{

    /**
     * @param Request $request
     * @param Response $response
     * @param Messages $messages
     * @return Response
     */
    public function delete(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                $group = new SingleGroup($data['name']);
                $group->delete();
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.groups',
            303
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Messages $messages
     * @return Response
     */
    public function edit(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                $group = new SingleGroup($data['currentName']);
                $group->setName($data['newName']);
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.groups',
            303
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Messages $messages
     * @return Response
     */
    public function add(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                SingleGroup::create(trim($data['name']));
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.groups',
            303
        );
    }

}
