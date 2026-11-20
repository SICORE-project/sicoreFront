<?php

/*
 * Routeur de « php artisan serve » (Laravel l'utilise automatiquement s'il existe).
 *
 * Identique au routeur de Laravel, avec en plus la mise en cache des fichiers
 * statiques par le navigateur : sans cela, chaque page retéléchargeait tous les
 * CSS, JS et polices (12 à 21 fichiers) un par un via le serveur de développement.
 *
 * - Fichiers versionnés (?v=...) et bibliothèques de assets/vendor : gardés 1 an.
 * - Autres fichiers : revérifiés à chaque page (réponse 304 vide s'ils n'ont pas
 *   changé), pour qu'une modification soit toujours visible immédiatement.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && is_file($publicPath.$uri)) {
    $types = [
        'css' => 'text/css; charset=UTF-8', 'js' => 'application/javascript; charset=UTF-8',
        'woff2' => 'font/woff2', 'woff' => 'font/woff', 'ttf' => 'font/ttf',
        'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'webp' => 'image/webp', 'ico' => 'image/x-icon',
    ];
    $extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

    // Autres types de fichiers : comportement d'origine du serveur PHP.
    if (! isset($types[$extension]) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return false;
    }

    $file = $publicPath.$uri;
    $modified = filemtime($file);
    $etag = '"'.dechex($modified).'-'.dechex(filesize($file)).'"';
    $versioned = isset($_GET['v']) || str_starts_with($uri, '/assets/vendor/');

    header('Content-Type: '.$types[$extension]);
    header('ETag: '.$etag);
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $modified).' GMT');
    header('Cache-Control: '.($versioned ? 'public, max-age=31536000, immutable' : 'no-cache'));

    if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
        http_response_code(304);

        return true;
    }

    header('Content-Length: '.filesize($file));
    readfile($file);

    return true;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
