<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $id = $_POST["id_funcionario"];
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $data_contratacao = $_POST["data_contratacao"];
    $cargo = $_POST["cargo"];
    $salario = $_POST["salario"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];

    $sql = "UPDATE funcionario 
            SET Nome=:nome, CPF=:cpf, Data_contratacao=:data, Cargo=:cargo, 
                Salario=:salario, Telefone=:tel, Email=:email
            WHERE id_funcionario=:id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':cpf',$cpf);
    $stmt->bindParam(':data',$data_contratacao);
    $stmt->bindParam(':cargo',$cargo);
    $stmt->bindParam(':salario',$salario);
    $stmt->bindParam(':tel',$telefone);
    $stmt->bindParam(':email',$email);
    $stmt->bindParam(':id',$id,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Funcionário alterado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_FUNCIONARIOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao alterar o funcionário!');</script>";
    }
}
?>
