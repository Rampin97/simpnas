<?php

namespace Simpnas\Controllers\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\SimpleVars;
use Simpnas\Utils\Functions;
use Simpnas\Utils\User as SingleUser;
use Slim\Flash\Messages;


class User
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
                $user = new SingleUser($data['username']);
                $user->delete();
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.users',
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
                $user = new SingleUser($data['username']);
                $user->setComment($data['comment']);
                $user->setGroups($data['groups']);

                if (count($data['password']) > 0) {
                    $user->setPassword($data['password']);
                }
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.users',
            303
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Messages $messages
     * @param SimpleVars $simpleVars
     * @return Response
     */
    public function add(Request $request, Response $response, Messages $messages, SimpleVars $simpleVars): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                SingleUser::create(
                    $data['username'],
                    $data['password'],
                    $simpleVars->getHomeVolume(),
                    $data['groups'] ?? [],
                    $data['comment']
                );
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.users',
            303
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Messages $messages
     * @return Response
     */
    public function disabled(Request $request, Response $response, Messages $messages): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                $user = new SingleUser($data['username']);
                $user->setDisabled(in_array($data['disabled'], ['1', 'yes', 'y'], true));
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.users',
            303
        );
    }

}
