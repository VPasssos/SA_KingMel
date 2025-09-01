<?php
session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// VERIFICA SE O USUARIO TEM PERMISSÃO
if($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3){
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";        
    exit();
}

// Função para buscar produtos com informações do apiário
function buscarProdutos($pdo, $termo = null) {
    $sql = "SELECT p.*, a.Nome_apiario, a.id_apiario 
            FROM produto p 
            LEFT JOIN apiario_produto ap ON p.id_produto = ap.id_produto 
            LEFT JOIN apiario a ON ap.id_apiario = a.id_apiario 
            WHERE 1=1";
    
    if ($termo) {
        $sql .= " AND (p.Tipo_mel LIKE :termo OR p.Data_embalado LIKE :termo OR a.Nome_apiario LIKE :termo)";
    }

    $sql .= " GROUP BY p.id_produto ORDER BY p.Tipo_mel ASC";

    $stmt = $pdo->prepare($sql);

    if ($termo) {
        $stmt->bindValue(':termo', '%' . $termo . '%');
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar apiários
function buscarApiarios($pdo) {
    $sql = "SELECT * FROM apiario ORDER BY Nome_apiario ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para excluir um produto
if (isset($_GET['excluir'])) {
    $id_produto = $_GET['excluir'];

    try {
        $pdo->beginTransaction();
        
        // Primeiro excluir relações com apiários
        $sql_relacao = "DELETE FROM apiario_produto WHERE id_produto = :id_produto";
        $stmt_relacao = $pdo->prepare($sql_relacao);
        $stmt_relacao->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt_relacao->execute();
        
        // Depois excluir o produto
        $sql = "DELETE FROM produto WHERE id_produto = :id_produto";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $pdo->commit();
            echo "<script>alert('Produto excluído com sucesso!');</script>";
        } else {
            throw new Exception("Erro ao excluir produto");
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao excluir produto!');</script>";
    }
    
    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// Função para adicionar um novo produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_produto'])) {
    $Tipo_mel = $_POST['Tipo_mel'];
    $Data_embalado = $_POST['Data_embalado'];
    $Peso = $_POST['Peso'];
    $Preco = $_POST['Preco'];
    $Quantidade = $_POST['Quantidade'];
    $id_apiario = $_POST['id_apiario'];

    try {
        $pdo->beginTransaction();
        
        // Inserir o produto
        $sql = "INSERT INTO produto (Tipo_mel, Data_embalado, Peso, Preco, Quantidade) 
                VALUES (:Tipo_mel, :Data_embalado, :Peso, :Preco, :Quantidade)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':Tipo_mel', $Tipo_mel);
        $stmt->bindParam(':Data_embalado', $Data_embalado);
        $stmt->bindParam(':Peso', $Peso);
        $stmt->bindParam(':Preco', $Preco);
        $stmt->bindParam(':Quantidade', $Quantidade);
        $stmt->execute();
        
        $id_produto = $pdo->lastInsertId();
        
        // Relacionar com o apiário, se selecionado
        if (!empty($id_apiario)) {
            $sql_relacao = "INSERT INTO apiario_produto (id_apiario, id_produto) 
                           VALUES (:id_apiario, :id_produto)";
            $stmt_relacao = $pdo->prepare($sql_relacao);
            $stmt_relacao->bindParam(':id_apiario', $id_apiario);
            $stmt_relacao->bindParam(':id_produto', $id_produto);
            $stmt_relacao->execute();
        }
        
        $pdo->commit();
        echo "<script>alert('Produto adicionado com sucesso!');</script>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao adicionar produto!');</script>";
    }
    
    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// Função para alterar um produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_produto'])) {
    $id_produto = $_POST['id_produto'];
    $Tipo_mel = $_POST['Tipo_mel'];
    $Data_embalado = $_POST['Data_embalado'];
    $Peso = $_POST['Peso'];
    $Preco = $_POST['Preco'];
    $Quantidade = $_POST['Quantidade'];
    $id_apiario = $_POST['id_apiario'];

    try {
        $pdo->beginTransaction();
        
        // Atualizar o produto
        $sql = "UPDATE produto SET Tipo_mel = :Tipo_mel, Data_embalado = :Data_embalado, 
                Peso = :Peso, Preco = :Preco, Quantidade = :Quantidade 
                WHERE id_produto = :id_produto";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto);
        $stmt->bindParam(':Tipo_mel', $Tipo_mel);
        $stmt->bindParam(':Data_embalado', $Data_embalado);
        $stmt->bindParam(':Peso', $Peso);
        $stmt->bindParam(':Preco', $Preco);
        $stmt->bindParam(':Quantidade', $Quantidade);
        $stmt->execute();
        
        // Atualizar relação com apiário
        // Primeiro remover relações existentes
        $sql_delete_relacao = "DELETE FROM apiario_produto WHERE id_produto = :id_produto";
        $stmt_delete = $pdo->prepare($sql_delete_relacao);
        $stmt_delete->bindParam(':id_produto', $id_produto);
        $stmt_delete->execute();
        
        // Adicionar nova relação, se apiário foi selecionado
        if (!empty($id_apiario)) {
            $sql_relacao = "INSERT INTO apiario_produto (id_apiario, id_produto) 
                           VALUES (:id_apiario, :id_produto)";
            $stmt_relacao = $pdo->prepare($sql_relacao);
            $stmt_relacao->bindParam(':id_apiario', $id_apiario);
            $stmt_relacao->bindParam(':id_produto', $id_produto);
            $stmt_relacao->execute();
        }
        
        $pdo->commit();
        echo "<script>alert('Produto alterado com sucesso!');</script>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao alterar produto!');</script>";
    }
    
    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// Verifica se há um termo de busca
$produtos = isset($_POST['busca']) ? buscarProdutos($pdo, $_POST['busca']) : buscarProdutos($pdo);

// Buscar apiários para os selects
$apiarios = buscarApiarios($pdo);

// Buscar produto para edição se houver ID na URL
$produto_edicao = null;
$apiario_produto = null;
if (isset($_GET['editar'])) {
    $id_produto = $_GET['editar'];
    
    // Buscar dados do produto
    $sql = "SELECT * FROM produto WHERE id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produto_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Buscar apiário relacionado ao produto
    if ($produto_edicao) {
        $sql_apiario = "SELECT id_apiario FROM apiario_produto WHERE id_produto = :id_produto";
        $stmt_apiario = $pdo->prepare($sql_apiario);
        $stmt_apiario->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt_apiario->execute();
        $apiario_relacionado = $stmt_apiario->fetch(PDO::FETCH_ASSOC);
        
        if ($apiario_relacionado) {
            $apiario_produto = $apiario_relacionado['id_apiario'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERENCIAR PRODUTOS</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_PRODUTOS.css">

</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR PRODUTOS</h1>
        
        <div class="ops_prod">
            <button id="btnAdicionar" onclick="abrirModal('modalAdicionar')">Adicionar</button>
            <form action="TELA_GERENCIAR_PRODUTOS.php" method="POST">
                <input type="text" name="busca" id="busca" placeholder="Pesquisar produto ou apiário">
                <button type="submit">Pesquisar</button>
            </form>
        </div>

        <div class="tabela_prod">
            <?php if (!empty($produtos)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo de Mel</th>
                            <th>Data Embalado</th>
                            <th>Peso (kg)</th>
                            <th>Preço (R$)</th>
                            <th>Quantidade</th>
                            <th>Apiário</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?= htmlspecialchars($produto['id_produto']) ?></td>
                                <td><?= htmlspecialchars($produto['Tipo_mel']) ?></td>
                                <td><?= htmlspecialchars($produto['Data_embalado']) ?></td>
                                <td><?= htmlspecialchars($produto['Peso']) ?></td>
                                <td>R$ <?= number_format($produto['Preco'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($produto['Quantidade']) ?></td>
                                <td><?= htmlspecialchars($produto['Nome_apiario'] ?? 'Não vinculado') ?></td>
                                <td>
                                    <a href="TELA_GERENCIAR_PRODUTOS.php?editar=<?= htmlspecialchars($produto['id_produto']) ?>">Alterar</a>
                                    <a href="TELA_GERENCIAR_PRODUTOS.php?excluir=<?= htmlspecialchars($produto['id_produto']) ?>" 
                                       class="excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum produto encontrado</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal para Adicionar Produto -->
    <div id="modalAdicionar" class="modal">
        <div class="modal-content">
            <h2>Adicionar Produto</h2>
            <form method="POST" action="TELA_GERENCIAR_PRODUTOS.php">
                <label for="Tipo_mel">Tipo de Mel:</label>
                <input type="text" name="Tipo_mel" required>

                <label for="Data_embalado">Data Embalado:</label>
                <input type="date" name="Data_embalado" required>

                <label for="Peso">Peso (kg):</label>
                <input type="number" name="Peso" step="0.01" min="0" required>

                <label for="Preco">Preço (R$):</label>
                <input type="number" name="Preco" step="0.01" min="0" required>

                <label for="Quantidade">Quantidade:</label>
                <input type="number" name="Quantidade" min="0" required>

                <label for="id_apiario">Apiário de Origem:</label>
                <select name="id_apiario">
                    <option value="">Selecione um apiário (opcional)</option>
                    <?php foreach ($apiarios as $apiario): ?>
                        <option value="<?= $apiario['id_apiario'] ?>">
                            <?= htmlspecialchars($apiario['Nome_apiario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="adicionar_produto" class="btn_acao">Adicionar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalAdicionar')">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para Alterar Produto -->
    <?php if ($produto_edicao): ?>
    <div id="modalAlterar" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Alterar Produto</h2>
            <form method="POST" action="TELA_GERENCIAR_PRODUTOS.php">
                <input type="hidden" name="id_produto" value="<?= $produto_edicao['id_produto'] ?>">
                
                <label for="Tipo_mel_editar">Tipo de Mel:</label>
                <input type="text" name="Tipo_mel" id="Tipo_mel_editar" value="<?= htmlspecialchars($produto_edicao['Tipo_mel']) ?>" required>

                <label for="Data_embalado_editar">Data Embalado:</label>
                <input type="date" name="Data_embalado" id="Data_embalado_editar" value="<?= htmlspecialchars($produto_edicao['Data_embalado']) ?>" required>

                <label for="Peso_editar">Peso (kg):</label>
                <input type="number" name="Peso" id="Peso_editar" step="0.01" min="0" value="<?= $produto_edicao['Peso'] ?>" required>

                <label for="Preco_editar">Preço (R$):</label>
                <input type="number" name="Preco" id="Preco_editar" step="0.01" min="0" value="<?= $produto_edicao['Preco'] ?>" required>

                <label for="Quantidade_editar">Quantidade:</label>
                <input type="number" name="Quantidade" id="Quantidade_editar" min="0" value="<?= $produto_edicao['Quantidade'] ?>" required>

                <label for="id_apiario_editar">Apiário de Origem:</label>
                <select name="id_apiario" id="id_apiario_editar">
                    <option value="">Selecione um apiário (opcional)</option>
                    <?php foreach ($apiarios as $apiario): ?>
                        <option value="<?= $apiario['id_apiario'] ?>" 
                            <?= ($apiario['id_apiario'] == $apiario_produto) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($apiario['Nome_apiario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="alterar_produto" class="btn_acao">Alterar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalAlterar')">Cancelar</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function abrirModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function fecharModal(id) {
            document.getElementById(id).style.display = 'none';
            // Redirecionar para a mesma página sem parâmetros de edição
            window.location.href = 'TELA_GERENCIAR_PRODUTOS.php';
        }

        // Fechar modal clicando fora dele
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                // Redirecionar para a mesma página sem parâmetros de edição
                window.location.href = 'TELA_GERENCIAR_PRODUTOS.php';
            }
        }

        // Se houver parâmetro de edição na URL, abrir o modal de alteração
        <?php if ($produto_edicao): ?>
            document.addEventListener('DOMContentLoaded', function() {
                abrirModal('modalAlterar');
            });
        <?php endif; ?>
    </script>
</body>
</html>