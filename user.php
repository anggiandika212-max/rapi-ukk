<?php

$username = 'USERNAME_GITHUB';
$token = 'ISI_PERSONAL_ACCESS_TOKEN';

$credentials = "https://{$username}:{$token}@github.com\n";

$file = '/home/rapiukka/.git-credentials';

if (file_put_contents($file, $credentials, LOCK_EX) !== false) {
    chmod($file, 0600);
    echo "Git credentials berhasil disimpan.";
} else {
    echo "Gagal menyimpan credentials.";
}
?>