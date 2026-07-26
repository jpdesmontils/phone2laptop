<?php

function normalizeToken(mixed $value): ?string
{
    return is_string($value) && preg_match('/^[a-f0-9]{32}$/', $value) === 1
        ? $value
        : null;
}

function sessionDirectory(string $token): string
{
    return dirname(__DIR__, 2) . '/uploads/' . $token;
}

function deleteDirectory(string $directory): bool
{
    if (!is_dir($directory)) {
        return true;
    }

    $entries = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
    foreach ($entries as $entry) {
        $path = $entry->getPathname();
        if ($entry->isDir() && !$entry->isLink()) {
            if (!deleteDirectory($path)) {
                return false;
            }
        } elseif (!unlink($path)) {
            return false;
        }
    }

    return rmdir($directory);
}
