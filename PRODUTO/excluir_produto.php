<?php
session_start();
require_once '../telas/conexao.php';

// VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

// INICIALIZA AS VARIÁVEIS
$produtos = null;

// LISTA TODOS OS PRODUTOS
$sql = "SELECT * FROM produto ORDER BY Tipo_mel ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SE O ID FOR PASSADO VIA GET, EXCLUI O PRODUTO
if(isset($_GET["id"]) && is_numeric($_GET["id"])){
    $id_produto = $_GET["id"];

    $sql = "DELETE FROM produto WHERE id_produto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_produto, PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Produto excluído com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_PRODUTOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir produto!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Produtos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="estilo1.css">
</head>
<body>
    <h2>Excluir Produtos</h2>

    <?php if (!empty($produtos)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Tipo do mel</th>
                <th>Data de embalagem</th>
                <th>Peso</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Ações</th>
            </tr>

            <?php foreach($produtos as $produto): ?>
                <tr>
                    <td><?= htmlspecialchars($produto['id_produto']) ?></td>
                    <td><?= htmlspecialchars($produto['Tipo_mel']) ?></td>
                    <td><?= htmlspecialchars($produto['Data_embalado']) ?></td>
                    <td><?= htmlspecialchars($produto['Peso']) ?></td>
                    <td><?= htmlspecialchars($produto['Preco']) ?></td>
                    <td><?= htmlspecialchars($produto['Quantidade']) ?></td>
                    <td>
                        <a href="excluir_produto.php?id=<?= htmlspecialchars($produto['id_produto']) ?>" 
                           onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                           <button class="excluir">Excluir</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum produto encontrado</p>
    <?php endif; ?>

    <address>
        Gustavo Wendt / estudante / técnico em sistemas 
    </address>
</body>
</html>
