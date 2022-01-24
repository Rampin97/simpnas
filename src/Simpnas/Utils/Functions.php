<?php

namespace Simpnas\Utils;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;

class Functions
{

    public const cacheFolder = __DIR__ . '/../../cache';

    /**
     * @param float $bytes
     * @return string
     */
    public static function formatSize(float $bytes): string {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < ( count( $types ) -1 )) {
            $bytes /= 1024;
            $i++;
        }

        //change $bytes to 2 to get a decimal reading f ex 3.95GB instead 4GB
        return round($bytes, 0) . " " . $types[$i];
    }

    /**
     * @param string $file
     * @param string $string
     * @return void
     */
    public static function deleteLineInFile(string $file, string $string): void {
        $i = 0;
        $array = [];

        $read = fopen($file, 'rb') or die("can't open the file");

        while (!feof($read)) {
            $array[$i] = fgets($read);
            ++$i;
        }

        fclose($read);

        $write = fopen($file, 'wb') or die("can't open the file");

        foreach ($array as $a) {
            if (!str_contains($a, $string)) {
                fwrite($write,$a);
            }
        }

        fclose($write);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $where
     * @param int $status
     * @return ResponseInterface
     */
    public static function redirect(ServerRequestInterface $request, ResponseInterface $response, string $where, int $status): ResponseInterface {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRouteParser();

        $url = strpos($where, "http") === 0 ? $where : $route->fullUrlFor($request->getUri(), $where);

        return $response
            ->withHeader('Location', $url)
            ->withStatus($status);
    }

    /**
     * @return bool
     */
    public static function isCacheEnabled(): bool {
        $env = getenv('SIMPNAS_CACHE', true);

        return $env === false || !in_array(strtolower($env), ['0', 'false', 'no'], true);
    }

    /**
     * @return bool
     */
    public static function isFakeEnabled(): bool {
        $env = getenv('SIMPNAS_FAKE', true);

        if ($env === false) {
            return false;
        }

        return in_array(strtolower($env), ['1', 'true', 'yes'], true);
    }

}
