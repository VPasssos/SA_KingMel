<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $id = $_POST["id_cliente"];
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];
    $data_nasc = $_POST["data_nascimento"];
    $endereco = $_POST["endereco"];

    $sql = "UPDATE cliente 
            SET Nome=:nome, CPF=:cpf, Telefone=:tel, Email=:email, 
                Data_nascimento=:nasc, Endereco=:endereco
            WHERE id_cliente=:id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':cpf',$cpf);
    $stmt->bindParam(':tel',$telefone);
    $stmt->bindParam(':email',$email);
    $stmt->bindParam(':nasc',$data_nasc);
    $stmt->bindParam(':endereco',$endereco);
    $stmt->bindParam(':id',$id,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Cliente alterado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_CLIENTES.php';</script>";
    } else {
        echo "<script>alert('Erro ao alterar o cliente!');</script>";
    }
}
?>
