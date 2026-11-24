<?php

putenv('HOME=/home/rapiukka');

exec('git config --global --get credential.helper 2>&1', $output, $status);

echo "<pre>";
echo htmlspecialchars(implode("\n", $output));
echo "</pre>";

?>