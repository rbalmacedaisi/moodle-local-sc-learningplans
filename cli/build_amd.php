<?php
/**
 * Build AMD modules from local/sc_learningplans/amd/src/.
 *
 * The source uses ES6 (import / export) so the body must be rewritten
 * to AMD-style `_exports.X = ...` before minifying. This is the
 * transformation grunt-contrib-requirejs does for Moodle core, but
 * we do it manually here because we do not run the full grunt build.
 *
 * Usage: php local/sc_learningplans/cli/build_amd.php
 *
 * @package    local_sc_learningplans
 * @copyright  2026 Grupo Makro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', 1);
define('NO_DEBUG_DISPLAY', true);

require(__DIR__ . '/../../../config.php');

$srcroot = $CFG->dirroot . '/local/sc_learningplans/amd/src';
$buildroot = $CFG->dirroot . '/local/sc_learningplans/amd/build';

if (!is_dir($srcroot)) {
    fwrite(STDERR, "Source dir not found: {$srcroot}\n");
    exit(1);
}
if (!is_dir($buildroot) && !mkdir($buildroot, 0755, true)) {
    fwrite(STDERR, "Cannot create build dir: {$buildroot}\n");
    exit(1);
}

// Locate terser.
$terser = trim(shell_exec('command -v terser 2>/dev/null'));
if ($terser === '') {
    $localbin = $CFG->dirroot . '/node_modules/.bin/terser';
    if (is_executable($localbin)) {
        $terser = $localbin;
    } else {
        $tmp = sys_get_temp_dir() . '/terser-build-' . uniqid();
        mkdir($tmp, 0755, true);
        chdir($tmp);
        exec('npm init -y > /dev/null 2>&1 && npm install terser 2>&1', $out, $rc);
        if ($rc !== 0) {
            fwrite(STDERR, "Failed to install terser in {$tmp}\n");
            exit(2);
        }
        $terser = $tmp . '/node_modules/.bin/terser';
    }
}

/**
 * Rewrite ES6 export statements into AMD `_exports.X = ...` assignments
 * that terser will safely preserve.
 *
 * Handles:
 *   - export const X = ...                  -> _exports.X = ...
 *   - export let X = ...                    -> _exports.X = ...
 *   - export var X = ...                    -> _exports.X = ...
 *   - export function f(...) {...}          -> _exports.f = function(...)
 *   - export class C {...}                  -> _exports.C = class C {...}
 *   - export default EXPR;                   -> _exports.default = EXPR;
 *   - export { a, b as c };                 -> _exports.a = a; _exports.c = b;
 *   - export * from "dep";                   -> (delegated to _deps)
 */
function rewrite_es6_to_amd(string $source): array {
    // Strip top-level ES6 imports.
    $imports = [];
    $deps = [];
    if (preg_match_all('/^\s*import\s+(?:\{[^}]*\}|\*\s+as\s+\w+|\w+)\s+from\s+[\'"]([^\'"]+)[\'"]\s*;?/m', $source, $imatches, PREG_SET_ORDER)) {
        foreach ($imatches as $m) {
            $deps[] = $m[1];
            // Record the local alias for the import.
            if (preg_match('/^\s*import\s+\*\s+as\s+(\w+)\s+from/', $m[0], $am)) {
                $imports[] = ['name' => $am[1], 'kind' => 'star', 'dep' => $m[1]];
            } elseif (preg_match('/^\s*import\s*\{([^}]+)\}\s*from/', $m[0], $am)) {
                foreach (explode(',', $am[1]) as $piece) {
                    $piece = trim($piece);
                    if ($piece === '') continue;
                    if (preg_match('/^(\w+)\s+as\s+(\w+)$/', $piece, $mm)) {
                        $imports[] = ['name' => $mm[2], 'orig' => $mm[1], 'kind' => 'named', 'dep' => $m[1]];
                    } else {
                        $imports[] = ['name' => trim($piece), 'kind' => 'named', 'dep' => $m[1]];
                    }
                }
            } elseif (preg_match('/^\s*import\s+(\w+)\s+from/', $m[0], $am)) {
                $imports[] = ['name' => $am[1], 'kind' => 'default', 'dep' => $m[1]];
            }
        }
    }

    // Build an arglist for the factory function.
    $args = [];
    $seen = [];
    foreach ($imports as $imp) {
        if (!in_array($imp['name'], $seen, true)) {
            $args[] = $imp['name'];
            $seen[] = $imp['name'];
        }
    }

    // Strip the import statements from the source so they don't break parsing.
    $body = preg_replace('/^\s*import\s+(?:\{[^}]*\}|\*\s+as\s+\w+|\w+)\s+from\s+[\'"]([^\'"]+)[\'"]\s*;?/m', '', $source);

    // Rewrite top-level `export const/let/var X = ...`  ->  `_exports.X = ...;`
    $body = preg_replace_callback(
        '/^(?<![\w$])(export\s+(?:const|let|var)\s+([A-Za-z_$][\w$]*)\b)/m',
        function ($m) { return '_exports.' . $m[2] . ' ='; },
        $body
    );

    // Rewrite top-level `export function NAME(...) {...}` ->
    //   `_exports.NAME = function(...) {...}`
    $body = preg_replace_callback(
        '/^(?<![\w$])export\s+function\s+([A-Za-z_$][\w$]*)/m',
        function ($m) { return '_exports.' . $m[1] . ' = function'; },
        $body
    );

    // Rewrite top-level `export class NAME {...}` -> `_exports.NAME = class ...`
    $body = preg_replace_callback(
        '/^(?<![\w$])export\s+class\s+([A-Za-z_$][\w$]*)/m',
        function ($m) { return '_exports.' . $m[1] . ' = class'; },
        $body
    );

    // Rewrite `export default EXPR;` (single-line form) -> `_exports.default = EXPR;`
    $body = preg_replace_callback(
        '/^(?<![\w$])export\s+default\s+([^;]+);/m',
        function ($m) { return '_exports.default = ' . $m[1] . ';'; },
        $body
    );

    // Rewrite `export { a as b, c };` -> assign each.
    $body = preg_replace_callback(
        '/^(?<![\w$])export\s*\{([^}]+)\}\s*;?$/m',
        function ($m) {
            $out = [];
            foreach (explode(',', $m[1]) as $piece) {
                $piece = trim($piece);
                if ($piece === '') continue;
                if (preg_match('/^(\w+)\s+as\s+(\w+)$/', $piece, $mm)) {
                    $out[] = '_exports.' . $mm[2] . ' = ' . $mm[1] . ';';
                } else {
                    $out[] = '_exports.' . $piece . ' = ' . $piece . ';';
                }
            }
            return implode("\n", $out);
        },
        $body
    );

    // Strip leading whitespace on every line (terser handles its own formatting).
    $body = preg_replace('/^[ \t]+/m', '', $body);

    return [$body, $args, $deps];
}

$files = glob($srcroot . '/*.js');
if (!$files) {
    echo "No AMD modules found in {$srcroot}\n";
    exit(0);
}

$ok = 0;
foreach ($files as $src) {
    $name = basename($src);
    $modulename = preg_replace('/\.js$/', '', $name);
    $dst = $buildroot . '/' . preg_replace('/\.js$/', '.min.js', $name);

    $source = file_get_contents($src);

    // Detect AMD define() wrapper and unwrap.
    if (preg_match('/^define\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\[[^\]]*\])\s*,\s*function\s*\(([^)]*)\)\s*\{(.*)\}\s*\)\s*;?\s*$/sm', $source, $m)) {
        // define('name', [deps], function(args) { body });
        $amdnamed = "'" . $m[1] . "'";
        $amddeps = trim($m[2]);
        $amdfactoryargs = trim($m[3]);
        $amdfactorybody = $m[4];
    } elseif (preg_match('/^define\(\s*(\[[^\]]*\])\s*,\s*function\s*\(([^)]*)\)\s*\{(.*)\}\s*\)\s*;?\s*$/sm', $source, $m)) {
        $amdnamed = null;
        $amddeps = trim($m[1]);
        $amdfactoryargs = trim($m[2]);
        $amdfactorybody = $m[3];
    } else {
        // ES6 source. Rewrite imports/exports into AMD shape.
        [$amdfactorybody, $parsedargs, $parseddeps] = rewrite_es6_to_amd($source);
        $amdnamed = "'local_sc_learningplans/{$modulename}'";
        $amddeps = '[' . implode(',', array_map(function ($d) { return "'{$d}'"; }, array_unique($parseddeps))) . ']';
        $amdfactoryargs = implode(',', $parsedargs);
    }

    // Write the (possibly unwrapped) body to a temp file, then minify it.
    $tmp = tempnam(sys_get_temp_dir(), 'gmkamd');
    $tmpSrc = $tmp . '.js';
    $tmpDst = $tmp . '.min.js';
    file_put_contents($tmpSrc, $amdfactorybody);
    $cmd = escapeshellcmd($terser)
        . ' ' . escapeshellarg($tmpSrc)
        . ' --compress --mangle --comments=/^!/'
        . ' -o ' . escapeshellarg($tmpDst);
    exec($cmd, $out, $rc);
    if ($rc !== 0) {
        @unlink($tmpSrc);
        @unlink($tmpDst);
        @unlink($tmp);
        fwrite(STDERR, "Failed to minify {$name} (exit={$rc})\n");
        continue;
    }
    $minifiedBody = file_get_contents($tmpDst);
    @unlink($tmpSrc);
    @unlink($tmpDst);
    @unlink($tmp);

    // Build the AMD envelope.
    $defineHeader = $amdnamed !== null
        ? "define({$amdnamed},{$amddeps},function({$amdfactoryargs}){"
        : "define({$amddeps},function({$amdfactoryargs}){";
    $wrapped = $defineHeader . $minifiedBody . "});\n";

    file_put_contents($dst, $wrapped);
    echo "OK  {$name} -> " . basename($dst) . " (" . filesize($dst) . " bytes)\n";
    $ok++;
}

// Bump jsrev so the AMD loader invalidates the previous URLs.
$newrev = time();
set_config('jsrev', $newrev, 'core');
purge_all_caches();

echo "\n{$ok}/" . count($files) . " minified. jsrev bumped to {$newrev}.\n";
exit(0);