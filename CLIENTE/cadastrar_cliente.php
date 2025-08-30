<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2){
    echo "<script>alert('Acesso Negado!');window.location.href='../TELAS/MENU.php';</script>";
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];
    $data_nasc = $_POST["data_nascimento"];
    $endereco = $_POST["endereco"];

    $sql = "INSERT INTO cliente (Nome, CPF, Telefone, Email, Data_nascimento, Endereco) 
            VALUES (:nome,:cpf,:telefone,:email,:data,:endereco)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':cpf',$cpf);
    $stmt->bindParam(':telefone',$telefone);
    $stmt->bindParam(':email',$email);
    $stmt->bindParam(':data',$data_nasc);
    $stmt->bindParam(':endereco',$endereco);

    if($stmt->execute()){
        echo "<script>alert('Cliente cadastrado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_CLIENTES.php';</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar cliente!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Cadastrar Cliente</title></head>
<script src="../JS/mascaras.js"></script>
<body>
<h2>Cadastrar Cliente</h2>
<form action="cadastrar_cliente.php" method="POST">
    Nome: <input type="text" name="nome" required oninput="mascara(this, nomeM)" maxlength="10"><br>
    CPF: <input type="text" name="cpf" required oninput="mascara(this, cpfM)" maxlength="14"><br>
    Telefone: <input type="text" name="telefone" oninput="mascara(this, telefoneM)" maxlength="15"><br>
    Email: <input type="email" name="email"><br>
    Data de Nascimento: <input type="date" name="data_nascimento" oninput="mascara(this, dataM)" maxlength="10"><br>
    Endereço: <input type="text" name="endereco"><br>
    <button type="submit">Salvar</button>
</form>
</body>
</html>
