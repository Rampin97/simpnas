<?php

namespace Simpnas\Controllers\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\SimpleVars;
use Simpnas\Utils\Functions;
use Simpnas\Utils\Share as SingleShare;
use Slim\Flash\Messages;


class Share
{

    /**
     * @param Request $request
     * @param Response $response
     * @param Messages $messages
     * @param SimpleVars $simpleVars
     * @return Response
     */
    public function delete(Request $request, Response $response, Messages $messages, SimpleVars $simpleVars): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                $group = new SingleShare($data['name']);
                $group->delete($simpleVars->getApps());
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.shares',
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
    public function edit(Request $request, Response $response, Messages $messages, SimpleVars $simpleVars): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            try {
                $group = new SingleShare($data['currentName']);
                $group->edit(
                    $simpleVars->getApps(),
                    $data['newName'],
                    $data['volume'],
                    $data['group'],
                    trim($data['comment']),
                    in_array($data['readOnly'] ?? '0', ['yes', '1', 'y', 'true'], true)
                );
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.shares',
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
                SingleShare::create(
                    $simpleVars->getApps(),
                    $data['name'],
                    $data['volume'],
                    $data['group'],
                    trim($data['comment']),
                    in_array($data['readOnly'] ?? '0', ['yes', '1', 'y', 'true'], true)
                );
            } catch (Exception $e) {
                $messages->addMessage('error', $e->getMessage());
            }
        }

        return Functions::redirect(
            $request,
            $response,
            'account.shares',
            303
        );
    }

}
