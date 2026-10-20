<?php
declare(strict_types=1);

/**
 * Fail when a PHPStan baseline gains entries or raises a count compared with a reference.
 *
 * Usage:
 *   php dev/tests/check-phpstan-baseline.php REFERENCE.neon CURRENT.neon
 *   php dev/tests/check-phpstan-baseline.php --git-ref=SHA CURRENT.neon
 *
 * With --git-ref the reference is CURRENT's path at that commit. An empty or all-zero ref, or a
 * commit without a baseline, means this change introduces the reviewed baseline.
 */

/**
 * @return array<string, int> Entry key => count
 */
function readBaseline(string $contents): array
{
    /** @var list<array<string, string>> $blocks */
    $blocks = [];
    $current = null;
    foreach (preg_split('/\R/', $contents) ?: [] as $line) {
        if (preg_match('/^\s*-\s*$/', $line) === 1) {
            if ($current !== null) {
                $blocks[] = $current;
            }
            $current = [];
            continue;
        }
        if ($current === null
            || preg_match('/^\s*(message|rawMessage|identifier|count|path):\s*(.*)$/', $line, $match) !== 1
        ) {
            continue;
        }
        $value = trim($match[2]);
        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            $value = str_replace("''", "'", substr($value, 1, -1));
        } elseif (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            $value = stripcslashes(substr($value, 1, -1));
        }
        $current[$match[1]] = $value;
    }
    if ($current !== null) {
        $blocks[] = $current;
    }

    $entries = [];
    foreach ($blocks as $block) {
        $key = implode("\n", [
            $block['message'] ?? $block['rawMessage'] ?? '',
            $block['identifier'] ?? '',
            $block['path'] ?? '',
        ]);
        $entries[$key] = ($entries[$key] ?? 0) + (int) ($block['count'] ?? 1);
    }

    return $entries;
}

/**
 * Read a file at a commit, or null when the commit does not contain it.
 */
function readAtRef(string $ref, string $path): ?string
{
    $process = proc_open(['git', 'show', $ref . ':' . $path], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to run git.');
    }
    $output = (string) stream_get_contents($pipes[1]);
    $error = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) === 0) {
        return $output;
    }
    if (str_contains($error, 'exists on disk, but not in') || str_contains($error, 'does not exist in')) {
        return null;
    }
    throw new RuntimeException(sprintf('Unable to read %s at %s: %s', $path, $ref, trim($error)));
}

/**
 * @param list<string> $arguments
 * @return int Exit status
 */
function checkBaseline(array $arguments): int
{
    $gitRef = null;
    $paths = [];
    foreach ($arguments as $argument) {
        if (str_starts_with($argument, '--git-ref=')) {
            $gitRef = substr($argument, strlen('--git-ref='));
        } else {
            $paths[] = $argument;
        }
    }
    $currentPath = $paths[$gitRef === null ? 1 : 0] ?? '';
    if ($currentPath === '' || !is_file($currentPath)) {
        throw new RuntimeException('Pass the current baseline file.');
    }
    if ($gitRef === null) {
        if (!is_file($paths[0])) {
            throw new RuntimeException('Pass the reference baseline file.');
        }
        $referenceContents = (string) file_get_contents($paths[0]);
    } elseif ($gitRef === '' || preg_match('/^0+$/', $gitRef) === 1) {
        echo "No reference commit; the current baseline is treated as the reviewed starting point.\n";
        return 0;
    } else {
        $referenceContents = readAtRef($gitRef, $currentPath);
        if ($referenceContents === null) {
            echo "{$currentPath} does not exist at {$gitRef}; this change introduces the reviewed baseline.\n";
            return 0;
        }
    }

    $reference = readBaseline($referenceContents);
    $current = readBaseline((string) file_get_contents($currentPath));
    $growth = [];
    foreach ($current as $key => $count) {
        $allowed = $reference[$key] ?? 0;
        if ($count > $allowed) {
            [$message, $identifier, $path] = explode("\n", $key);
            $growth[] = sprintf('%s [%s] %s: %d -> %d', $path, $identifier, $message, $allowed, $count);
        }
    }
    printf(
        "Reference: %d entries, %d errors. Current: %d entries, %d errors. Entries removed: %d.\n",
        count($reference),
        array_sum($reference),
        count($current),
        array_sum($current),
        count(array_diff_key($reference, $current))
    );
    if ($growth !== []) {
        throw new RuntimeException("The PHPStan baseline may only shrink. New or increased entries:\n- "
            . implode("\n- ", $growth));
    }
    echo "The PHPStan baseline did not grow.\n";

    return 0;
}

try {
    /** @var list<string> $arguments */
    $arguments = array_slice($_SERVER['argv'] ?? [], 1);
    exit(checkBaseline($arguments));
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
