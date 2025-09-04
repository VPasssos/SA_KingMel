<?php
session_start();
include('../conexao.php');

// VERIFICA SE O USUÁRIO TEM PERMISSÃO
if ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3) {
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

$produtos = isset($_POST['busca']) ? buscarProdutos($pdo, $_POST['busca']) : buscarProdutos($pdo);
$apiarios = buscarApiarios($pdo);

$produto_carrinho = null;
$imagemBase64 = null;

if (isset($_GET['carrinho'])) {
    $id_produto = $_GET['carrinho'];

    // Buscar dados do produto
    $sql = "SELECT * FROM produto WHERE id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produto_carrinho = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto_carrinho && isset($produto_carrinho['foto'])) {
        $imagemBase64 = base64_encode($produto_carrinho['foto']);
    }

    // Buscar apiário relacionado ao produto para mostrar no modal
    if ($produto_carrinho) {
        $sql_apiario = "SELECT a.Nome_apiario 
                        FROM apiario a
                        JOIN apiario_produto ap ON a.id_apiario = ap.id_apiario
                        WHERE ap.id_produto = :id_produto LIMIT 1";
        $stmt_apiario = $pdo->prepare($sql_apiario);
        $stmt_apiario->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt_apiario->execute();
        $apiario_relacionado = $stmt_apiario->fetch(PDO::FETCH_ASSOC);
        // Adiciona o nome do apiário ao array do produto para usar no modal
        if ($apiario_relacionado) {
            $produto_carrinho['Nome_apiario'] = $apiario_relacionado['Nome_apiario'];
        } else {
            $produto_carrinho['Nome_apiario'] = 'Não vinculado';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_ao_carrinho'])) {
    $id_produto = $_POST['id_produto'];
    $qtd_produto = $_POST['quantidade'];
    $usuario = $_SESSION['usuario']; 

    // Buscar id_usuario
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nome = :nome");
    $stmt->bindParam(':nome', $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $usuario_dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario_dados) {
        echo "<script>alert('Usuário inválido.'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }
    $id_usuario = $usuario_dados['id_usuario'];

    // Buscar preço
    $stmt = $pdo->prepare("SELECT Preco FROM produto WHERE id_produto = :id_produto");
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produtoDados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produtoDados) {
        echo "<script>alert('Produto inválido.'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }

    $preco_unitario = $produtoDados['Preco'];
    $preco_total = $preco_unitario * $qtd_produto;

    // Buscar apiário
    $stmtApiario = $pdo->prepare("SELECT id_apiario FROM apiario_produto WHERE id_produto = :id_produto LIMIT 1");
    $stmtApiario->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmtApiario->execute();
    $apiario = $stmtApiario->fetch(PDO::FETCH_ASSOC);

    if ($apiario && $preco_total) {
        $id_apiario = $apiario['id_apiario'];

        // Inserir no carrinho
        $sql = "INSERT INTO carrinho (id_produto, qtd_produto, preco_unitario, id_apiario, id_usuario) 
                VALUES (:id_produto, :qtd_produto, :preco_unitario, :id_apiario, :id_usuario)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto);
        $stmt->bindParam(':qtd_produto', $qtd_produto);
        $stmt->bindParam(':preco_unitario', $preco_total);
        $stmt->bindParam(':id_apiario', $id_apiario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        echo "<script>alert('Produto adicionado ao carrinho com sucesso!'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro: produto sem apiário vinculado.'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }
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
    <script src="../JS/mascaras.js"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
        }
    </style>
</head>
<body onload="verificaModalCarrinho()">

<?php include("MENU.php"); ?>

<main>
    <h1>GERENCIAR PRODUTOS</h1>

    <div class="ops_prod">
        <button id="btncarrinho" onclick="abrirModal('modalcarrinho')">Carrinho</button>
        <form action="LOJA.php" method="POST">
            <input type="text" name="busca" placeholder="Pesquisar produto ou apiário" value="<?= htmlspecialchars($_POST['busca'] ?? '') ?>">
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
                        <th>Imagem</th>
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
                                <?php if ($produto['foto']): ?>
                                    <img src="data:<?= $produto['tipo_foto'] ?>;base64,<?= base64_encode($produto['foto']) ?>" alt="Imagem" width="50">
                                <?php else: ?>
                                    Sem imagem
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="TELA_LOJA.php?carrinho=<?= htmlspecialchars($produto['id_produto']) ?>">Adicionar ao carrinho</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum produto encontrado.</p>
        <?php endif; ?>
    </div>
</main>

<?php if ($produto_carrinho): ?>
    <div id="modalCarrinho" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Adicionar ao Carrinho</h2>
            <form method="POST" action="TELA_LOJA.php">
                <!-- ID do produto, campo oculto para envio -->
                <input type="hidden" name="id_produto" value="<?= htmlspecialchars($produto_carrinho['id_produto']) ?>">

                <!-- Exibição dos dados em inputs readonly -->
                <label for="id_produto_display">ID do Produto:</label>
                <input type="text" id="id_produto_display" value="<?= htmlspecialchars($produto_carrinho['id_produto']) ?>" readonly>

                <label for="tipo_mel">Tipo de Mel:</label>
                <input type="text" id="tipo_mel" value="<?= htmlspecialchars($produto_carrinho['Tipo_mel']) ?>" readonly>

                <label for="data_embalado">Data Embalado:</label>
                <input type="text" id="data_embalado" value="<?= htmlspecialchars($produto_carrinho['Data_embalado']) ?>" readonly>

                <label for="peso">Peso (kg):</label>
                <input type="text" id="peso" value="<?= htmlspecialchars($produto_carrinho['Peso']) ?>" readonly>

                <label for="preco">Preço (R$):</label>
                <input type="text" id="preco" value="<?= number_format($produto_carrinho['Preco'], 2, ',', '.') ?>" readonly>

                <label for="nome_apiario">Apiário:</label>
                <input type="text" id="nome_apiario" value="<?= htmlspecialchars($produto_carrinho['Nome_apiario'] ?? 'Não vinculado') ?>" readonly>

                <!-- Input para o usuário escolher a quantidade -->
                <label for="quantidade">Quantidade desejada:</label>
                <input type="number" name="quantidade" id="quantidade" min="1" max="<?= htmlspecialchars($produto_carrinho['Quantidade']) ?>" required>

                <!-- Exibe a imagem do produto, se houver -->
                <?php if ($imagemBase64): ?>
                    <img src="data:<?= htmlspecialchars($produto_carrinho['tipo_foto']) ?>;base64,<?= $imagemBase64 ?>" 
                     alt="Imagem do produto" 
                     style="max-width: 150px; height: auto; display: block; margin: 10px 0;">
                <?php endif; ?>



                <br><br>
                <button type="submit" name="adicionar_ao_carrinho" class="btn_acao">Confirmar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalCarrinho')">Cancelar</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Modal de visualização do carrinho -->
<div id="modalCarrinhoLista" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Meu Carrinho</h2>

        <table border="1" width="100%">
            <thead>
                <tr>
                    <th>Tipo de Mel</th>
                    <th>Apiário</th>
                    <th>Peso (kg)</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Total (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($itensCarrinho)): ?>
                    <?php 
                    $totalGeral = 0;
                    foreach ($itensCarrinho as $item): 
                        $subtotal = $item['qtd_produto'] * $item['preco_unitario'];
                        $totalGeral += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['Tipo_mel']) ?></td>
                            <td><?= htmlspecialchars($item['Nome_apiario'] ?? 'Não vinculado') ?></td>
                            <td><?= htmlspecialchars($item['Peso']) ?></td>
                            <td><?= htmlspecialchars($item['qtd_produto']) ?></td>
                            <td><?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                            <td><?= number_format($subtotal, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Seu carrinho está vazio.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5">Total Geral</th>
                    <th><?= isset($totalGeral) ? number_format($totalGeral, 2, ',', '.') : '0,00' ?></th>
                </tr>
            </tfoot>
        </table>

        <br>
        <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalCarrinhoLista')">Fechar</button>
    </div>
</div>

<script>
// Funções de abrir/fechar modal
function abrirModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function fecharModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>



<script>
function fecharModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Verifica se a URL contém ?carrinho= e abre o modal automaticamente
function verificaModalCarrinho() {
    const params = new URLSearchParams(window.location.search);
    if (params.has('carrinho')) {
        const modal = document.getElementById('modalCarrinho');
        if (modal) {
            modal.style.display = 'flex'; // Mostra o modal
        }
    }
}
</script>

</body>
</html>
