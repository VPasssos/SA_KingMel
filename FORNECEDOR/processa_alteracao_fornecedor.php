<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $id = $_POST["id_apiario"];
    $nome = $_POST["nome_apiario"];
    $cnpj = $_POST["cnpj"];
    $quantidade = $_POST["quantidade"];
    $data_inicio = $_POST["data_inicio"];
    $endereco = $_POST["endereco"];

    $sql = "UPDATE apiario 
            SET Nome_apiario=:nome, CNPJ=:cnpj, Quantidade=:qtd, Data_inicio=:data, Endereco=:endereco
            WHERE id_apiario=:id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':cnpj',$cnpj);
    $stmt->bindParam(':qtd',$quantidade);
    $stmt->bindParam(':data',$data_inicio);
    $stmt->bindParam(':endereco',$endereco);
    $stmt->bindParam(':id',$id,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Fornecedor alterado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_FORNECEDORES.php';</script>";
    } else {
        echo "<script>alert('Erro ao alterar o fornecedor!');</script>";
    }
}
?>
