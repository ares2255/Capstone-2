<?php
include "config/db.php";
$test = $conn->query("SHOW COLUMNS FROM settings");
while($col = $test->fetch_assoc()){
    echo $col['Field'] . "<br>";
}
exit();
?>