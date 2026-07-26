<?php

const UPLOAD_COUNTER_INITIAL_VALUE = 100000;

function uploadCounterPath(): string
{
    return dirname(__DIR__, 2) . '/uploads/.upload-count';
}

function readUploadCount(): int
{
    $handle = fopen(uploadCounterPath(), 'c+');
    if ($handle === false) {
        return UPLOAD_COUNTER_INITIAL_VALUE;
    }

    $count = UPLOAD_COUNTER_INITIAL_VALUE;
    if (flock($handle, LOCK_SH)) {
        rewind($handle);
        $storedValue = trim((string) stream_get_contents($handle));
        if (ctype_digit($storedValue)) {
            $count = max(UPLOAD_COUNTER_INITIAL_VALUE, (int) $storedValue);
        }
        flock($handle, LOCK_UN);
    }

    fclose($handle);
    return $count;
}

function incrementUploadCount(): int
{
    $handle = fopen(uploadCounterPath(), 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        if (is_resource($handle)) {
            fclose($handle);
        }
        return UPLOAD_COUNTER_INITIAL_VALUE;
    }

    $storedValue = trim((string) stream_get_contents($handle));
    $count = ctype_digit($storedValue)
        ? max(UPLOAD_COUNTER_INITIAL_VALUE, (int) $storedValue)
        : UPLOAD_COUNTER_INITIAL_VALUE;
    $count++;

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, (string) $count);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $count;
}
