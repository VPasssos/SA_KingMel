<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

$fornecedor = null;

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    if (!empty($_POST['busca_fornecedor'])) {
        $busca = trim($_POST["busca_fornecedor"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM apiario WHERE id_apiario = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM apiario WHERE Nome_apiario LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$fornecedor){
            echo "<script>alert('Fornecedor não encontrado!');</script>";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"]=="GET") {
    if (!empty($_GET['id'])) {
        $busca = trim($_GET["id"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM apiario WHERE id_apiario = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM apiario WHERE Nome_apiario LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$fornecedor){
            echo "<script>alert('Fornecedor não encontrado!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Fornecedor</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="../JS/mascaras.js"></script>
</head>
<body>
<?php include("../TELAS/MENU.php"); ?>

<h2>Alterar Fornecedor</h2>

<form action="alterar_fornecedor.php" method="POST">
    <label for="busca_fornecedor">Digite o ID ou Nome do Fornecedor:</label>
    <input type="text" id="busca_fornecedor" name="busca_fornecedor" required>
    <button type="submit">Buscar</button>
</form>

<?php if($fornecedor): ?>
    <form action="processa_alteracao_fornecedor.php" method="POST">
        <input type="hidden" name="id_apiario" value="<?=htmlspecialchars($fornecedor['id_apiario'])?>">

        <label>Nome:</label>
        <input type="text" name="nome_apiario" value="<?=htmlspecialchars($fornecedor['Nome_apiario'])?>" required oninput="mascara(this, nomeM)" maxlength="10"><br>

        <label>CNPJ:</label>
        <input type="text" name="cnpj" value="<?=htmlspecialchars($fornecedor['CNPJ'])?>" required  oninput="mascara(this, cnpjM)" maxlength="14"><br>

        <label>Quantidade:</label>
        <input type="number" name="quantidade" value="<?=htmlspecialchars($fornecedor['Quantidade'])?>"><br>

        <label>Data Início:</label>
        <input type="date" name="data_inicio" value="<?=htmlspecialchars($fornecedor['Data_inicio'])?>" oninput="mascara(this, dataM)" maxlength="10"><br>

        <label>Endereço:</label>
        <input type="text" name="endereco" value="<?=htmlspecialchars($fornecedor['Endereco'])?>"><br>

        <button type="submit">Alterar</button>
        <button type="reset">Cancelar</button>
    </form>
<?php endif; ?>
</body>
</html>
