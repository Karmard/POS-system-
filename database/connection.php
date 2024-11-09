<?php
$servername = "localhost";
$username = "Any";    
$password = "";           
$database = "pos";  

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) 
{
    die("Connection failed: " . $conn->connect_error);
}

