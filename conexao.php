<?php
// Configurações para a conexão com o banco de dados

$host = 'localhost';
$dbname = 'sa_kingmel';
$user = 'root';
$pass = '';

try {
    // Conexão PDO com charset utf8mb4 para evitar problemas de acentuação
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

?>
