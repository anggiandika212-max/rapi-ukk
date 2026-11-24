<?php
putenv('HOME=/home/rapiukka');
putenv('GIT_TERMINAL_PROMPT=0');

chdir('/home/rapiukka/public_html');

function runGit($command) {
    passthru($command . ' 2>&1', $status);

    if ($status !== 0) {
        die("<br>Proses gagal: " . htmlspecialchars($command));
    }
}

runGit('git add coba.php');
runGit('git commit -m "Update coba.php"');
runGit('git push origin main');

echo "<h3>Berhasil memperbarui GitHub!</h3>";
?>