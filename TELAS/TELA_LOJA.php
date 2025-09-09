<?php
session_start();
include('../conexao.php');

// ======= PERMISSÃO =======
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3)) {
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";
    exit();
}

$busca  = $_POST["busca"]  ?? '';
$filtro = $_POST["filtro"] ?? '';

$orderBy = "p.Tipo_mel ASC";
switch ($filtro) {
    case 'preco_desc': $orderBy = "p.Preco DESC"; break;
    case 'preco_asc':  $orderBy = "p.Preco ASC";  break;
    case 'peso_desc':  $orderBy = "p.Peso DESC";  break;
    case 'peso_asc':   $orderBy = "p.Peso ASC";   break;
}

// ======= BUSCAS =======
function buscarProduto($pdo, $busca, $orderBy) {
<<<<<<< HEAD
=======
    // Mantendo sua lógica e campos usados na listagem + imagem
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
    $sql = "SELECT 
                p.id_produto, p.Tipo_mel, p.Data_embalado, p.Peso, p.Preco, p.Quantidade, 
                p.tipo_foto, p.foto, a.Nome_apiario
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

function buscarProdutos($pdo, $termo = null) {
    $sql = "SELECT p.*, a.Nome_apiario, a.id_apiario 
            FROM produto p 
            LEFT JOIN apiario_produto ap ON p.id_produto = ap.id_produto 
            LEFT JOIN apiario a ON ap.id_apiario = a.id_apiario 
            WHERE 1=1";
    if ($termo) $sql .= " AND (p.Tipo_mel LIKE :termo OR p.Data_embalado LIKE :termo OR a.Nome_apiario LIKE :termo)";
    $sql .= " GROUP BY p.id_produto ORDER BY p.Tipo_mel ASC";
    $stmt = $pdo->prepare($sql);
    if ($termo) $stmt->bindValue(':termo', '%' . $termo . '%');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarApiarios($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM apiario ORDER BY Nome_apiario ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getIdUsuario($pdo) {
    if (!isset($_SESSION['usuario'])) {
        echo "<script>alert('Sessão inválida.'); window.location.href='principal.php';</script>";
        exit();
    }
    $usuario = $_SESSION['usuario'];
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nome = :nome");
    $stmt->bindParam(':nome', $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $usuario_dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario_dados) {
        echo "<script>alert('Usuário inválido.'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }
    return (int)$usuario_dados['id_usuario'];
}

<<<<<<< HEAD
=======
// Corrigido: trazendo imagem do produto para o modal do carrinho
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
function listarcarrinho($pdo) {
    $id_usuario = getIdUsuario($pdo);
    $sql = "SELECT 
                c.id_produto, p.Tipo_mel, c.qtd_produto, c.preco_unitario, 
                a.Nome_apiario, p.foto, p.tipo_foto
            FROM carrinho AS c
            INNER JOIN produto AS p ON p.id_produto = c.id_produto
            INNER JOIN apiario AS a ON a.id_apiario = c.id_apiario
            WHERE c.id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ======= DADOS INICIAIS =======
$produtos      = buscarProduto($pdo, $busca, $orderBy);
$apiarios      = buscarApiarios($pdo);
$itensCarrinho = listarcarrinho($pdo);

$produto_carrinho = null;
$imagemBase64     = null;

// ======= MODAL: ADICIONAR AO CARRINHO (abrir via GET) =======
if (isset($_GET['carrinho'])) {
    $id_produto = (int)$_GET['carrinho'];
    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto = :id_produto");
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produto_carrinho = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto_carrinho && isset($produto_carrinho['foto'])) {
        $imagemBase64 = base64_encode($produto_carrinho['foto']);
    }

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

// ======= AÇÕES POST =======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = getIdUsuario($pdo);

    // ADICIONAR AO CARRINHO
    if (isset($_POST['adicionar_ao_carrinho'])) {
        $id_produto  = (int)$_POST['id_produto'];
        $qtd_produto = (int)$_POST['quantidade'];

        $stmtProduto = $pdo->prepare("SELECT Preco, Quantidade FROM produto WHERE id_produto = :id_produto");
        $stmtProduto->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmtProduto->execute();
        $produtoDados = $stmtProduto->fetch(PDO::FETCH_ASSOC);

        if (!$produtoDados) {
            echo "<script>alert('Produto inválido.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }

        if ($qtd_produto < 1 || $qtd_produto > (int)$produtoDados['Quantidade']) {
            echo "<script>alert('Quantidade solicitada maior que o estoque disponível.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }

        $preco_total_item = (float)$produtoDados['Preco'] * $qtd_produto;

        $stmtApiario = $pdo->prepare("SELECT id_apiario FROM apiario_produto WHERE id_produto = :id_produto LIMIT 1");
        $stmtApiario->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmtApiario->execute();
        $apiario = $stmtApiario->fetch(PDO::FETCH_ASSOC);
        if (!$apiario) {
            echo "<script>alert('Erro: produto sem apiário vinculado.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }
        $id_apiario = (int)$apiario['id_apiario'];

        $stmt = $pdo->prepare("INSERT INTO carrinho (id_produto, qtd_produto, preco_unitario, id_apiario, id_usuario) 
                               VALUES (:id_produto, :qtd_produto, :preco_unitario, :id_apiario, :id_usuario)");
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->bindParam(':qtd_produto', $qtd_produto, PDO::PARAM_INT);
<<<<<<< HEAD
=======
        // Observação: sua coluna chama 'preco_unitario', mas armazena o TOTAL do item (mantido)
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
        $stmt->bindParam(':preco_unitario', $preco_total_item);
        $stmt->bindParam(':id_apiario', $id_apiario, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Produto adicionado ao carrinho!'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }

    // COMPRAR CARRINHO
    if (isset($_POST['comprar_carrinho'])) {
        $itensCarrinho = listarcarrinho($pdo);
        if (empty($itensCarrinho)) {
            echo "<script>alert('Carrinho vazio.'); window.location.href='TELA_LOJA.php';</script>";
            exit();
        }

        $totalGeral = 0.0;
        foreach ($itensCarrinho as $item) {
            $totalGeral += (float)$item['preco_unitario'];
        }

        $stmt = $pdo->prepare("INSERT INTO compra_carrinho (id_usuario, preco_total) VALUES (:id_usuario, :preco_total)");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':preco_total', $totalGeral);
        $stmt->execute();
        $id_compra_carrinho = (int)$pdo->lastInsertId();

        foreach ($itensCarrinho as $item) {
            $stmt = $pdo->prepare("INSERT INTO compra_carrinho_produto 
                    (id_compra_carrinho, id_produto, qtd_produto, preco_unitario) 
                    VALUES (:id_compra_carrinho, :id_produto, :qtd_produto, :preco_unitario)");
            $stmt->bindParam(':id_compra_carrinho', $id_compra_carrinho, PDO::PARAM_INT);
            $stmt->bindParam(':id_produto', $item['id_produto'], PDO::PARAM_INT);
            $stmt->bindParam(':qtd_produto', $item['qtd_produto'], PDO::PARAM_INT);
            $stmt->bindParam(':preco_unitario', $item['preco_unitario']);
            $stmt->execute();

            $stmt_estoque = $pdo->prepare("UPDATE produto SET Quantidade = Quantidade - :quantidade WHERE id_produto = :id_produto");
            $stmt_estoque->bindParam(':quantidade', $item['qtd_produto'], PDO::PARAM_INT);
            $stmt_estoque->bindParam(':id_produto', $item['id_produto'], PDO::PARAM_INT);
            $stmt_estoque->execute();
        }

        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Compra realizada com sucesso!'); window.location.href='TELA_LOJA.php';</script>";
        exit();
    }
}

// ======= EXCLUIR ITEM DO CARRINHO =======
if (isset($_GET['excluir'])) {
    $id_produto = (int)$_GET['excluir'];
    try {
        $pdo->beginTransaction();
        $sql = "DELETE FROM carrinho WHERE id_produto = :id_produto AND id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', getIdUsuario($pdo), PDO::PARAM_INT);
        if ($stmt->execute()) {
            $pdo->commit();
            echo "<script>alert('Produto excluído com sucesso!'); window.location.href='TELA_LOJA.php';</script>";
        } else {
            throw new Exception("Erro ao excluir produto");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao excluir produto!'); window.location.href='TELA_LOJA.php';</script>";
    }
    exit();
}

// ======= VISUALIZAR COMPRAS =======
function VisualizarCompras($pdo) {
    $id_usuario = getIdUsuario($pdo);
    $sql = "SELECT 
                c.id_compra_carrinho, u.id_usuario, c.data_compra, c.preco_total, c.status, p.Tipo_mel, p.tipo_foto, p.foto
            FROM compra_carrinho AS c
            INNER JOIN usuario AS u ON u.id_usuario = c.id_usuario
            INNER JOIN compra_carrinho_produto as ccp ON ccp.id_compra_carrinho = c.id_compra_carrinho
            INNER JOIN produto as p ON ccp.id_produto = p.id_produto
            WHERE c.id_usuario = :id_usuario
            ORDER BY c.data_compra DESC, c.id_compra_carrinho DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
<<<<<<< HEAD
}

=======


}
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
$compras = VisualizarCompras($pdo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <title>King Mel – Loja</title>
=======
    <title>🐝 King Mel – Loja</title>
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c

    <!-- Google Fonts para título mais bonito -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <!-- Estilos externos existentes -->
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_PRODUTOS.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_IMAGENS.css">
<<<<<<< HEAD
    <style>
        /* Estilos específicos para a loja no padrão de gerenciamento */
        .loja-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .loja-titulo {
            color: var(--comeia);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            padding-bottom: 15px;
        }
        
        .loja-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            gap: 20px;
        }
        
        .loja-actions button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .loja-actions button:hover {
            background-color: var(--hover-color);
        }
        
        .search-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-filter input,
        .search-filter select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: var(--input-bg);
        }
        
        .search-filter input:focus,
        .search-filter select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(224, 165, 0, 0.2);
        }
        
        .search-filter button {
            padding: 12px 20px;
        }
        
        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .produto-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px var(--shadow-color);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .produto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .produto-img {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9f9f9;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .produto-img img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f2f2f2;
            color: #888;
            font-size: 14px;
            border-radius: 4px;
        }
        
        .produto-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .produto-titulo {
            font-size: 18px;
            font-weight: 700;
            color: var(--cinza-escuro);
            margin-bottom: 10px;
        }
        
        .produto-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #666;
        }
        
        .produto-meta span {
            background: #f3f3f3;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .preco {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-color);
            margin: 10px 0;
        }
        
        .produto-estoque {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .badge-estoque {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .em-estoque {
            background-color: #28a745;
        }
        
        .sem-estoque {
            background-color: #dc3545;
        }
        
        .qtd {
            font-size: 13px;
            color: #666;
        }
        
        .produto-acoes {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-add {
            display: block;
            width: 100%;
            text-align: center;
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-add:hover {
            background-color: var(--hover-color);
        }
        
        .btn-add[disabled] {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
        }
        
        /* Modal de carrinho no padrão de gerenciamento */
        .modal-carrinho-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }
        
        .listagem-cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .card-item {
            display: flex;
            gap: 12px;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px var(--shadow-color);
            padding: 12px;
        }
        
        .card-item img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid #eee;
        }
        
        .card-content {
            flex: 1;
        }
        
        .card-title {
            font-weight: 700;
            font-size: 15px;
            color: #222;
        }
        
        .card-meta {
            font-size: 13px;
            color: #555;
        }
        
        .card-preco {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-color);
            margin-top: 4px;
        }
        
        .card-actions a {
            color: #e74c3c;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .card-actions a:hover {
            color: #c0392b;
            text-decoration: underline;
        }
        
        .total-resumo {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            color: #222;
            display: flex;
            justify-content: space-between;
        }
        
        /* Modal de adicionar produto */
        .modal-produto {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .modal-produto img {
            max-width: 180px;
            max-height: 180px;
            object-fit: contain;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid #eee;
            background: #fff;
            padding: 6px;
        }
        
        .modal-produto .produto-nome {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #222;
        }
        
        .modal-produto .produto-apiario {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .modal-produto .produto-preco {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        
        .modal-produto .produto-peso {
            font-size: 14px;
            color: #444;
            margin-bottom: 12px;
        }
        
        .modal-produto .estoque {
            font-size: 13px;
            margin-bottom: 14px;
            color: #27ae60;
            font-weight: 600;
        }
        
        .modal-produto .estoque.esgotado {
            color: #c0392b;
        }
        
        .modal-produto .qtd-box {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .modal-produto .qtd-box input {
            width: 80px;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .loja-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-filter {
                width: 100%;
            }
            
            .search-filter input,
            .search-filter select {
                width: 100%;
            }
            
            .produtos-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .modal-carrinho-content {
                width: 95%;
                padding: 20px;
            }
            
            .card-item {
                flex-direction: column;
                text-align: center;
            }
            
            .card-actions {
                align-items: center;
            }
        }
    </style>
=======
    <link rel="stylesheet" href="../ESTILOS/ESTILO_LOJA2.css">

>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
    <script src="../JS/mascaras.js"></script>
</head>
<body onload="verificaModalCarrinho()">

<?php include("MENU.php"); ?>

<<<<<<< HEAD
<div class="loja-container">
    <h1 class="loja-titulo">
        King Mel – Sua Loja de Mel Premium
    </h1>

    <!-- Barra de ações/Busca/Filtro -->
    <div class="loja-actions">
        <div class="actions-left">
            <button id="btncarrinho" onclick="abrirModal('modalCarrinhoLista')">🛒 Carrinho</button>
            <button id="btncompras" onclick="abrirModal('modalVisualizarCompras')">🧾 Minhas compras</button>
        </div>

        <form action="TELA_LOJA.php" method="POST" class="search-filter">
            <input type="text" name="busca" placeholder="Buscar tipo de mel..." value="<?= htmlspecialchars($busca) ?>">
            <select name="filtro" aria-label="Ordenar por">
                <option value="">Ordenar por</option>
                <option value="preco_desc" <?= ($filtro == 'preco_desc') ? 'selected' : '' ?>>Maior preço</option>
                <option value="preco_asc" <?= ($filtro == 'preco_asc') ? 'selected' : '' ?>>Menor preço</option>
                <option value="peso_desc" <?= ($filtro == 'peso_desc') ? 'selected' : '' ?>>Peso maior</option>
                <option value="peso_asc" <?= ($filtro == 'peso_asc') ? 'selected' : '' ?>>Peso menor</option>
=======
<main>
    <h1 class="loja-titulo">
        🐝 King Mel – Sua Loja de Mel Premium
    </h1>

    <!-- Barra de ações/Busca/Filtro -->
    <div class="ops_prod loja-actions">
        <div class="actions-left">
            <button id="btncarrinho" onclick="abrirModal('modalCarrinhoLista')">🛒 Carrinho</button>
            <button id="btncompras"  onclick="abrirModal('modalVisualizarCompras')">🧾 Minhas compras</button>
        </div>

        <form action="TELA_LOJA.php" method="POST" class="search-filter">
            <input type="text" name="busca" placeholder="Buscar tipo de mel..." value="<?= htmlspecialchars($_POST['busca'] ?? '') ?>">
            <select name="filtro" aria-label="Ordenar por">
                <option value="">Ordenar por</option>
                <option value="preco_desc" <?= (($_POST['filtro'] ?? '') == 'preco_desc') ? 'selected' : '' ?>>Maior preço</option>
                <option value="preco_asc"  <?= (($_POST['filtro'] ?? '') == 'preco_asc')  ? 'selected' : '' ?>>Menor preço</option>
                <option value="peso_desc"  <?= (($_POST['filtro'] ?? '') == 'peso_desc')  ? 'selected' : '' ?>>Peso maior</option>
                <option value="peso_asc"   <?= (($_POST['filtro'] ?? '') == 'peso_asc')   ? 'selected' : '' ?>>Peso menor</option>
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
            </select>
            <button type="submit">Pesquisar</button>
        </form>
    </div>

    <!-- LISTA DE PRODUTOS EM CARDS -->
    <?php if (!empty($produtos)): ?>
        <div class="produtos-grid">
            <?php foreach ($produtos as $produto): ?>
                <div class="produto-card">
                    <div class="produto-img">
                        <?php if (!empty($produto['foto'])): ?>
                            <img src="data:<?= htmlspecialchars($produto['tipo_foto']) ?>;base64,<?= base64_encode($produto['foto']) ?>" alt="<?= htmlspecialchars($produto['Tipo_mel']) ?>">
                        <?php else: ?>
                            <div class="img-placeholder">Sem imagem</div>
                        <?php endif; ?>
                    </div>

                    <div class="produto-info">
                        <h3 class="produto-titulo"><?= htmlspecialchars($produto['Tipo_mel']) ?></h3>
                        <div class="produto-meta">
                            <span class="apiario" title="Apiário"><?= htmlspecialchars($produto['Nome_apiario'] ?? 'Não vinculado') ?></span>
                            <span class="peso" title="Peso"><?= htmlspecialchars($produto['Peso']) ?> kg</span>
                            <span class="data" title="Data Embalado"><?= htmlspecialchars($produto['Data_embalado']) ?></span>
                        </div>
                        <div class="preco">R$ <?= number_format($produto['Preco'], 2, ',', '.') ?></div>

                        <div class="produto-estoque">
                            <span class="badge-estoque <?= ((int)$produto['Quantidade'] > 0 ? 'em-estoque' : 'sem-estoque') ?>">
                                <?= (int)$produto['Quantidade'] > 0 ? 'Em estoque' : 'Indisponível' ?>
                            </span>
                            <small class="qtd">Qtd: <?= (int)$produto['Quantidade'] ?></small>
                        </div>
                    </div>

                    <div class="produto-acoes">
                        <?php if ((int)$produto['Quantidade'] > 0): ?>
                            <a class="btn-add" href="TELA_LOJA.php?carrinho=<?= (int)$produto['id_produto'] ?>">Adicionar ao carrinho</a>
                        <?php else: ?>
                            <button class="btn-add" disabled>Indisponível</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Nenhum produto encontrado.</p>
    <?php endif; ?>
<<<<<<< HEAD
</div>
=======
</main>
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c

<!-- MODAIS: ADICIONAR AO CARRINHO, VISUALIZAR CARRINHO E COMPRAS -->
<?php if ($produto_carrinho): ?>
<div id="modalCarrinho" class="modal">
<<<<<<< HEAD
  <div class="modal-content">
=======
  <div class="modal-content modal-md">
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
    <h2>Adicionar ao Carrinho</h2>
    <form method="POST" action="TELA_LOJA.php" class="modal-produto">
      <input type="hidden" name="id_produto" value="<?= (int)$produto_carrinho['id_produto'] ?>">

      <?php if ($imagemBase64): ?>
        <img src="data:<?= htmlspecialchars($produto_carrinho['tipo_foto']) ?>;base64,<?= $imagemBase64 ?>" alt="Imagem do produto">
      <?php else: ?>
        <img src="../IMAGENS/sem-foto.png" alt="Sem imagem">
      <?php endif; ?>

      <div class="produto-nome"><?= htmlspecialchars($produto_carrinho['Tipo_mel']) ?></div>
      <div class="produto-apiario">Apiário: <?= htmlspecialchars($produto_carrinho['Nome_apiario']) ?></div>
      <div class="produto-preco">R$ <?= number_format($produto_carrinho['Preco'], 2, ',', '.') ?></div>
      <div class="produto-peso">Peso: <?= htmlspecialchars($produto_carrinho['Peso']) ?> kg • Embalado em <?= htmlspecialchars($produto_carrinho['Data_embalado']) ?></div>

      <?php if ((int)$produto_carrinho['Quantidade'] > 0): ?>
        <div class="estoque">Em estoque (<?= (int)$produto_carrinho['Quantidade'] ?> disponíveis)</div>
        <div class="qtd-box">
          <label for="qtd">Quantidade:</label>
          <input type="number" id="qtd" name="quantidade" min="1" max="<?= (int)$produto_carrinho['Quantidade'] ?>" required>
        </div>
      <?php else: ?>
        <div class="estoque esgotado">Produto indisponível</div>
      <?php endif; ?>

      <div class="modal-actions">
        <?php if ((int)$produto_carrinho['Quantidade'] > 0): ?>
          <button type="submit" name="adicionar_ao_carrinho" class="btn_acao">Confirmar</button>
        <?php endif; ?>
        <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalCarrinho')">Cancelar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div id="modalCarrinhoLista" class="modal">
<<<<<<< HEAD
  <div class="modal-carrinho-content">
=======
  <div class="modal-content modal-lg">
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
    <h2>Meu Carrinho</h2>
    <form method="POST" action="TELA_LOJA.php">
      <div class="listagem-cards">
        <?php if (!empty($itensCarrinho)):
          $totalGeral = 0.0;
          foreach($itensCarrinho as $item):
            $totalGeral += (float)$item['preco_unitario'];
        ?>
          <div class="card-item">
            <?php if (!empty($item['foto'])): ?>
              <img src="data:<?= htmlspecialchars($item['tipo_foto']) ?>;base64,<?= base64_encode($item['foto']) ?>" alt="Produto">
            <?php else: ?>
              <img src="../IMAGENS/sem-foto.png" alt="Sem imagem">
            <?php endif; ?>
            <div class="card-content">
              <div class="card-title"><?= htmlspecialchars($item['Tipo_mel']) ?></div>
              <div class="card-meta"><?= htmlspecialchars($item['Nome_apiario']) ?> • Qtd: <?= (int)$item['qtd_produto'] ?></div>
              <div class="card-preco">R$ <?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?></div>
            </div>
            <div class="card-actions">
              <a href="TELA_LOJA.php?excluir=<?= (int)$item['id_produto'] ?>" onclick="return confirm('Excluir este produto do carrinho?')">Remover</a>
            </div>
          </div>
        <?php endforeach; else: ?>
          <p>Carrinho vazio.</p>
        <?php endif; ?>
      </div>
      <div class="total-resumo">
        <span>Total:</span>
        <span>R$ <?= number_format($totalGeral ?? 0, 2, ',', '.') ?></span>
      </div>
      <div class="modal-actions">
        <button type="submit" name="comprar_carrinho" class="btn_acao">Finalizar Compra</button>
        <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalCarrinhoLista')">Fechar</button>
      </div>
    </form>
  </div>
</div>

<div id="modalVisualizarCompras" class="modal">
<<<<<<< HEAD
  <div class="modal-carrinho-content">
=======
  <div class="modal-content modal-lg">
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
    <h2>Minhas Compras</h2>
    <div class="listagem-cards">
      <?php if (!empty($compras)): foreach($compras as $compra): ?>
        <div class="card-item">
<<<<<<< HEAD
          <?php if ($compra && !empty($compra['foto'])): ?>
            <img src="data:<?= $compra['tipo_foto'] ?>;base64,<?= base64_encode($compra['foto']) ?>" alt="Foto do produto" width="50" height="auto">
          <?php else: ?>
            <img src="../IMAGENS/sem-foto.png" alt="Sem imagem">
          <?php endif; ?>
=======
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
          <div class="card-content">
            <div class="card-title">Compra #<?= (int)$compra['id_compra_carrinho'] ?></div>
            <div class="card-meta"><?= htmlspecialchars($compra['data_compra']) ?> • Produto: <?= htmlspecialchars($compra['Tipo_mel']) ?></div>
            <div class="card-preco">R$ <?= number_format((float)$compra['preco_total'], 2, ',', '.') ?></div>
<<<<<<< HEAD
          </div>
          <div class="card-actions">
            <span style="color:<?= ($compra['status']=='Concluída'?'green':'#c0392b') ?>; font-weight:700;">
              <?= htmlspecialchars($compra['status'] ?? 'Pendente') ?>
=======
            <div class="foto-produto">
                <?php if ($compra): ?>
                    <img src="data:<?=$compra['tipo_foto']?>;base64,<?=base64_encode($compra['foto'])?>" alt="Foto do produto" width="50" height="auto">
                <?php endif; ?>
            </div>
          </div>
          <div class="card-actions">
            <span style="color:<?= ($compra['status']=='Concluída'?'green':'#c0392b') ?>; font-weight:700;">
              <?= htmlspecialchars($compra['status']) ?>
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
            </span>
          </div>
        </div>
      <?php endforeach; else: ?>
        <p>Você ainda não fez nenhuma compra.</p>
      <?php endif; ?>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalVisualizarCompras')">Fechar</button>
    </div>
  </div>
</div>

<script>
function abrirModal(id){ var el=document.getElementById(id); if(el) el.style.display='flex'; }
function fecharModal(id){ var el=document.getElementById(id); if(el) el.style.display='none'; }
function verificaModalCarrinho(){
    const params=new URLSearchParams(window.location.search);
    if(params.has('carrinho')){abrirModal('modalCarrinho');}
}
// Fecha modal clicando fora do conteúdo
document.addEventListener('click', function(e){
    const modal = e.target.closest('.modal');
<<<<<<< HEAD
    const content = e.target.closest('.modal-content, .modal-carrinho-content');
=======
    const content = e.target.closest('.modal-content');
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
    if(modal && !content){ modal.style.display='none'; }
});
</script>

</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> c082375b032df07d26ba6cdef482eb2de869931c
