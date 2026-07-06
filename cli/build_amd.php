<?php
/**
 * Minify every AMD module under local/sc_learningplans/amd/src/.
 *
 * Run after every pull on the server so that the cachejs=1 production
 * setting can keep serving the optimised .min.js files.
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

$terser = trim(shell_exec('command -v terser 2>/dev/null'));
if ($terser === '') {
    // Fallback to local node_modules install.
    $localbin = $CFG->dirroot . '/node_modules/.bin/terser';
    if (is_executable($localbin)) {
        $terser = $localbin;
    } else {
        // Last resort: install terser into a temp dir.
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
    $dst = $buildroot . '/' . preg_replace('/\.js$/', '.min.js', $name);
    $cmd = escapeshellcmd($terser)
        . ' ' . escapeshellarg($src)
        . ' --compress --mangle --comments=/^!/'
        . ' -o ' . escapeshellarg($dst);
    exec($cmd, $out, $rc);
    if ($rc !== 0) {
        fwrite(STDERR, "Failed to minify {$name} (exit={$rc})\n");
        continue;
    }
    echo "OK  {$name} -> " . basename($dst) . " (" . filesize($dst) . " bytes)\n";
    $ok++;
}

// Bump jsrev so the AMD loader invalidates the previous URLs.
$newrev = time();
set_config('jsrev', $newrev, 'core');
purge_all_caches();

echo "\n{$ok}/" . count($files) . " minified. jsrev bumped to {$newrev}.\n";
exit(0);