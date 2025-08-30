<?php
session_start();
require_once 'conexao.php';

if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2) {
    echo "<script>alert('Acesso Negado!');window.location.href='MENU.php';</script>";
    exit();
}

// INICIALIZA A VARIÁVEL PARA EVITAR ERROS
$produtos = [];

// SE O FORMULÁRIO FOR ENVIADO, BUSCA O PRODUTO PELO ID OU NOME
if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["busca"])) {
    $busca = trim($_POST["busca"]);

    if(is_numeric($busca)) {
        $sql = "SELECT * FROM produto WHERE id_produto = :busca ORDER BY Tipo_mel ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM produto WHERE Tipo_mel LIKE :busca_nome ORDER BY Tipo_mel ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
    }
} else {
    $sql = "SELECT * FROM produto ORDER BY Tipo_mel ASC";
    $stmt = $pdo->prepare($sql);
}

$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINGMEL</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_PRODUTOS.css">
</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR PRODUTOS</h1>
    </main>

    <div class="tabela_produto">

    <!-- Formulário de busca -->
    <form action="TELA_GERENCIAR_PRODUTOS.php" method="POST">
        <label for="busca">Digite o ID ou Nome do produto:</label>
        <input type="text" id="busca" name="busca">
        <button type="submit">Buscar</button>
    </form>

    <?php if(!empty($produtos)): ?>
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th>ID do produto</th>
                    <th>Tipo do mel</th>
                    <th>Data de embalagem</th>
                    <th>Peso</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['id_produto']) ?></td>
                        <td><?= htmlspecialchars($produto['Tipo_mel']) ?></td>
                        <td><?= htmlspecialchars($produto['Data_embalado']) ?></td>
                        <td><?= htmlspecialchars($produto['Peso']) ?></td>
                        <td><?= htmlspecialchars($produto['Preco']) ?></td>
                        <td><?= htmlspecialchars($produto['Quantidade']) ?></td>
                        <td>
                            <a href="../PRODUTO/alterar_produto.php?id=<?= htmlspecialchars($produto['id_produto']) ?>">Alterar</a>
                            <a href="../PRODUTO/excluir_produto.php?id=<?= htmlspecialchars($produto['id_produto']) ?>" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../PRODUTO/cadastrar_produto.php">Cadastrar Novo Produto</a>
    <?php else: ?>
        <p>Nenhum produto encontrado</p>
    <?php endif; ?>
    </div>
</body>
</html>
