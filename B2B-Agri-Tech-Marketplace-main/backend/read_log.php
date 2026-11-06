<?php
$log = file_get_contents(__DIR__ . '/storage/logs/laravel.log');
$lines = explode("\n", $log);
$last10 = array_slice($lines, -15);
foreach ($last10 as $l) {
    echo wordwrap($l, 100, "\n  ", true) . "\n---\n";
}
