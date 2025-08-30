<?php
session_start();
require_once '../telas/conexao.php';

// Apenas ADM (1) pode excluir
if($_SESSION['perfil']!=1){
    echo "<script>alert('Acesso Negado!');window.location.href='../TELAS/MENU.php';</script>";
    exit();
}

if(isset($_GET["id"]) && is_numeric($_GET["id"])){
    $id = $_GET["id"];

    $sql = "DELETE FROM apiario WHERE id_apiario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id',$id,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Fornecedor excluído com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_FORNECEDORES.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir fornecedor!');</script>";
    }
}
?>
