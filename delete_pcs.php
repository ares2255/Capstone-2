<?php
include "config/db.php";
$pdo->query("TRUNCATE TABLE pcs");
header("Location: settings.php?msg=reset_complete");
exit();
?>
