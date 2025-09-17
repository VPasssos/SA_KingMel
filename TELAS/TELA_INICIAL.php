<?php
session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// VERIFICA SE O USUARIO ESTÁ LOGADO
if(!isset($_SESSION['usuario'])){
    header("Location: TELA_LOGIN.php");
    exit();
}

// Função para buscar clientes
function buscarClientes($pdo) {
    $sql = "SELECT id_cliente, nome FROM cliente ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar produtos
function buscarProdutos($pdo) {
    $sql = "SELECT id_produto, Tipo_mel, Preco, Quantidade FROM produto WHERE Quantidade > 0 ORDER BY Tipo_mel ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar vendas do dia
function buscarVendasHoje($pdo) {
    $sql = "SELECT p.Numero_pedido, p.Preco, c.nome as cliente_nome, p.Data_pedido 
            FROM pedido p 
            INNER JOIN cliente c ON p.Id_cliente = c.id_cliente 
            WHERE DATE(p.Data_pedido) = CURDATE() 
            ORDER BY p.Data_pedido DESC 
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para contar vendas do dia
function contarVendasHoje($pdo) {
    $sql = "SELECT COUNT(*) as total FROM pedido WHERE DATE(Data_pedido) = CURDATE()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Função para buscar vendas por termo de busca
function buscarVendas($pdo, $termo = null) {
    $sql = "SELECT p.*, c.nome as cliente_nome 
            FROM pedido p 
            INNER JOIN cliente c ON p.Id_cliente = c.id_cliente 
            WHERE 1=1";
    
    if ($termo) {
        $sql .= " AND (p.Numero_pedido LIKE :termo OR c.nome LIKE :termo OR p.Local_entrega LIKE :termo)";
    }
    
    $sql .= " ORDER BY p.Data_pedido DESC LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    
    if ($termo) {
        $stmt->bindValue(':termo', '%' . $termo . '%');
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para registrar uma venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_venda'])) {
    $id_cliente = $_POST['id_cliente'];
    $id_produto = $_POST['id_produto'];
    $quantidade = $_POST['quantidade'];
    $preco_unitario = $_POST['preco_unitario'];
    $total = $quantidade * $preco_unitario;
    
    // Gerar número do pedido
    $numero_pedido = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
    
    try {
        $pdo->beginTransaction();
        
        // 1. Registrar o pedido
        $sql_pedido = "INSERT INTO pedido (Numero_pedido, Preco, Id_cliente, Local_entrega, Data_pedido) 
                       VALUES (:numero_pedido, :preco, :id_cliente, :local_entrega, NOW())";
        $stmt_pedido = $pdo->prepare($sql_pedido);
        
        // Buscar endereço do cliente
        $sql_cliente = "SELECT Endereco FROM cliente WHERE id_cliente = :id_cliente";
        $stmt_cliente = $pdo->prepare($sql_cliente);
        $stmt_cliente->bindParam(':id_cliente', $id_cliente);
        $stmt_cliente->execute();
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        $local_entrega = $cliente['Endereco'];
        
        $stmt_pedido->bindParam(':numero_pedido', $numero_pedido);
        $stmt_pedido->bindParam(':preco', $total);
        $stmt_pedido->bindParam(':id_cliente', $id_cliente);
        $stmt_pedido->bindParam(':local_entrega', $local_entrega);
        $stmt_pedido->execute();
        
        $id_pedido = $pdo->lastInsertId();
        
        // 2. Registrar item do pedido
        $sql_item = "INSERT INTO item_pedido (id_produto, qtd_produto, preco_unitario, id_pedido) 
                     VALUES (:id_produto, :quantidade, :preco_unitario, :id_pedido)";
        $stmt_item = $pdo->prepare($sql_item);
        $stmt_item->bindParam(':id_produto', $id_produto);
        $stmt_item->bindParam(':quantidade', $quantidade);
        $stmt_item->bindParam(':preco_unitario', $preco_unitario);
        $stmt_item->bindParam(':id_pedido', $id_pedido);
        $stmt_item->execute();
        
        // 3. Atualizar estoque do produto
        $sql_estoque = "UPDATE produto SET Quantidade = Quantidade - :quantidade WHERE id_produto = :id_produto";
        $stmt_estoque = $pdo->prepare($sql_estoque);
        $stmt_estoque->bindParam(':quantidade', $quantidade);
        $stmt_estoque->bindParam(':id_produto', $id_produto);
        $stmt_estoque->execute();
        
        $pdo->commit();
        
        $mensagem_sucesso = "Venda registrada com sucesso! Nº do pedido: $numero_pedido";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensagem_erro = "Erro ao registrar venda: " . $e->getMessage();
    }
}

// Buscar clientes e produtos
$clientes = buscarClientes($pdo);
$produtos = buscarProdutos($pdo);
$vendas_hoje = buscarVendasHoje($pdo);
$total_vendas_hoje = contarVendasHoje($pdo);

// Verificar se há busca de vendas
$vendas_busca = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_vendas'])) {
    $termo_busca = $_POST['termo_busca'];
    $vendas_busca = buscarVendas($pdo, $termo_busca);
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>King Mel - Sistema de Vendas</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTIO_INICIAL.css">
    
</head>
<body>
    <?php include("MENU.php"); ?>

    <div class="container">
        <div class="header">
            <h1>King Mel - Sistema de Gestão</h1>
            <p>Bem-vindo, <?php echo $_SESSION['usuario']; ?>!</p>
        </div>

        <?php if(isset($mensagem_sucesso)): ?>
            <div class="mensagem sucesso">
                <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($mensagem_erro)): ?>
            <div class="mensagem erro">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>

        <div class="busca-vendas">
            <h2>Buscar Vendas</h2>
            <form class="busca-form" method="POST" action="">
                <input type="text" name="termo_busca" placeholder="Nº pedido, cliente ou endereço" value="<?= isset($_POST['termo_busca']) ? htmlspecialchars($_POST['termo_busca']) : '' ?>">
                <button type="submit" name="buscar_vendas">Buscar</button>
            </form>
        </div>

        <?php if(!empty($vendas_busca)): ?>
            <div class="card">
                <h2>Resultados da Busca</h2>
                <table class="tabela-vendas">
                    <thead>
                        <tr>
                            <th>Nº Pedido</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Local Entrega</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($vendas_busca as $venda): ?>
                            <tr>
                                <td><?= htmlspecialchars($venda['Numero_pedido']) ?></td>
                                <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($venda['Data_pedido'])) ?></td>
                                <td><?= htmlspecialchars($venda['Local_entrega']) ?></td>
                                <td>R$ <?= number_format($venda['Preco'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="dashboard">
            <div class="card">
                <h2>Nova Venda</h2>
                <form class="venda-form" method="POST" action="">
                    <div class="form-group">
                        <label for="id_cliente">Cliente:</label>
                        <select name="id_cliente" id="id_cliente" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id_cliente'] ?>">
                                    <?= htmlspecialchars($cliente['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                                
                    <div class="form-group">
                        <label for="id_produto">Produto:</label>
                        <select name="id_produto" id="id_produto" required onchange="atualizarPreco()">
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= $produto['id_produto'] ?>" 
                                        data-preco="<?= $produto['Preco'] ?>"
                                        data-estoque="<?= $produto['Quantidade'] ?>">
                                    <?= htmlspecialchars($produto['Tipo_mel']) ?> 
                                    (R$ <?= number_format($produto['Preco'], 2, ',', '.') ?>)
                                    - Estoque: <?= $produto['Quantidade'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantidade">Quantidade:</label>
                        <input type="number" name="quantidade" id="quantidade" min="1" required onchange="calcularTotal()">
                    </div>

                    <div class="form-group">
                        <label for="preco_unitario">Preço Unitário (R$):</label>
                        <input type="number" name="preco_unitario" id="preco_unitario" step="0.01" min="0" readonly>
                    </div>

                    <div class="resumo-venda">
                        <p>Subtotal: <span id="subtotal">R$ 0,00</span></p>
                        <p class="total">Total: <span id="total">R$ 0,00</span></p>
                    </div>

                    <button type="submit" name="registrar_venda" class="btn-venda">Registrar Venda</button>
                </form>
            </div>

            <div class="card">
                <h2>Resumo do Dia</h2>
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number"><?= count($produtos) ?></div>
                        <div class="stat-label">Produtos em Estoque</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count($clientes) ?></div>
                        <div class="stat-label">Clientes Cadastrados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $total_vendas_hoje ?></div>
                        <div class="stat-label">Vendas Hoje</div>
                    </div>
                </div>
                
                <h3 style="margin-top: 30px;">Últimas Vendas de Hoje</h3>
                <div class="lista-vendas">
                    <?php if(!empty($vendas_hoje)): ?>
                        <?php foreach($vendas_hoje as $venda): ?>
                            <div class="venda-item">
                                <div class="venda-info">
                                    <div class="venda-numero"><?= htmlspecialchars($venda['Numero_pedido']) ?></div>
                                    <div class="venda-cliente"><?= htmlspecialchars($venda['cliente_nome']) ?></div>
                                </div>
                                <div class="venda-valor">R$ <?= number_format($venda['Preco'], 2, ',', '.') ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--cinza-escuro); margin-top: 20px;">
                            Nenhuma venda registrada hoje.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function atualizarPreco() {
            const produtoSelect = document.getElementById('id_produto');
            const precoInput = document.getElementById('preco_unitario');
            const quantidadeInput = document.getElementById('quantidade');
            const selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
            
            if (selectedOption.value !== '') {
                const preco = selectedOption.getAttribute('data-preco');
                const estoque = selectedOption.getAttribute('data-estoque');
                
                precoInput.value = preco;
                quantidadeInput.max = estoque;
                
                if (quantidadeInput.value > estoque) {
                    quantidadeInput.value = estoque;
                }
                
                calcularTotal();
            } else {
                precoInput.value = '';
                quantidadeInput.value = '';
                calcularTotal();
            }
        }

        function calcularTotal() {
            const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            const precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
            const subtotal = quantidade * precoUnitario;
            
            document.getElementById('subtotal').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
            document.getElementById('total').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
        }

        // Inicializar cálculos
        document.getElementById('quantidade').addEventListener('input', calcularTotal);
        document.getElementById('id_produto').addEventListener('change', atualizarPreco);
        
        // Calcular total inicial
        calcularTotal();
    </script>
</body>
</html>