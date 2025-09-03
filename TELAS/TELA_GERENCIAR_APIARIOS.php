<?php
session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// VERIFICA SE O USUARIO TEM PERMISSÃO
if($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3){
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";        
    exit();
}

// Função para buscar apiários
function buscarApiarios($pdo, $termo = null) {
    $sql = "SELECT * FROM apiario";
    
    if ($termo) {
        $sql .= " WHERE Nome_apiario LIKE :termo OR CNPJ LIKE :termo OR Endereco LIKE :termo";
    }

    $sql .= " ORDER BY Nome_apiario ASC";

    $stmt = $pdo->prepare($sql);

    if ($termo) {
        $stmt->bindValue(':termo', '%' . $termo . '%');
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para excluir um apiário
if (isset($_GET['excluir'])) {
    $id_apiario = $_GET['excluir'];

    $sql = "DELETE FROM apiario WHERE id_apiario = :id_apiario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_apiario', $id_apiario, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Apiário excluído com sucesso!'); window.location.href='TELA_GERENCIAR_APIARIOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir apiário!'); window.location.href='TELA_GERENCIAR_APIARIOS.php';</script>";
    }
    exit();
}

// Função para adicionar um novo apiário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_apiario'])) {
    $Nome_apiario = $_POST['Nome_apiario'];
    $CNPJ = $_POST['CNPJ'];
    $Quantidade = $_POST['Quantidade'];
    $Data_inicio = $_POST['Data_inicio'];
    $Endereco = $_POST['Endereco'];

    $sql = "INSERT INTO apiario (Nome_apiario, CNPJ, Quantidade, Data_inicio, Endereco) 
            VALUES (:Nome_apiario, :CNPJ, :Quantidade, :Data_inicio, :Endereco)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':Nome_apiario', $Nome_apiario);
    $stmt->bindParam(':CNPJ', $CNPJ);
    $stmt->bindParam(':Quantidade', $Quantidade);
    $stmt->bindParam(':Data_inicio', $Data_inicio);
    $stmt->bindParam(':Endereco', $Endereco);
    
    if ($stmt->execute()) {
        echo "<script>alert('Apiário adicionado com sucesso!'); window.location.href='TELA_GERENCIAR_APIARIOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao adicionar apiário!'); window.location.href='TELA_GERENCIAR_APIARIOS.php';</script>";
    }
    exit();
}

// Função para alterar um apiário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_apiario'])) {
    $id_apiario = $_POST['id_apiario'];
    $Nome_apiario = $_POST['Nome_apiario'];
    $CNPJ = $_POST['CNPJ'];
    $Quantidade = $_POST['Quantidade'];
    $Data_inicio = $_POST['Data_inicio'];
    $Endereco = $_POST['Endereco'];

    $sql = "UPDATE apiario SET Nome_apiario = :Nome_apiario, CNPJ = :CNPJ, 
            Quantidade = :Quantidade, Data_inicio = :Data_inicio, Endereco = :Endereco 
            WHERE id_apiario = :id_apiario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_apiario', $id_apiario);
    $stmt->bindParam(':Nome_apiario', $Nome_apiario);
    $stmt->bindParam(':CNPJ', $CNPJ);
    $stmt->bindParam(':Quantidade', $Quantidade);
    $stmt->bindParam(':Data_inicio', $Data_inicio);
    $stmt->bindParam(':Endereco', $Endereco);
    
    if ($stmt->execute()) {
        echo "<script>alert('Apiário alterado com sucesso!'); window.location.href='TELA_GERENCIAR_APIARIOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao alterar apiário!'); window.location.href='TELA_GERENCIAR_APIARIOS.php';</script>";
    }
    exit();
}

// Verifica se há um termo de busca
$apiarios = isset($_POST['busca']) ? buscarApiarios($pdo, $_POST['busca']) : buscarApiarios($pdo);

// Buscar apiário para edição se houver ID na URL
$apiario_edicao = null;
if (isset($_GET['editar'])) {
    $id_apiario = $_GET['editar'];
    $sql = "SELECT * FROM apiario WHERE id_apiario = :id_apiario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_apiario', $id_apiario, PDO::PARAM_INT);
    $stmt->execute();
    $apiario_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERENCIAR APIÁRIOS</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_APIARIOS.css">
    <script src="../JS/mascaras.js"></script>

</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR APIÁRIOS</h1>
        
        <div class="ops_api">
            <button id="btnAdicionar" onclick="abrirModal('modalAdicionar')">Adicionar</button>
            <form action="TELA_GERENCIAR_APIARIOS.php" method="POST">
                <input type="text" name="busca" id="busca" placeholder="Pesquisar apiário">
                <button type="submit">Pesquisar</button>
            </form>
        </div>

        <div class="tabela_api">
            <?php if (!empty($apiarios)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome do Apiário</th>
                            <th>CNPJ</th>
                            <th>Quantidade</th>
                            <th>Data de Início</th>
                            <th>Endereço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apiarios as $apiario): ?>
                            <tr>
                                <td><?= htmlspecialchars($apiario['id_apiario']) ?></td>
                                <td><?= htmlspecialchars($apiario['Nome_apiario']) ?></td>
                                <td><?= htmlspecialchars($apiario['CNPJ']) ?></td>
                                <td><?= htmlspecialchars($apiario['Quantidade']) ?></td>
                                <td><?= htmlspecialchars($apiario['Data_inicio']) ?></td>
                                <td><?= htmlspecialchars($apiario['Endereco']) ?></td>
                                <td>
                                    <a href="TELA_GERENCIAR_APIARIOS.php?editar=<?= htmlspecialchars($apiario['id_apiario']) ?>">Alterar</a>
                                    <a href="TELA_GERENCIAR_APIARIOS.php?excluir=<?= htmlspecialchars($apiario['id_apiario']) ?>" 
                                       class="excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir este apiário?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum apiário encontrado</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal para Adicionar Apiário -->
    <div id="modalAdicionar" class="modal">
        <div class="modal-content">
            <h2>Adicionar Apiário</h2>
            <form method="POST" action="TELA_GERENCIAR_APIARIOS.php">
                <label for="Nome_apiario">Nome do Apiário:</label>
                <input type="text" name="Nome_apiario" required onkeypress ="mascara(this, nomeM)">

                <label for="CNPJ">CNPJ:</label>
                <input type="text" name="CNPJ" placeholder="00.000.000/0000-00" required onkeypress ="mascara(this, cnpjM)">

                <label for="Quantidade">Quantidade de Colmeias:</label>
                <input type="number" name="Quantidade" min="0" required onkeypress ="mascara(this, qtdeM)">

                <label for="Data_inicio">Data de Início:</label>
                <input type="date" name="Data_inicio" required>

                <label for="Endereco">Endereço:</label>
                <textarea name="Endereco"></textarea>

                <button type="submit" name="adicionar_apiario" class="btn_acao">Adicionar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalAdicionar')">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para Alterar Apiário -->
    <?php if ($apiario_edicao): ?>
    <div id="modalAlterar" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Alterar Apiário</h2>
            <form method="POST" action="TELA_GERENCIAR_APIARIOS.php">
                <input type="hidden" name="id_apiario" value="<?= $apiario_edicao['id_apiario'] ?>">
                
                <label for="Nome_apiario_editar">Nome do Apiário:</label>
                <input type="text" name="Nome_apiario" id="Nome_apiario_editar" value="<?= htmlspecialchars($apiario_edicao['Nome_apiario']) ?>" required onkeypress ="mascara(this, nomeM)">

                <label for="CNPJ_editar">CNPJ:</label>
                <input type="text" name="CNPJ" id="CNPJ_editar" value="<?= htmlspecialchars($apiario_edicao['CNPJ']) ?>" required>

                <label for="Quantidade_editar">Quantidade de Colmeias:</label>
                <input type="number" name="Quantidade" id="Quantidade_editar" min="0" value="<?= htmlspecialchars($apiario_edicao['Quantidade']) ?>" required>

                <label for="Data_inicio_editar">Data de Início:</label>
                <input type="date" name="Data_inicio" id="Data_inicio_editar" value="<?= htmlspecialchars($apiario_edicao['Data_inicio']) ?>" required>

                <label for="Endereco_editar">Endereço:</label>
                <textarea name="Endereco" id="Endereco_editar"><?= htmlspecialchars($apiario_edicao['Endereco']) ?></textarea>

                <button type="submit" name="alterar_apiario" class="btn_acao">Alterar</button>
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
            window.location.href = 'TELA_GERENCIAR_APIARIOS.php';
        }

        // Fechar modal clicando fora dele
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                // Redirecionar para a mesma página sem parâmetros de edição
                window.location.href = 'TELA_GERENCIAR_APIARIOS.php';
            }
        }

        // Se houver parâmetro de edição na URL, abrir o modal de alteração
        <?php if ($apiario_edicao): ?>
            document.addEventListener('DOMContentLoaded', function() {
                abrirModal('modalAlterar');
            });
        <?php endif; ?>

        // Máscara para CNPJ
        document.addEventListener('DOMContentLoaded', function() {
            const cnpjInputs = document.querySelectorAll('input[name="CNPJ"]');
            cnpjInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 14) value = value.slice(0, 14);
                    
                    if (value.length > 12) {
                        value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
                    } else if (value.length > 8) {
                        value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})/, '$1.$2.$3/$4');
                    } else if (value.length > 5) {
                        value = value.replace(/^(\d{2})(\d{3})(\d{3})/, '$1.$2.$3');
                    } else if (value.length > 2) {
                        value = value.replace(/^(\d{2})(\d{3})/, '$1.$2');
                    }
                    e.target.value = value;
                });
            });
        });
    </script>
</body>
</html>