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
    $data_contratacao = $_POST["data_contratacao"];
    $cargo = $_POST["cargo"];
    $salario = $_POST["salario"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];

    $sql = "INSERT INTO funcionario (Nome, CPF, Data_contratacao, Cargo, Salario, Telefone, Email) 
            VALUES (:nome,:cpf,:data,:cargo,:salario,:tel,:email)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':cpf',$cpf);
    $stmt->bindParam(':data',$data_contratacao);
    $stmt->bindParam(':cargo',$cargo);
    $stmt->bindParam(':salario',$salario);
    $stmt->bindParam(':tel',$telefone);
    $stmt->bindParam(':email',$email);

    if($stmt->execute()){
        echo "<script>alert('Funcionário cadastrado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_FUNCIONARIOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar funcionário!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Cadastrar Funcionário</title></head>
<script src="../JS/mascaras.js"></script>
<body>
<h2>Cadastrar Funcionário</h2>
<form action="cadastrar_funcionario.php" method="POST">
    Nome: <input type="text" name="nome" required oninput="mascara(this, nomeM)" maxlength="10"><br>
    CPF: <input type="text" name="cpf" required oninput="mascara(this, cpfM)" maxlength="14"><br>
    Data Contratação: <input type="date" name="data_contratacao" oninput="mascara(this, dataM)" maxlength="10"><br>
    Cargo: <input type="text" name="cargo"><br>
    Salário: <input type="number" step="0.01" name="salario"><br>
    Telefone: <input type="text" name="telefone" oninput="mascara(this, telefoneM)" maxlength="15"><br>
    Email: <input type="email" name="email"><br>
    <button type="submit">Salvar</button>
</form>
</body>
</html>
