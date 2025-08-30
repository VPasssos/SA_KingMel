<?php
session_start();
require_once '../telas/conexao.php';

// VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

// INICIALIZA VARIÁVEL
$produto = null;

// SE O FORMULÁRIO DE BUSCA FOR ENVIADO
if ($_SERVER["REQUEST_METHOD"]=="POST") {
    if (!empty($_POST['busca_produto'])) {
        $busca = trim($_POST["busca_produto"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM produto WHERE id_produto = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM produto WHERE Tipo_mel LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$produto){
            echo "<script>alert('Produto não encontrado!');</script>";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"]=="GET") {
    if (!empty($_GET['id'])) {
        $busca = trim($_GET["id"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM produto WHERE id_produto = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM produto WHERE Tipo_mel LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$produto){
            echo "<script>alert('Produto não encontrado!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Produto</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="mascaras.js"></script>
</head>
<body>
<?php include("../TELAS/MENU.php"); ?>

<h2>Alterar Produto</h2>

<!-- FORMULÁRIO DE BUSCA -->
<form action="alterar_produto.php" method="POST">
    <label for="busca_produto">Digite o ID ou Nome do Produto:</label>
    <input type="text" id="busca_produto" name="busca_produto" required>
    <button type="submit">Buscar</button>
</form>

<?php if($produto): ?>
    <!-- FORMULÁRIO DE ALTERAÇÃO -->
    <form action="processa_alteracao_produto.php" method="POST">
        <input type="hidden" name="id_produto" value="<?=htmlspecialchars($produto['id_produto'])?>">

        <label for="tipo">Tipo de Mel:</label>
        <input type="text" name="tipo" id="tipo" value="<?=htmlspecialchars($produto['Tipo_mel'])?>" required><br>

        <label for="data_embalado">Data de Embalagem:</label>
        <input type="date" name="data_embalado" id="data_embalado" value="<?=htmlspecialchars($produto['Data_embalado'])?>" required><br>

        <label for="peso">Peso (kg):</label>
        <input type="number" step="0.01" name="peso" id="peso" value="<?=htmlspecialchars($produto['Peso'])?>" required><br>

        <label for="preco">Preço (R$):</label>
        <input type="number" step="0.01" name="preco" id="preco" value="<?=htmlspecialchars($produto['Preco'])?>" required><br>

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="quantidade" id="quantidade" value="<?=htmlspecialchars($produto['Quantidade'])?>" required><br>

        <button type="submit">Alterar</button>
        <button type="reset">Cancelar</button>
    </form>
<?php endif; ?>

<address>
    Gustavo Wendt / estudante / técnico em sistemas
</address>
</body>
</html>
