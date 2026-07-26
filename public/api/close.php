<?php
require_once __DIR__ . '/../includes/storage.php';

header('Content-Type: application/json');

// Autoriser uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier le Content-Type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
// Normaliser (enlever les espaces, paramètres comme charset)
$contentType = strtolower(trim(explode(';', $contentType)[0]));

if ($contentType !== 'application/json') {
    http_response_code(415);
    echo json_encode(['error' => 'Type de média non supporté. Utilisez application/json.']);
    exit;
}

// Lire et décoder le corps
$input = file_get_contents('php://input');
if ($input === false || $input === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Corps de requête vide']);
    exit;
}

$payload = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON invalide']);
    exit;
}

$token = normalizeToken($payload['token'] ?? null);
$action = $payload['action'] ?? 'delete';

if ($token === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Token invalide']);
    exit;
}

$dir = sessionDirectory($token);

switch ($action) {
    case 'delete':
        if (!deleteDirectory($dir)) {
            http_response_code(500);
            echo json_encode(['error' => 'Suppression incomplète']);
            exit;
        }
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue']);
}
