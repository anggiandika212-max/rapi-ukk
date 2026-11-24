<?php

putenv('HOME=/home/rapiukka');

$commands = [
    'git config --global --get credential.helper',
    'git config --global --get credential.useHttpPath',
    'git remote get-url origin'
];

foreach ($commands as $command) {
    $output = [];
    exec($command . ' 2>&1', $output, $status);

    echo "<pre>";
    echo htmlspecialchars(implode("\n", $output));
    echo "</pre>";
}
?>