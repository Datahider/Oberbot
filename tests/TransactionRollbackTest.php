<?php

declare(strict_types=1);

$broken = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/bot', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $source = (string) file_get_contents($file->getPathname());
    $offset = 0;
    while (($position = strpos($source, 'DB::beginTransaction()', $offset)) !== false) {
        $offset = $position + 1;
        $line = substr_count($source, "\n", 0, $position) + 1;
        $function_end = strpos($source, 'function ', $position);
        $tail = substr($source, $position, $function_end === false ? null : $function_end - $position);
        if (!preg_match('/catch\s*\(([^)$]+)\$\w+\)\s*\{(.*?)\n\s*\}/s', $tail, $match)) {
            $broken[] = sprintf('%s:%d transaction without catch', $file->getPathname(), $line);
            continue;
        }
        if (trim($match[1]) !== '\Throwable') {
            $broken[] = sprintf('%s:%d transaction catch type is %s, expected \Throwable', $file->getPathname(), $line, trim($match[1]));
        }
        if (strpos($match[2], 'DB::rollBack()') === false) {
            $broken[] = sprintf('%s:%d transaction catch without DB::rollBack()', $file->getPathname(), $line);
        }
    }
}

if ($broken !== []) {
    throw new RuntimeException("Transactions without \\Throwable rollback:\n" . implode("\n", $broken));
}

echo "Oberbot transaction rollback tests: OK\n";
