<?php

declare(strict_types=1);

function catchTypesInFile(string $path): array
{
    $tokens = token_get_all((string) file_get_contents($path));
    $namespace = '';
    $imports = [];
    $result = [];
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            continue;
        }
        if ($token[0] === T_NAMESPACE) {
            $namespace = '';
            for ($j = $i + 1; $j < $count && $tokens[$j] !== ';' && $tokens[$j] !== '{'; $j++) {
                if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_NAME_QUALIFIED, T_STRING], true)) {
                    $namespace = $tokens[$j][1];
                }
            }
        } elseif ($token[0] === T_USE && $namespace !== '') {
            $name = null;
            $alias = null;
            $is_class_import = true;
            for ($j = $i + 1; $j < $count && $tokens[$j] !== ';'; $j++) {
                if (!is_array($tokens[$j])) {
                    continue;
                }
                if ($tokens[$j][0] === T_FUNCTION || $tokens[$j][0] === T_CONST) {
                    $is_class_import = false;
                } elseif (in_array($tokens[$j][0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_STRING], true)) {
                    if ($name === null) {
                        $name = ltrim($tokens[$j][1], '\\');
                    } else {
                        $alias = $tokens[$j][1];
                    }
                }
            }
            if ($is_class_import && $name !== null) {
                $parts = explode('\\', $name);
                $imports[$alias ?? end($parts)] = $name;
            }
        } elseif ($token[0] === T_CATCH) {
            for ($j = $i + 1; $j < $count && $tokens[$j] !== ')'; $j++) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_VARIABLE) {
                    break;
                }
                if (!is_array($tokens[$j]) || !in_array($tokens[$j][0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    continue;
                }
                $type = $tokens[$j][1];
                if ($tokens[$j][0] === T_NAME_FULLY_QUALIFIED) {
                    $resolved = ltrim($type, '\\');
                } else {
                    $first = explode('\\', $type)[0];
                    if (isset($imports[$first])) {
                        $resolved = $imports[$first] . substr($type, strlen($first));
                    } else {
                        $resolved = ($namespace === '' ? '' : $namespace . '\\') . $type;
                    }
                }
                $result[] = ['type' => $type, 'resolved' => $resolved, 'line' => $tokens[$j][2]];
            }
        }
    }

    return $result;
}

$broken = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/bot', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    foreach (catchTypesInFile($file->getPathname()) as $catch) {
        if (!class_exists($catch['resolved']) && !interface_exists($catch['resolved'])) {
            $broken[] = sprintf('%s:%d catch (%s) resolves to missing %s', $file->getPathname(), $catch['line'], $catch['type'], $catch['resolved']);
        }
    }
}

if ($broken !== []) {
    throw new RuntimeException("Unresolvable catch types:\n" . implode("\n", $broken));
}

echo "Oberbot catch types tests: OK\n";
