<?php
// Test suite: php tests/run.php from a git checkout. Needs PHP >= 8.2 with curl; no network.
// Each check prints "ok" or "FAIL"; the exit code is the number of failures (0 = all passed).

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = 0;

function check(bool $passed, string $what): void
{
    global $failures;
    echo ($passed ? 'ok   ' : 'FAIL ') . $what . "\n";
    if (!$passed) $failures++;
}

/** Split a SKILL.md into its YAML-ish frontmatter keys and body; only the flat keys this repo uses. */
function skillParts(string $text): array
{
    if (preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', $text, $m) !== 1) return array(array(), '');
    $keys = array();
    foreach (explode("\n", $m[1]) as $line) {
        if (preg_match('/^([a-z-]+):\s*(.*)$/', $line, $kv) === 1) $keys[$kv[1]] = $kv[2];
    }
    return array($keys, $m[2]);
}

/** Every https URL host+path prefix named in a text, without query strings or trailing punctuation. */
function urlsIn(string $text): array
{
    preg_match_all('#https://[A-Za-z0-9.-]+(?:/[A-Za-z0-9._/~-]*)?#', $text, $m);
    return array_values(array_unique(array_map(fn ($u) => rtrim($u, '/.'), $m[0])));
}

// --- Repository layout and the Agent Plugins manifest -------------------------------------------
foreach (array('LICENSE', 'NOTICE', 'README.md', 'AGENTS.md', 'SECURITY.md', 'CHANGELOG.md', 'plugin.json', 'bin/pm-check', 'docs/adr/0000-template.md', 'docs/adr/0001-layout-languages-and-install.md') as $file) {
    check(is_file("$root/$file"), "file exists: $file");
}
$manifest = json_decode((string) @file_get_contents("$root/plugin.json"), true);
check(is_array($manifest), 'plugin.json is JSON');
check(($manifest['$schema'] ?? '') === 'https://agent-plugins.org/schemas/1.0.0/plugin.schema.json', 'plugin.json carries the exact Agent Plugins schema URL (OpenClaw treats the file as a bundle only then)');
check(($manifest['name'] ?? '') !== '', 'plugin.json has a name');
foreach (array('openclaw.plugin.json', 'package.json', 'index.js', 'index.ts', 'index.mjs') as $trap) {
    check(!file_exists("$root/$trap"), "no $trap at the root (it would stop OpenClaw reading the repo as a bundle)");
}

// --- Skills -------------------------------------------------------------------------------------
$skillDirs = glob("$root/skills/*", GLOB_ONLYDIR) ?: array();
check(count($skillDirs) === 4, 'four skills');
$skillUrls = array();
$readme = (string) @file_get_contents("$root/README.md");
foreach ($skillDirs as $dir) {
    $name = basename($dir);
    $text = (string) @file_get_contents("$dir/SKILL.md");
    list($keys, $body) = skillParts($text);
    check(($keys['name'] ?? '') === $name, "$name: frontmatter name matches its folder");
    check(strlen($keys['description'] ?? '') >= 40, "$name: has a description");
    $meta = json_decode($keys['metadata'] ?? '', true);
    check(is_array($meta['openclaw']['requires']['bins'] ?? null), "$name: metadata.openclaw.requires.bins is set");
    check(strpos($name, 'pm-') === 0, "$name: prefixed name (never shadows a user's or Pikkumies' own skill)");
    check(strpos($readme, "`$name`") !== false, "$name: listed in README");
    // A key never reaches a command line: no header built from a shell variable or a pasted key.
    check(preg_match('/-H\s+["\']?(Authorization|X-API-Key):/i', $body) !== 1, "$name: no key header written into a command line");
    check(preg_match('/(vsk|pm)_?-?[A-Za-z0-9]{20,}/', $body) !== 1, "$name: no key-shaped literal");
    check(strpos($body, '/tmp/') === false, "$name: no /tmp paths (shared hosts)");
    foreach (urlsIn($body) as $url) $skillUrls[$url] = true;
}

// --- Endpoints: every one a skill or the CLI uses is declared in SECURITY.md ---------------------
$security = (string) @file_get_contents("$root/SECURITY.md");
$cliText = (string) @file_get_contents("$root/bin/pm-check");
foreach (urlsIn($cliText) as $url) $skillUrls[$url] = true;
$declared = urlsIn($security);
foreach (array_keys($skillUrls) as $url) {
    if (strpos($url, 'github.com/MagnaCapax/') !== false || $url === 'https://wiki.pulsedmedia.com/wiki') continue;
    $covered = false;
    foreach ($declared as $prefix) $covered = $covered || strpos($url, $prefix) === 0;
    check($covered, "endpoint declared in SECURITY.md: $url");
}

// --- Public-repository hygiene: deployment words kept out of the tree ---------------------------
$words = array_filter(array_map('trim', explode("\n", (string) (getenv('KOKKO_FORBIDDEN_WORDS') ?: @file_get_contents("$root/.forbidden-words")))));
if ($words === array()) {
    echo "skip forbidden-words check (no KOKKO_FORBIDDEN_WORDS and no .forbidden-words)\n";
    if (getenv('KOKKO_REQUIRE_FORBIDDEN_WORDS') === '1') check(false, 'forbidden-word list required but missing');
} else {
    exec('git -C ' . escapeshellarg($root) . ' ls-files', $tracked);
    foreach ($tracked as $file) {
        $content = strtolower((string) @file_get_contents("$root/$file"));
        foreach ($words as $word) check(strpos($content, strtolower($word)) === false, "no forbidden word in $file");
    }
}

// --- bin/pm-check against a stand-in server ------------------------------------------------------
$port = 18000 + random_int(0, 2000);
$server = proc_open(array(PHP_BINARY, '-S', "127.0.0.1:$port", "$root/tests/stand-in-server.php"), array(1 => array('file', '/dev/null', 'w'), 2 => array('file', '/dev/null', 'w')), $pipes);
for ($i = 0; $i < 50 && @fsockopen('127.0.0.1', $port) === false; $i++) usleep(100000);
$work = sys_get_temp_dir() . '/kokko-test-' . bin2hex(random_bytes(6));
mkdir($work, 0700);

/** Run pm-check with a key file for one scenario; return [exit code, stdout, stderr]. */
function runCheck(string $root, string $work, int $port, string $scenario, array $args, int $mode = 0600, ?string $endpoint = null): array
{
    $file = "$work/$scenario.key";
    file_put_contents($file, 'endpoint=' . ($endpoint ?? "http://127.0.0.1:$port/v1") . "\nkey=vsk-test-$scenario-0123456789abcdef\n");
    chmod($file, $mode);
    $cmd = array_merge(array(PHP_BINARY, "$root/bin/pm-check"), $args, array('--key-file', $file));
    $proc = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $p);
    $out = stream_get_contents($p[1]);
    $err = stream_get_contents($p[2]);
    return array(proc_close($proc), trim($out), trim($err));
}

$below = array('credits', '--below', '200000');
list($rc, $out) = runCheck($root, $work, $port, 'healthy', $below);
check($rc === 0 && $out === 'NO_REPLY', 'healthy balance: NO_REPLY, exit 0');
list($rc, $out) = runCheck($root, $work, $port, 'low', $below);
check($rc === 0 && strpos($out, '90000 left') !== false && strpos($out, 'days at the last 7 days') !== false, 'low balance: warning with runway, exit 0');
list($rc, $out) = runCheck($root, $work, $port, 'idle', $below);
check($rc === 0 && strpos($out, 'no spending in the last 7 days') !== false, 'low balance, no spending: says so instead of dividing by zero');
list($rc, $out, $err) = runCheck($root, $work, $port, 'unauthorized', $below);
check($rc === 1 && $out === '' && strpos($err, 'HTTP 401') !== false, 'refused key: exit 1, nothing on stdout');
list($rc, $out) = runCheck($root, $work, $port, 'broken', $below);
check($rc === 1 && $out === '', 'server error: exit 1');
list($rc, $out) = runCheck($root, $work, $port, 'garbled', $below);
check($rc === 1 && $out === '', 'non-JSON answer: exit 1');
list($rc) = runCheck($root, $work, $port, 'healthy', $below, 0644);
check($rc === 2, 'key file readable by others: refused, exit 2');
list($rc) = runCheck($root, $work, $port, 'healthy', $below, 0600, 'http://example.invalid/v1');
check($rc === 2, 'plain-http endpoint off this machine: refused, exit 2');
list($rc) = runCheck($root, $work, $port, 'healthy', array('credits'));
check($rc === 2, 'missing --below: exit 2');
list($rc) = runCheck($root, $work, $port, 'healthy', array('balance'));
check($rc === 2, 'unknown command: exit 2');
$proc = proc_open(array(PHP_BINARY, "$root/bin/pm-check", 'credits', '--below', '1', '--key-file', "$work/absent.key"), array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $p);
check(proc_close($proc) === 2, 'missing key file: exit 2');

proc_terminate($server);
proc_close($server);
array_map('unlink', glob("$work/*") ?: array());
rmdir($work);

echo $failures === 0 ? "all checks passed\n" : "$failures check(s) failed\n";
exit(min($failures, 255));
