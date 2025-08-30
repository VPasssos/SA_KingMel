<?php
session_start();
require_once '../telas/conexao.php';

// Apenas ADM (1) e Secretaria (2) podem cadastrar
if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2){
    echo "<script>alert('Acesso Negado!');window.location.href='../TELAS/MENU.php';</script>";
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $nome = $_POST["nome_apiario"];
    $cnpj = $_POST["cnpj"];
    $quantidade = $_POST["quantidade"];
    $data_inicio = $_POST["data_inicio"];
    $endereco = $_POST["endereco"];

    $sql = "INSERT INTO apiario (Nome_apiario, CNPJ, Quantidade, Data_inicio, Endereco)
            VALUES (:nome, :cnpj, :quantidade, :data_inicio, :endereco)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':cnpj',$cnpj);
    $stmt->bindParam(':quantidade',$quantidade);
    $stmt->bindParam(':data_inicio',$data_inicio);
    $stmt->bindParam(':endereco',$endereco);

    if($stmt->execute()){
        echo "<script>alert('Fornecedor cadastrado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_FORNECEDORES.php';</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar fornecedor!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Fornecedor</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="../JS/mascaras.js"></script>
</head>
<body>
<?php include("../TELAS/MENU.php"); ?>

<h2>Cadastrar Fornecedor</h2>
<form action="cadastrar_fornecedor.php" method="POST">
    <label>Nome:</label>
    <input type="text" name="nome_apiario" required oninput="mascara(this, nomeM)" maxlength="15"><br>

    <label>CNPJ:</label>
    <input type="text" name="cnpj" required oninput="mascara(this, cnpjM)" maxlength="14"><br>

    <label>Quantidade:</label>
    <input type="number" name="quantidade"><br>

    <label>Data de Início:</label>
    <input type="date" name="data_inicio"><br>

    <label>Endereço:</label>
    <input type="text" name="endereco"><br>

    <button type="submit">Salvar</button>
    <button type="reset">Cancelar</button>
</form>

<address>
    Gustavo Wendt / estudante / técnico em sistemas
</address>
</body>
</html>
