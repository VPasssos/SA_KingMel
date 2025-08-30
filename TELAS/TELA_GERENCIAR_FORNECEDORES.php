<?php
session_start();
require_once 'conexao.php';

if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2) {
    echo "<script>alert('Acesso Negado!');window.location.href='MENU.php';</script>";
    exit();
}

// INICIALIZA A VARIÁVEL PARA EVITAR ERROS
$fornecedores = [];

// SE O FORMULÁRIO FOR ENVIADO, BUSCA O FORNECEDOR PELO ID OU NOME
if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["busca"])) {
    $busca = trim($_POST["busca"]);

    if(is_numeric($busca)) {
        $sql = "SELECT * FROM apiario WHERE id_apiario = :busca ORDER BY Nome_apiario ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM apiario WHERE Nome_apiario LIKE :busca_nome ORDER BY Nome_apiario ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
    }
} else {
    $sql = "SELECT * FROM apiario ORDER BY Nome_apiario ASC";
    $stmt = $pdo->prepare($sql);
}

$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINGMEL - Fornecedores</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_PRODUTOS.css">
</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR FORNECEDORES</h1>
    </main>

    <div class="tabela_fornecedor">

    <!-- Formulário de busca -->
    <form action="TELA_GERENCIAR_FORNECEDORES.php" method="POST">
        <label for="busca">Digite o ID ou Nome do fornecedor:</label>
        <input type="text" id="busca" name="busca">
        <button type="submit">Buscar</button>
    </form>

    <?php if(!empty($fornecedores)): ?>
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Quantidade</th>
                    <th>Data de início</th>
                    <th>Endereço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($fornecedores as $fornecedor): ?>
                    <tr>
                        <td><?= htmlspecialchars($fornecedor['id_apiario']) ?></td>
                        <td><?= htmlspecialchars($fornecedor['Nome_apiario']) ?></td>
                        <td><?= htmlspecialchars($fornecedor['CNPJ']) ?></td>
                        <td><?= htmlspecialchars($fornecedor['Quantidade']) ?></td>
                        <td><?= htmlspecialchars($fornecedor['Data_inicio']) ?></td>
                        <td><?= htmlspecialchars($fornecedor['Endereco']) ?></td>
                        <td>
                            <a href="../FORNECEDOR/alterar_fornecedor.php?id=<?= htmlspecialchars($fornecedor['id_apiario']) ?>">Alterar</a>
                            <a href="../FORNECEDOR/excluir_fornecedor.php?id=<?= htmlspecialchars($fornecedor['id_apiario']) ?>" onclick="return confirm('Tem certeza que deseja excluir este fornecedor?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../FORNECEDOR/cadastrar_fornecedor.php">Cadastrar Novo Fornecedor</a>
    <?php else: ?>
        <p>Nenhum fornecedor encontrado</p>
    <?php endif; ?>
    </div>
</body>
</html>
