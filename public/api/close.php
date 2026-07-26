<?php
require_once dirname(__DIR__) . '/includes/session.php';

// Autoriser uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier le Content-Type
$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
// Normaliser (enlever les espaces, paramètres comme charset)
$contentType = strtolower(trim(explode(';', $contentType)[0]));

if ($contentType !== 'application/json') {
    http_response_code(415);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Type de média non supporté. Utilisez application/json.']);
    exit;
}

// Lire et décoder le corps
$input = file_get_contents('php://input');
if ($input === false || $input === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Corps de requête vide']);
    exit;
}

$payload = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'JSON invalide']);
    exit;
}

$token = session_token($payload['token'] ?? '');
$action = $payload['action'] ?? 'delete';

if (!$token || strlen($token) < 32) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Token invalide']);
    exit;
}

$dir = session_dir($token);

if (!is_dir($dir)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Session introuvable']);
    exit;
}

switch ($action) {
    case 'delete_file':
        $name = $payload['name'] ?? '';
        if (!is_string($name) || $name === '' || basename($name) !== $name || $name === 'bloc-notes.txt') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Nom de fichier invalide']);
            exit;
        }

        $file = $dir . '/' . $name;
        if (!is_file($file)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Fichier introuvable']);
            exit;
        }

        if (!unlink($file)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer le fichier']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        break;

    case 'delete':
        if (!delete_session_directory($dir)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer la session']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Action inconnue']);
}
