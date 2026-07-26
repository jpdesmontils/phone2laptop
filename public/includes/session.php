<?php

const SESSION_TTL = 1800;

function session_token($value): string
{
    $token = is_string($value) ? $value : '';
    return preg_match('/^[a-f0-9]{32}$/', $token) ? $token : '';
}

function session_root(): string
{
    return dirname(__DIR__, 2) . '/uploads';
}

function session_dir(string $token): string
{
    return session_root() . '/' . $token;
}

function delete_session_directory(string $dir): bool
{
    if (!is_dir($dir)) return true;
    foreach (new FilesystemIterator($dir) as $entry) {
        if (!$entry->isFile() || !unlink($entry->getPathname())) return false;
    }
    return rmdir($dir);
}

function purge_expired_sessions(): void
{
    $root = session_root();
    if (!is_dir($root)) return;
    foreach (new DirectoryIterator($root) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) continue;
        $metadata = read_session_metadata($entry->getPathname());
        if ($metadata && $metadata['expiresAt'] <= time()) {
            delete_session_directory($entry->getPathname());
        }
    }
}

function read_session_metadata(string $dir): ?array
{
    $file = $dir . '/.session.json';
    if (!is_file($file)) return null;
    $metadata = json_decode((string) file_get_contents($file), true);
    return is_array($metadata) && isset($metadata['createdAt'], $metadata['expiresAt']) ? $metadata : null;
}

function open_session(string $token, bool $create = false): ?array
{
    $dir = session_dir($token);
    if (!is_dir($dir) && $create) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) return null;
        $createdAt = time();
        file_put_contents($dir . '/.session.json', json_encode([
            'createdAt' => $createdAt,
            'expiresAt' => $createdAt + SESSION_TTL,
        ]), LOCK_EX);
    }
    $metadata = read_session_metadata($dir);
    if (!$metadata) return null;
    if ($metadata['expiresAt'] <= time()) {
        delete_session_directory($dir);
        return null;
    }
    return $metadata + ['dir' => $dir];
}

function json_error(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}
