<?php
$servername = "sql313.infinityfree.com";
$username = "if0_39383632";
$password = "isabelleevens20";
$dbname = "if0_39383632_isabelle_evens";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
}
?>