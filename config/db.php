<?php
// REMOVE ANY LINE LIKE THIS:
// include "../index.php";  <-- DELETE THIS IF IT EXISTS

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "netcafepos";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>