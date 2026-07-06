<?php
/**
 * Build AMD modules from local/sc_learningplans/amd/src/.
 *
 * Uses terser to minify, then wraps the output in the AMD `define()`
 * envelope so Moodle's requirejs loader can pick it up. Also injects the
 * module name (filename without .js) when it's missing.
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

    // 1. Detect AMD `define([deps], factory)` (multi-line or single-line).
    $source = file_get_contents($src);
    $amddeps = null;
    $amdfactory = null;

    // Multi-line pattern: define(['...', ...], function(...){...});
    if (preg_match('/^define\(\s*(\[[^\]]*\])\s*,\s*function\s*\(([^)]*)\)\s*\{(.*)\}\s*\)\s*;?\s*$/sm', $source, $m)) {
        $amddeps = trim($m[1]);
        $amdfactoryargs = trim($m[2]);
        $amdfactorybody = $m[3];
    } elseif (preg_match('/^define\s*\(\s*([\'"][^\'"]+[\'"])\s*,\s*(\[[^\]]*\])\s*,\s*function\s*\(([^)]*)\)\s*\{(.*)\}\s*\)\s*;?\s*$/sm', $source, $m)) {
        // define('name', [...], function(){...});
        $amdnamed = trim($m[1]);
        $amddeps = trim($m[2]);
        $amdfactoryargs = trim($m[3]);
        $amdfactorybody = $m[4];
    } elseif (preg_match('/^define\s*\(\s*function\s*\(([^)]*)\)\s*\{(.*)\}\s*\)\s*;?\s*$/sm', $source, $m)) {
        // define(function(...){...});  - no deps
        $amddeps = '[]';
        $amdfactoryargs = trim($m[1]);
        $amdfactorybody = $m[2];
    }

    if ($amddeps === null) {
        // Source is not in classic AMD define() shape. Two possibilities:
        //   a) It uses ES6 import statements + top-level code (what we have here).
        //   b) It uses define() but our regex missed it.
        // Try to detect ES6 imports and gather them as deps heuristically.
        $imports = [];
        if (preg_match_all('/^\s*import\s+(?:\{[^}]*\}|\*\s+as\s+\w+|\w+)\s+from\s+[\'"]([^\'"]+)[\'"]\s*;?/m', $source, $im)) {
            foreach ($im[1] as $dep) {
                $imports[] = "'" . $dep . "'";
            }
        }
        // If there were any imports, we need to wrap in define().
        if (!empty($imports)) {
            $deps = '[' . implode(',', array_unique($imports)) . ']';
            // Extract the body (everything after the last import).
            $lastImportEnd = 0;
            foreach ($im[0] as $imp) {
                $pos = strrpos($source, $imp);
                if ($pos !== false) {
                    $lastImportEnd = max($lastImportEnd, $pos + strlen($imp));
                }
            }
            $body = trim(substr($source, $lastImportEnd));
            // The body is the factory body. The factory args will be the
            // imported names that need to be available inside.
            // We figure out the args from the `import * as X from ...` form.
            $args = [];
            if (preg_match_all('/import\s+\*\s+as\s+(\w+)\s+from/', $source, $an)) {
                foreach ($an[1] as $a) {
                    $args[] = $a;
                }
            } elseif (preg_match_all('/import\s*\{([^}]+)\}\s*from/', $source, $an2)) {
                foreach ($an2[1] as $raw) {
                    foreach (explode(',', $raw) as $piece) {
                        $piece = trim($piece);
                        if ($piece === '') continue;
                        // Pull out the local name after "as" or the last identifier.
                        if (preg_match('/\bas\s+(\w+)/', $piece, $m2)) {
                            $args[] = $m2[1];
                        } else {
                            $args[] = trim(preg_split('/\s+/', $piece)[0]);
                        }
                    }
                }
            } elseif (preg_match_all('/^import\s+(\w+)\s+from/', $source, $an3)) {
                foreach ($an3[1] as $a) {
                    $args[] = $a;
                }
            }
            $amdfactoryargs = implode(',', $args);
            $amdfactorybody = $body;
            $amddeps = $deps;
        } else {
            // Could not understand the source. Skip it.
            fwrite(STDERR, "SKIP {$name}: cannot parse as AMD define() or detect ES6 imports.\n");
            continue;
        }
    }

    // Minify the factory body alone, then re-wrap.
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

    // Assemble AMD wrapper. Always emit the 2-arg form so requirejs
    // attaches the module name properly.
    $wrapped = "define('local_sc_learningplans/{$modulename}',{$amddeps},function({$amdfactoryargs}){{$minifiedBody}});\n";

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