<?php
$servername = "localhost";
$username = "calcuser";
$password = "calc";

function makeSafe($data) {
  	$data = trim($data);
  	$data = stripslashes($data);
  	$data = htmlspecialchars($data);
  	return $data;
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=calc", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = $_POST['']

    echo "Connected successfully"; 
    $conn->exec('use calc;');
    if ($conn->)
    $query = "
	create table data (
        email varchar(255) not null primary key, 
        cost int not null, 
        initial int not null, 
        annual int not null, 
        months int not null);
    ";
    $conn->exec($query);

    $conn->

}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>