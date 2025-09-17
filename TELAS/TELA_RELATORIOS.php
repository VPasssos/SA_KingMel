<?php
session_start();
include('../conexao.php');

// VERIFICA SE O USUARIO TEM PERMISSÃO
if($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2){
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";        
    exit();
}

// ================= FUNÇÕES DE RELATÓRIOS =================

// Relatório de Vendas por Período
function getVendasPorPeriodo($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT 
                cc.id_compra_carrinho AS Numero_pedido,
                cc.data_compra AS Data_pedido,
                cc.preco_total AS Preco,
                u.nome AS cliente_nome,
                (SELECT COUNT(*) FROM compra_carrinho_produto WHERE id_compra_carrinho = cc.id_compra_carrinho) AS total_itens
            FROM compra_carrinho cc
            INNER JOIN usuario u ON cc.id_usuario = u.id_usuario
            WHERE cc.data_compra BETWEEN :data_inicio AND :data_fim
            ORDER BY cc.data_compra DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Produtos Mais Vendidos
function getProdutosMaisVendidos($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT 
                p.Tipo_mel,
                SUM(ccp.qtd_produto) AS total_vendido,
                SUM(ccp.preco_unitario * ccp.qtd_produto) AS valor_total,
                a.Nome_apiario
            FROM compra_carrinho_produto ccp
            INNER JOIN produto p ON ccp.id_produto = p.id_produto
            INNER JOIN compra_carrinho cc ON cc.id_compra_carrinho = ccp.id_compra_carrinho
            LEFT JOIN apiario_produto ap ON p.id_produto = ap.id_produto
            LEFT JOIN apiario a ON ap.id_apiario = a.id_apiario
            WHERE cc.data_compra BETWEEN :data_inicio AND :data_fim
            GROUP BY p.id_produto, p.Tipo_mel, a.Nome_apiario
            ORDER BY total_vendido DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Clientes que Mais Compram
function getClientesMaisCompram($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT 
                u.nome AS cliente,
                u.email AS email,
                COUNT(cc.id_compra_carrinho) AS total_pedidos,
                SUM(cc.preco_total) AS valor_total_gasto
            FROM usuario u
            INNER JOIN compra_carrinho cc ON u.id_usuario = cc.id_usuario
            WHERE cc.data_compra BETWEEN :data_inicio AND :data_fim
            GROUP BY u.id_usuario, u.nome, u.email
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
                (SELECT COUNT(*) FROM compra_carrinho WHERE data_compra BETWEEN :data_inicio AND :data_fim) AS total_pedidos,
                (SELECT COALESCE(SUM(preco_total),0) FROM compra_carrinho WHERE data_compra BETWEEN :data_inicio AND :data_fim) AS valor_total_vendas,
                (SELECT COALESCE(SUM(preco_total),0) / NULLIF(COUNT(*),0) 
                 FROM compra_carrinho WHERE data_compra BETWEEN :data_inicio AND :data_fim) AS valor_medio_pedido,
                (SELECT COUNT(DISTINCT id_usuario) FROM compra_carrinho WHERE data_compra BETWEEN :data_inicio AND :data_fim) AS clientes_ativos,
                (SELECT COUNT(*) FROM usuario) AS total_clientes,
                (SELECT COUNT(*) FROM produto WHERE Quantidade > 0) AS produtos_estoque";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Vendas Mensais
function getVendasMensais($pdo, $ano) {
    $sql = "SELECT MONTH(data_compra) AS mes, SUM(preco_total) AS valor_total
            FROM compra_carrinho
            WHERE YEAR(data_compra) = :ano
            GROUP BY MONTH(data_compra)
            ORDER BY mes";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ano', $ano);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ================= PROCESSAR FILTROS =================
$data_inicio_input = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : '2000-01-01';
$data_fim_input    = isset($_POST['data_fim']) ? $_POST['data_fim'] : date('Y-m-d');
$ano_grafico       = isset($_POST['ano_grafico']) ? $_POST['ano_grafico'] : date('Y');
$trimestre_input   = isset($_POST['trimestre']) ? $_POST['trimestre'] : '';

// Se trimestre selecionado, sobrescreve datas
if ($trimestre_input != '') {
    $ano_atual = $ano_grafico; // usa o ano escolhido
    switch ($trimestre_input) {
        case '1':
            $data_inicio_input = "$ano_atual-01-01";
            $data_fim_input    = "$ano_atual-03-31";
            break;
        case '2':
            $data_inicio_input = "$ano_atual-04-01";
            $data_fim_input    = "$ano_atual-06-30";
            break;
        case '3':
            $data_inicio_input = "$ano_atual-07-01";
            $data_fim_input    = "$ano_atual-09-30";
            break;
        case '4':
            $data_inicio_input = "$ano_atual-10-01";
            $data_fim_input    = "$ano_atual-12-31";
            break;
    }
}

// Intervalos datetime completos
$data_inicio_sql = $data_inicio_input . ' 00:00:00';
$data_fim_sql    = $data_fim_input    . ' 23:59:59';

$vendas_periodo = getVendasPorPeriodo($pdo, $data_inicio_sql, $data_fim_sql);
$produtos_mais_vendidos = getProdutosMaisVendidos($pdo, $data_inicio_sql, $data_fim_sql);
$clientes_mais_compram = getClientesMaisCompram($pdo, $data_inicio_sql, $data_fim_sql);
$estatisticas = getEstatisticasGerais($pdo, $data_inicio_sql, $data_fim_sql);
$vendas_mensais = getVendasMensais($pdo, $ano_grafico);

// Preparar gráfico
$meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dec'];
$dados_grafico = array_fill(0, 12, 0);
foreach($vendas_mensais as $venda){
    if($venda['mes']>=1 && $venda['mes']<=12){
        $dados_grafico[$venda['mes']-1] = (float)$venda['valor_total'];
    }
}

// Preparar dados para gráficos de pizza
// Dados para gráfico de produtos mais vendidos
$produtos_labels = [];
$produtos_data = [];
$produtos_cores = [];

foreach ($produtos_mais_vendidos as $produto) {
    $produtos_labels[] = $produto['Tipo_mel'];
    $produtos_data[] = (float)$produto['total_vendido'];
    $produtos_cores[] = sprintf('rgba(%d, %d, %d, 0.7)', rand(50, 200), rand(50, 200), rand(50, 200));
}

// Dados para gráfico de clientes que mais compram
$clientes_labels = [];
$clientes_data = [];
$clientes_cores = [];

foreach ($clientes_mais_compram as $cliente) {
    $clientes_labels[] = $cliente['cliente'];
    $clientes_data[] = (float)$cliente['valor_total_gasto'];
    $clientes_cores[] = sprintf('rgba(%d, %d, %d, 0.7)', rand(50, 200), rand(50, 200), rand(50, 200));
}

// Texto extra para exibir trimestre nos títulos
$texto_periodo = '';
if ($trimestre_input != '') {
    $texto_periodo = " - {$trimestre_input}º Trimestre de $ano_grafico";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>RELATÓRIOS - KING MEL</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_RELATORIOS.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio_input) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="data_fim">Data Fim:</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim_input) ?>" required>
            </div>

            <!-- NOVO: Filtro Trimestral -->
            <div class="form-group">
                <label for="trimestre">Trimestre:</label>
                <select name="trimestre">
                    <option value="">-- Selecionar --</option>
                    <option value="1" <?= $trimestre_input=='1'?'selected':'' ?>>1º Trimestre (Jan-Mar)</option>
                    <option value="2" <?= $trimestre_input=='2'?'selected':'' ?>>2º Trimestre (Abr-Jun)</option>
                    <option value="3" <?= $trimestre_input=='3'?'selected':'' ?>>3º Trimestre (Jul-Set)</option>
                    <option value="4" <?= $trimestre_input=='4'?'selected':'' ?>>4º Trimestre (Out-Dez)</option>
                </select>
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
        </form>
    </div>

    <!-- Estatísticas -->
    <div class="dashboard">
        <div class="stat-card"><div class="stat-label">Total de Pedidos</div><div class="stat-number"><?= $estatisticas['total_pedidos'] ?></div></div>
        <div class="stat-card"><div class="stat-label">Valor Total</div><div class="stat-number">R$ <?= number_format($estatisticas['valor_total_vendas'],2,',','.') ?></div></div>
        <div class="stat-card"><div class="stat-label">Ticket Médio</div><div class="stat-number">R$ <?= number_format($estatisticas['valor_medio_pedido'],2,',','.') ?></div></div>
        <div class="stat-card"><div class="stat-label">Clientes Ativos</div><div class="stat-number"><?= $estatisticas['clientes_ativos'] ?></div></div>
    </div>

    <!-- Gráfico de Vendas Mensais -->
    <div class="grafico-container">
        <h2>Vendas Mensais - <?= $ano_grafico ?><?= $texto_periodo ?></h2>
        <canvas id="graficoVendas"></canvas>
    </div>

    <!-- Gráfico de Produtos Mais Vendidos -->
    <div class="grafico-container">
        <h2>Produtos Mais Vendidos<?= $texto_periodo ?></h2>
        <?php if (!empty($produtos_mais_vendidos)): ?>
        <div class="chart-container" style="position: relative; height:400px; width:100%">
            <canvas id="graficoProdutos"></canvas>
        </div>
        <?php else: ?><p>Nenhum produto vendido.</p><?php endif; ?>
    </div>

    <!-- Gráfico de Clientes que Mais Compram -->
    <div class="grafico-container">
        <h2>Clientes que Mais Compram<?= $texto_periodo ?></h2>
        <?php if (!empty($clientes_mais_compram)): ?>
        <div class="chart-container" style="position: relative; height:400px; width:100%">
            <canvas id="graficoClientes"></canvas>
        </div>
        <?php else: ?><p>Nenhum cliente encontrado.</p><?php endif; ?>
    </div>

    <!-- Vendas no Período (mantido como tabela para detalhes) -->
    <div class="relatorio-section">
        <h2>Detalhes das Vendas<?= $texto_periodo ?></h2>
        <?php if (!empty($vendas_periodo)): ?>
        <table class="table">
            <thead><tr><th>Nº Pedido</th><th>Data</th><th>Cliente</th><th>Valor</th><th>Itens</th></tr></thead>
            <tbody>
                <?php foreach ($vendas_periodo as $v): ?>
                <tr>
                    <td><?= $v['Numero_pedido'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['Data_pedido'])) ?></td>
                    <td><?= htmlspecialchars($v['cliente_nome']) ?></td>
                    <td>R$ <?= number_format($v['Preco'],2,',','.') ?></td>
                    <td><?= $v['total_itens'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p>Nenhuma venda encontrada.</p><?php endif; ?>
    </div>
</main>

<script>
// Gráfico de Vendas Mensais (barras)
const ctxVendas = document.getElementById('graficoVendas').getContext('2d');
new Chart(ctxVendas,{
    type:'bar',
    data:{
        labels:<?= json_encode($meses) ?>,
        datasets:[{
            label:'Vendas (R$)',
            data:<?= json_encode($dados_grafico) ?>,
            backgroundColor:'rgba(224,165,0,0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Vendas Mensais'
            }
        }
    }
});

// Gráfico de Produtos Mais Vendidos (pizza)
<?php if (!empty($produtos_mais_vendidos)): ?>
const ctxProdutos = document.getElementById('graficoProdutos').getContext('2d');
new Chart(ctxProdutos, {
    type: 'pie',
    data: {
        labels: <?= json_encode($produtos_labels) ?>,
        datasets: [{
            data: <?= json_encode($produtos_data) ?>,
            backgroundColor: <?= json_encode($produtos_cores) ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
            },
            title: {
                display: true,
                text: 'Produtos Mais Vendidos (Quantidade)'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.raw || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} unidades (${percentage}%)`;
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Gráfico de Clientes que Mais Compram (pizza)
<?php if (!empty($clientes_mais_compram)): ?>
const ctxClientes = document.getElementById('graficoClientes').getContext('2d');
new Chart(ctxClientes, {
    type: 'pie',
    data: {
        labels: <?= json_encode($clientes_labels) ?>,
        datasets: [{
            data: <?= json_encode($clientes_data) ?>,
            backgroundColor: <?= json_encode($clientes_cores) ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
            },
            title: {
                display: true,
                text: 'Clientes que Mais Compram (Valor Gasto)'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.raw || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = Math.round((value / total) * 100);
                        return `${label}: R$ ${value.toFixed(2)} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>