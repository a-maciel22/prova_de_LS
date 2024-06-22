<?php
session_start(); // Inicia a sessão

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "prova_maciel";

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checagem da conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
