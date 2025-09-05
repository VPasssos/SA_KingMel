<?php
session_start();
include('../conexao.php');

// VERIFICA SE O USUÁRIO TEM PERMISSÃO
if ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3) {
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";
    exit();
}

$busca = $_POST["busca"] ?? '';
$filtro = $_POST["filtro"] ?? '';

$orderBy = "p.Tipo_mel ASC";

switch ($filtro) {
    case 'preco_desc':
        $orderBy = "p.Preco DESC";
        break;
    case 'preco_asc':
        $orderBy = "p.Preco ASC";
        break;
    case 'peso_desc':
        $orderBy = "p.Peso DESC";
        break;
    case 'peso_asc':
        $orderBy = "p.Peso ASC";
        break;
}

//Função busca produto pelo nome
function buscarProduto($pdo, $busca, $orderBy) {
    $sql = "SELECT p.id_produto, p.Tipo_mel, p.Data_embalado, p.Peso, p.Preco, p.Quantidade,p.tipo_foto,p.foto, a.Nome_apiario
            FROM produto AS p
            LEFT JOIN apiario_produto AS ap ON ap.id_produto = p.id_produto
            LEFT JOIN apiario AS a ON a.id_apiario = ap.id_apiario
            WHERE p.Tipo_mel LIKE :busca
            GROUP BY p.Tipo_mel
            ORDER BY $orderBy";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

// Função para buscar id do usuário
function getIdUsuario($pdo) {
    $usuario = $_SESSION['usuario'];
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nome = :nome");
    $stmt->bindParam(':nome', $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $usuario_dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario_dados) {
        echo "<script>alert('Usuário inválido.'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }
    return $usuario_dados['id_usuario'];
}

// Função para listar carrinho do usuário
function listarcarrinho($pdo) {
    $id_usuario = getIdUsuario($pdo);
    $sql = "SELECT c.id_produto, p.Tipo_mel, c.qtd_produto, c.preco_unitario, a.Nome_apiario
            FROM carrinho AS c
            INNER JOIN produto AS p ON p.id_produto = c.id_produto
            INNER JOIN apiario AS a ON a.id_apiario = c.id_apiario
            WHERE c.id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Buscar produtos, apiários e carrinho
$produtos = buscarProduto($pdo, $busca, $orderBy);
$apiarios = buscarApiarios($pdo);
$itensCarrinho = listarcarrinho($pdo);

$produto_carrinho = null;
$imagemBase64 = null;

// Modal para adicionar ao carrinho
if (isset($_GET['carrinho'])) {
    $id_produto = $_GET['carrinho'];

    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto = :id_produto");
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produto_carrinho = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto_carrinho && isset($produto_carrinho['foto'])) {
        $imagemBase64 = base64_encode($produto_carrinho['foto']);
    }

    // Buscar apiário vinculado
    if ($produto_carrinho) {
        $stmt_apiario = $pdo->prepare("SELECT a.Nome_apiario 
                                       FROM apiario a
                                       JOIN apiario_produto ap ON a.id_apiario = ap.id_apiario
                                       WHERE ap.id_produto = :id_produto LIMIT 1");
        $stmt_apiario->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt_apiario->execute();
        $apiario_relacionado = $stmt_apiario->fetch(PDO::FETCH_ASSOC);
        $produto_carrinho['Nome_apiario'] = $apiario_relacionado['Nome_apiario'] ?? 'Não vinculado';
    }
}

// Ações POST: adicionar ao carrinho ou comprar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = getIdUsuario($pdo);

    // Adicionar ao carrinho
    if (isset($_POST['adicionar_ao_carrinho'])) {
        $id_produto = $_POST['id_produto'];
        $qtd_produto = $_POST['quantidade'];

        // Buscar preço e estoque do produto
        $stmtProduto = $pdo->prepare("SELECT Preco, Quantidade FROM produto WHERE id_produto = :id_produto");
        $stmtProduto->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmtProduto->execute();
        $produtoDados = $stmtProduto->fetch(PDO::FETCH_ASSOC);

        if (!$produtoDados) {
            echo "<script>alert('Produto inválido.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }

        // Verifica estoque
        if ($qtd_produto > $produtoDados['Quantidade']) {
            echo "<script>alert('Quantidade solicitada maior que o estoque disponível.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }

        // Calcula preço total do item
        $preco_total = $produtoDados['Preco'] * $qtd_produto;

        // Buscar apiário
        $stmtApiario = $pdo->prepare("SELECT id_apiario FROM apiario_produto WHERE id_produto = :id_produto LIMIT 1");
        $stmtApiario->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmtApiario->execute();
        $apiario = $stmtApiario->fetch(PDO::FETCH_ASSOC);
        if (!$apiario) {
            echo "<script>alert('Erro: produto sem apiário vinculado.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }
        $id_apiario = $apiario['id_apiario'];

        // Inserir no carrinho
        $stmt = $pdo->prepare("INSERT INTO carrinho (id_produto, qtd_produto, preco_unitario, id_apiario, id_usuario) 
                               VALUES (:id_produto, :qtd_produto, :preco_unitario, :id_apiario, :id_usuario)");
        $stmt->bindParam(':id_produto', $id_produto);
        $stmt->bindParam(':qtd_produto', $qtd_produto);
        $stmt->bindParam(':preco_unitario', $preco_total);
        $stmt->bindParam(':id_apiario', $id_apiario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        echo "<script>alert('Produto adicionado ao carrinho!'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }

    // Comprar carrinho
    if (isset($_POST['comprar_carrinho'])) {
        $itensCarrinho = listarcarrinho($pdo);
        if (empty($itensCarrinho)) {
            echo "<script>alert('Carrinho vazio.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }

        // Total geral
        $totalGeral = 0;
        foreach ($itensCarrinho as $item) {
            $totalGeral += $item['preco_unitario'];
        }

        // Inserir compra
        $stmt = $pdo->prepare("INSERT INTO compra_carrinho (id_usuario, preco_total) VALUES (:id_usuario, :preco_total)");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':preco_total', $totalGeral);
        $stmt->execute();
        $id_compra_carrinho = $pdo->lastInsertId();

        // Inserir produtos da compra e atualizar estoque
        foreach ($itensCarrinho as $item) {
            // Inserir na tabela compra_carrinho_produto
            $stmt = $pdo->prepare("INSERT INTO compra_carrinho_produto (id_compra_carrinho, id_produto, qtd_produto, preco_unitario) 
                                   VALUES (:id_compra_carrinho, :id_produto, :qtd_produto, :preco_unitario)");
            $stmt->bindParam(':id_compra_carrinho', $id_compra_carrinho, PDO::PARAM_INT);
            $stmt->bindParam(':id_produto', $item['id_produto'], PDO::PARAM_INT);
            $stmt->bindParam(':qtd_produto', $item['qtd_produto'], PDO::PARAM_INT);
            $stmt->bindParam(':preco_unitario', $item['preco_unitario']);
            $stmt->execute();

            // Atualizar estoque
            $stmt_estoque = $pdo->prepare("UPDATE produto SET Quantidade = Quantidade - :quantidade WHERE id_produto = :id_produto");
            $stmt_estoque->bindParam(':quantidade', $item['qtd_produto'], PDO::PARAM_INT);
            $stmt_estoque->bindParam(':id_produto', $item['id_produto'], PDO::PARAM_INT);
            $stmt_estoque->execute();
        }

        // Limpar carrinho
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Compra realizada com sucesso!'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }
}

// Função para excluir um produto do carrinho
if (isset($_GET['excluir'])) {
    $id_produto = $_GET['excluir'];

    try {
        $pdo->beginTransaction();
        
        // Depois excluir o produto
        $sql = "DELETE FROM carrinho WHERE id_produto = :id_produto";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $pdo->commit();
            echo "<script>alert('Produto excluído com sucesso!'); window.location.href='TELA_LOJA.php';</script>";
        } else {
            throw new Exception("Erro ao excluir produto");
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao excluir produto!'); window.location.href='abrirModal('modalCarrinhoLista')';</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>GERENCIAR PRODUTOS</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_PRODUTOS.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_IMAGENS.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_LOJA.css">
    <script src="../JS/mascaras.js"></script>
    <style>
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: #fff; padding:20px; border-radius:10px; width:400px; }
    </style>
</head>
<body onload="verificaModalCarrinho()">

<?php include("MENU.php"); ?>

<main>
    <h1>GERENCIAR PRODUTOS</h1>

    <div class="ops_prod">
        <button id="btncarrinho" onclick="abrirModal('modalCarrinhoLista')">Carrinho</button>
        <form action="TELA_LOJA.php" method="POST">
            <input type="text" name="busca" placeholder="Pesquisar produto" value="<?= htmlspecialchars($_POST['busca'] ?? '') ?>">
            <select name="filtro">
                <option value="">Ordenar por</option>
                <option value="preco_desc" <?= (($_POST['filtro'] ?? '') == 'preco_desc') ? 'selected' : '' ?>>Maior preço</option>
                <option value="preco_asc" <?= (($_POST['filtro'] ?? '') == 'preco_asc') ? 'selected' : '' ?>>Menor preço</option>
                <option value="peso_desc" <?= (($_POST['filtro'] ?? '') == 'peso_desc') ? 'selected' : '' ?>>Peso maior</option>
                <option value="peso_asc" <?= (($_POST['filtro'] ?? '') == 'peso_asc') ? 'selected' : '' ?>>Peso menor</option>
            </select>
            <button type="submit">Pesquisar</button>
        </form>
    </div>

    <div class="tabela_prod">
        <?php if (!empty($produtos)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>Tipo de Mel</th><th>Data Embalado</th><th>Peso (kg)</th>
                        <th>Preço (R$)</th><th>Quantidade</th><th>Apiário</th><th>Imagem</th><th>Ações</th>
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
                                <?php if ($produto['foto']): ?>
                                    <img src="data:<?= $produto['tipo_foto'] ?>;base64,<?= base64_encode($produto['foto']) ?>" width="50">
                                <?php else: ?>Sem imagem<?php endif; ?>
                            </td>
                            <td><a href="TELA_LOJA.php?carrinho=<?= htmlspecialchars($produto['id_produto']) ?>">Adicionar ao carrinho</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?><p>Nenhum produto encontrado.</p><?php endif; ?>
    </div>
</main>

<!-- Modal Adicionar ao Carrinho -->
<?php if ($produto_carrinho): ?>
<div id="modalCarrinho" class="modal">
    <div class="modal-content">
        <h2>Adicionar ao Carrinho</h2>
        <form method="POST" action="TELA_LOJA.php">
            <input type="hidden" name="id_produto" value="<?= htmlspecialchars($produto_carrinho['id_produto']) ?>">
            <label>Tipo de Mel:</label>
            <input type="text" value="<?= htmlspecialchars($produto_carrinho['Tipo_mel']) ?>" readonly>
            <label>Data Embalado:</label>
            <input type="text" value="<?= htmlspecialchars($produto_carrinho['Data_embalado']) ?>" readonly>
            <label>Peso (kg):</label>
            <input type="text" value="<?= htmlspecialchars($produto_carrinho['Peso']) ?>" readonly>
            <label>Preço (R$):</label>
            <input type="text" value="<?= number_format($produto_carrinho['Preco'], 2, ',', '.') ?>" readonly>
            <label>Apiário:</label>
            <input type="text" value="<?= htmlspecialchars($produto_carrinho['Nome_apiario']) ?>" readonly>
            <label>Quantidade:</label>
            <input type="number" name="quantidade" min="1" max="<?= htmlspecialchars($produto_carrinho['Quantidade']) ?>" required>
            <?php if ($imagemBase64): ?>
                <img src="data:<?= htmlspecialchars($produto_carrinho['tipo_foto']) ?>;base64,<?= $imagemBase64 ?>" style="max-width:150px; display:block; margin:10px 0;">
            <?php endif; ?>
            <br><button type="submit" name="adicionar_ao_carrinho" class="btn_acao">Confirmar</button>
            <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalCarrinho')">Cancelar</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Visualizar Carrinho -->
<div id="modalCarrinhoLista" class="modal">
    <div class="modal-content">
        <h2>Meu Carrinho</h2>
        <form method="POST" action="TELA_LOJA.php">
            <table border="1" width="100%">
                <thead>
                    <tr>
                        <th>Tipo de Mel</th><th>Apiário</th><th>Quantidade</th>
                        <th>Preço Total (R$)</th><th>Imagem</th><th>Excluir do carrinho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($itensCarrinho)): 
                        $totalGeral=0;
                        foreach($itensCarrinho as $item):
                            $totalGeral += $item['preco_unitario'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['Tipo_mel']) ?></td>
                            <td><?= htmlspecialchars($item['Nome_apiario']) ?></td>
                            <td><?= $item['qtd_produto'] ?></td>
                            <td><?= number_format($item['preco_unitario'],2,',','.') ?></td>
                            <td> 
                                <?php if ($produto['foto']): ?>
                                    <img src="data:<?= $produto['tipo_foto'] ?>;base64,<?= base64_encode($produto['foto']) ?>" width="15">
                                <?php else: ?>Sem imagem<?php endif; ?>
                            </td>
                            <td><a href="TELA_LOJA.php?excluir=<?= htmlspecialchars($item['id_produto']) ?>" 
                                       class="excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir este produto do carrinho?')">Excluir</a></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4">Carrinho vazio.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="3">Total Geral</th><th><?= number_format($totalGeral ?? 0,2,',','.') ?></th></tr>
                </tfoot>
            </table>
            <br>
            <button type="submit" name="comprar_carrinho" class="btn_acao">Comprar</button>
            <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalCarrinhoLista')">Fechar</button>
        </form>
    </div>
</div>

<script>
function abrirModal(id){document.getElementById(id).style.display='flex';}
function fecharModal(id){document.getElementById(id).style.display='none';}
function verificaModalCarrinho(){
    const params=new URLSearchParams(window.location.search);
    if(params.has('carrinho')){abrirModal('modalCarrinho');}
}
</script>

</body>
</html>
