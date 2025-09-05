<?php
session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// VERIFICA SE O USUARIO TEM PERMISSÃO
if($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2){
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";        
    exit();
}

// Funções para buscar dados dos relatórios

// Relatório de Vendas por Período (inclui compras da loja)
function getVendasPorPeriodo($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT p.Numero_pedido AS Numero_pedido, p.Data_pedido, p.Preco, c.Nome AS cliente_nome,
                   COUNT(ip.id_item) AS total_itens
            FROM pedido p
            INNER JOIN cliente c ON p.Id_cliente = c.id_cliente
            LEFT JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
            WHERE p.Data_pedido BETWEEN :data_inicio AND :data_fim
            GROUP BY p.id_pedido

            UNION ALL

            SELECT CONCAT('C-', cc.id_compra_carrinho) AS Numero_pedido, cc.Data_compra AS Data_pedido, cc.preco_total AS Preco,
                   u.nome AS cliente_nome,
                   (SELECT COUNT(*) FROM compra_carrinho_produto WHERE id_compra_carrinho = cc.id_compra_carrinho) AS total_itens
            FROM compra_carrinho cc
            INNER JOIN usuario u ON cc.id_usuario = u.id_usuario
            WHERE cc.Data_compra BETWEEN :data_inicio AND :data_fim

            ORDER BY Data_pedido DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Relatório de Produtos Mais Vendidos
function getProdutosMaisVendidos($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT p.Tipo_mel, SUM(ip.qtd_produto) AS total_vendido,
                   SUM(ip.qtd_produto * ip.preco_unitario) AS valor_total,
                   a.Nome_apiario
            FROM item_pedido ip
            INNER JOIN produto p ON ip.id_produto = p.id_produto
            INNER JOIN pedido ped ON ip.id_pedido = ped.id_pedido
            LEFT JOIN apiario_produto ap ON p.id_produto = ap.id_produto
            LEFT JOIN apiario a ON ap.id_apiario = a.id_apiario
            WHERE ped.Data_pedido BETWEEN :data_inicio AND :data_fim
            GROUP BY p.id_produto
            ORDER BY total_vendido DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Relatório de Clientes que Mais Compram (pedidos + loja)
function getClientesMaisCompram($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT cliente, email, SUM(valor_total) AS valor_total_gasto, SUM(total_pedidos) AS total_pedidos
            FROM (
                -- Clientes do sistema de pedidos
                SELECT c.Nome AS cliente, c.Email AS email, COUNT(p.id_pedido) AS total_pedidos, SUM(p.Preco) AS valor_total
                FROM cliente c
                INNER JOIN pedido p ON c.id_cliente = p.Id_cliente
                WHERE p.Data_pedido BETWEEN :data_inicio AND :data_fim
                GROUP BY c.id_cliente, c.Nome, c.Email

                UNION ALL

                -- Clientes da loja (usuários)
                SELECT u.nome AS cliente, u.email AS email, COUNT(cc.id_compra_carrinho) AS total_pedidos, SUM(cc.preco_total) AS valor_total
                FROM usuario u
                INNER JOIN compra_carrinho cc ON u.id_usuario = cc.id_usuario
                WHERE cc.Data_compra BETWEEN :data_inicio AND :data_fim
                GROUP BY u.id_usuario, u.nome, u.email
            ) AS clientes
            GROUP BY cliente, email
            ORDER BY valor_total_gasto DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Estatísticas Gerais
function getEstatisticasGerais($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT 
                ((SELECT COUNT(*) FROM pedido WHERE Data_pedido BETWEEN :data_inicio AND :data_fim) +
                 (SELECT COUNT(*) FROM compra_carrinho WHERE Data_compra BETWEEN :data_inicio AND :data_fim)) AS total_pedidos,
                 
                ((SELECT COALESCE(SUM(Preco),0) FROM pedido WHERE Data_pedido BETWEEN :data_inicio AND :data_fim) +
                 (SELECT COALESCE(SUM(preco_total),0) FROM compra_carrinho WHERE Data_compra BETWEEN :data_inicio AND :data_fim)) AS valor_total_vendas,
                 
                ((SELECT COALESCE(SUM(Preco),0) FROM pedido WHERE Data_pedido BETWEEN :data_inicio AND :data_fim) +
                 (SELECT COALESCE(SUM(preco_total),0) FROM compra_carrinho WHERE Data_compra BETWEEN :data_inicio AND :data_fim)) /
                NULLIF(((SELECT COUNT(*) FROM pedido WHERE Data_pedido BETWEEN :data_inicio AND :data_fim) +
                       (SELECT COUNT(*) FROM compra_carrinho WHERE Data_compra BETWEEN :data_inicio AND :data_fim)),0) AS valor_medio_pedido,
                       
                ((SELECT COUNT(DISTINCT Id_cliente) FROM pedido WHERE Data_pedido BETWEEN :data_inicio AND :data_fim) +
                 (SELECT COUNT(DISTINCT id_usuario) FROM compra_carrinho WHERE Data_compra BETWEEN :data_inicio AND :data_fim)) AS clientes_ativos,
                 
                (SELECT COUNT(*) FROM cliente) AS total_clientes,
                (SELECT COUNT(*) FROM produto WHERE Quantidade > 0) AS produtos_estoque
            ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Vendas Mensais
function getVendasMensais($pdo, $ano) {
    $sql = "SELECT mes, SUM(valor_total) AS valor_total
            FROM (
                SELECT MONTH(Data_pedido) AS mes, SUM(Preco) AS valor_total
                FROM pedido
                WHERE YEAR(Data_pedido) = :ano
                GROUP BY MONTH(Data_pedido)

                UNION ALL

                SELECT MONTH(Data_compra) AS mes, SUM(preco_total) AS valor_total
                FROM compra_carrinho
                WHERE YEAR(Data_compra) = :ano
                GROUP BY MONTH(Data_compra)
            ) AS vendas
            GROUP BY mes
            ORDER BY mes";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ano', $ano);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Processar filtros
$data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-01');
$data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : date('Y-m-d');
$ano_grafico = isset($_POST['ano_grafico']) ? $_POST['ano_grafico'] : date('Y');

// Buscar dados para os relatórios
$vendas_periodo = getVendasPorPeriodo($pdo, $data_inicio, $data_fim);
$produtos_mais_vendidos = getProdutosMaisVendidos($pdo, $data_inicio, $data_fim);
$clientes_mais_compram = getClientesMaisCompram($pdo, $data_inicio, $data_fim);
$estatisticas = getEstatisticasGerais($pdo, $data_inicio, $data_fim);
$vendas_mensais = getVendasMensais($pdo, $ano_grafico);

// Preparar dados para o gráfico
$meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dec'];
$dados_grafico = array_fill(0, 12, 0);
foreach($vendas_mensais as $venda){
    if($venda['mes']>=1 && $venda['mes']<=12){
        $dados_grafico[$venda['mes']-1] = (float)$venda['valor_total'];
    }
}

// Gerar CSV
if(isset($_POST['gerar_csv'])){
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_vendas_'.date('Y-m-d').'.csv');
    
    $output = fopen('php://output','w');
    fputcsv($output,['Relatório de Vendas - King Mel'], ';');
    fputcsv($output,['Período: '.$data_inicio.' até '.$data_fim], ';');
    fputcsv($output,['']);
    fputcsv($output,['Nº Pedido','Data','Cliente','Valor Total','Itens'], ';');
    
    foreach($vendas_periodo as $venda){
        fputcsv($output, [
            $venda['Numero_pedido'],
            $venda['Data_pedido'],
            $venda['cliente_nome'],
            'R$ '.number_format($venda['Preco'],2,',','.'), 
            $venda['total_itens']
        ], ';');
    }
    
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RELATÓRIOS - KING MEL</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_RELATORIOS.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../JS/mascaras.js"></script>
</head>
<body>
<?php include("MENU.php"); ?>

<main>
    <h1>RELATÓRIOS - KING MEL</h1>
    
    <!-- Filtros -->
    <div class="filtros">
        <h2>Filtrar Relatórios</h2>
        <form method="POST" class="form-filtros">
            <div class="form-group">
                <label for="data_inicio">Data Início:</label>
                <input type="date" name="data_inicio" value="<?= $data_inicio ?>" required>
            </div>
            
            <div class="form-group">
                <label for="data_fim">Data Fim:</label>
                <input type="date" name="data_fim" value="<?= $data_fim ?>" required>
            </div>
            
            <div class="form-group">
                <label for="ano_grafico">Ano para Gráfico:</label>
                <select name="ano_grafico">
                    <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                        <option value="<?= $i ?>" <?= $i == $ano_grafico ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-filtrar">Aplicar Filtros</button>
            </div>
            
            <div class="form-group">
                <button type="submit" name="gerar_csv" class="btn-csv">Exportar CSV</button>
            </div>
        </form>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="dashboard">
        <div class="stat-card">
            <div class="stat-label">Total de Pedidos</div>
            <div class="stat-number"><?= $estatisticas['total_pedidos'] ?? 0 ?></div>
            <div class="stat-desc">no período selecionado</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Valor Total</div>
            <div class="stat-number">R$ <?= number_format($estatisticas['valor_total_vendas'] ?? 0, 2, ',', '.') ?></div>
            <div class="stat-desc">em vendas</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ticket Médio</div>
            <div class="stat-number">R$ <?= number_format($estatisticas['valor_medio_pedido'] ?? 0, 2, ',', '.') ?></div>
            <div class="stat-desc">por pedido</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Clientes Ativos</div>
            <div class="stat-number"><?= $estatisticas['clientes_ativos'] ?? 0 ?></div>
            <div class="stat-desc">de <?= $estatisticas['total_clientes'] ?? 0 ?> total</div>
        </div>
    </div>

    <!-- Gráfico de Vendas Mensais -->
    <div class="grafico-container">
        <h2>Vendas Mensais - <?= $ano_grafico ?></h2>
        <canvas id="graficoVendas" height="100"></canvas>
    </div>

    <!-- Relatório de Vendas por Período -->
    <div class="relatorio-section">
        <h2>
            Vendas no Período
            <span style="font-size: 14px; font-weight: normal;">
                <?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?>
            </span>
        </h2>
        <?php if (!empty($vendas_periodo)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nº Pedido</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Valor Total</th>
                        <th>Itens</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendas_periodo as $venda): ?>
                        <tr>
                            <td><?= htmlspecialchars($venda['Numero_pedido']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($venda['Data_pedido'])) ?></td>
                            <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                            <td class="valor-monetario">R$ <?= number_format($venda['Preco'], 2, ',', '.') ?></td>
                            <td><?= $venda['total_itens'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="sem-dados">Nenhuma venda encontrada no período selecionado.</div>
        <?php endif; ?>
    </div>

    <!-- Produtos Mais Vendidos -->
    <div class="relatorio-section">
        <h2>Produtos Mais Vendidos</h2>
        <?php if (!empty($produtos_mais_vendidos)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Apiário</th>
                        <th>Quantidade</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos_mais_vendidos as $produto): ?>
                        <tr>
                            <td><?= htmlspecialchars($produto['Tipo_mel']) ?></td>
                            <td><?= htmlspecialchars($produto['Nome_apiario'] ?? 'Não vinculado') ?></td>
                            <td><?= $produto['total_vendido'] ?> unidades</td>
                            <td class="valor-monetario">R$ <?= number_format($produto['valor_total'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="sem-dados">Nenhum dado de produtos vendidos no período.</div>
        <?php endif; ?>
    </div>

    <!-- Clientes que Mais Compram -->
    <div class="relatorio-section">
        <h2>Clientes que Mais Compram</h2>
        <?php if (!empty($clientes_mais_compram)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Contato</th>
                        <th>Total de Pedidos</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes_mais_compram as $cliente): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente['cliente']) ?></td>
                            <td><?= htmlspecialchars($cliente['email'] ?? '-') ?></td>
                            <td><?= $cliente['total_pedidos'] ?> pedidos</td>
                            <td class="valor-monetario">R$ <?= number_format($cliente['valor_total_gasto'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="sem-dados">Nenhum dado de clientes no período.</div>
        <?php endif; ?>
    </div>
</main>

<script>
const ctx = document.getElementById('graficoVendas').getContext('2d');
const graficoVendas = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses) ?>,
        datasets: [{
            label: 'Vendas Mensais (R$)',
            data: <?= json_encode($dados_grafico) ?>,
            backgroundColor: 'rgba(224, 165, 0, 0.7)',
            borderColor: 'rgba(224, 165, 0, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

window.addEventListener('resize', function() {
    graficoVendas.resize();
});
</script>
</body>
</html>
