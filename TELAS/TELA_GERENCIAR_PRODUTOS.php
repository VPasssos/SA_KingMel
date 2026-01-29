<?php
// (OPCIONAL) DESCOMENTE PARA DEBUG TEMPORÁRIO
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// ---------- FLASH MESSAGES (sobrevivem ao redirect) ----------
function set_flash($tipo, $msg) {
    $_SESSION['flash_'.$tipo] = $msg;
}
function get_flash($tipo) {
    if (!empty($_SESSION['flash_'.$tipo])) {
        $m = $_SESSION['flash_'.$tipo];
        unset($_SESSION['flash_'.$tipo]);
        return $m;
    }
    return null;
}

// ---------- PERMISSÃO ----------
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3)) {
    set_flash('erro', 'Acesso Negado');
    header("Location: TELA_INICIAL.php");
    exit();
}

// ---------- FUNÇÃO: REDIMENSIONAR IMAGEM (suporta jpg/png/webp/gif) ----------
// Sempre retorna JPEG binário e define $tipoSaida = 'image/jpeg'
function redimensionarImagem($arquivoTmp, $largura, $altura, &$tipoSaida) {
    $info = @getimagesize($arquivoTmp);
    if (!$info) {
        throw new Exception("Arquivo de imagem inválido.");
    }
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($arquivoTmp);
            break;
        case 'image/png':
            $src = imagecreatefrompng($arquivoTmp);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                throw new Exception("WEBP não suportado no servidor.");
            }
            $src = imagecreatefromwebp($arquivoTmp);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($arquivoTmp);
            break;
        default:
            throw new Exception("Formato não suportado: $mime");
    }

    list($w, $h) = $info;

    // cria destino
    $dst = imagecreatetruecolor($largura, $altura);
    // fundo branco para evitar fundo preto ao converter PNG/GIF com transparência
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $largura, $altura, $white);

    // copia com redimensionamento
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $largura, $altura, $w, $h);

    // buffer
    ob_start();
    // grava sempre em JPEG com qualidade boa
    imagejpeg($dst, null, 85);
    $bin = ob_get_clean();

    imagedestroy($dst);
    imagedestroy($src);

    // define tipo de saída coerente com o binário retornado
    $tipoSaida = 'image/jpeg';
    return $bin;
}

// ---------- BUSCAS ----------
function buscarProdutos($pdo, $termo = null) {
    // Usa GROUP_CONCAT para evitar erro com ONLY_FULL_GROUP_BY
    $sql = "
        SELECT 
            p.*,
            GROUP_CONCAT(DISTINCT a.Nome_apiario ORDER BY a.Nome_apiario SEPARATOR ', ') AS Apiarios
        FROM produto p
        LEFT JOIN apiario_produto ap ON p.id_produto = ap.id_produto
        LEFT JOIN apiario a ON ap.id_apiario = a.id_apiario
        WHERE 1=1
    ";

    if ($termo) {
        $sql .= " AND (p.Tipo_mel LIKE :termo OR p.Data_embalado LIKE :termo OR a.Nome_apiario LIKE :termo)";
    }

    $sql .= " GROUP BY p.id_produto
              ORDER BY p.Tipo_mel ASC";

    $stmt = $pdo->prepare($sql);

    if ($termo) {
        $stmt->bindValue(':termo', '%' . $termo . '%');
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarApiarios($pdo) {
    $sql = "SELECT * FROM apiario ORDER BY Nome_apiario ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $id_produto = (int)$_GET['excluir'];

    try {
        $pdo->beginTransaction();

        $sql_relacao = "DELETE FROM apiario_produto WHERE id_produto = :id_produto";
        $stmt_relacao = $pdo->prepare($sql_relacao);
        $stmt_relacao->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt_relacao->execute();

        $sql = "DELETE FROM produto WHERE id_produto = :id_produto";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $pdo->commit();
            set_flash('sucesso', 'Produto excluído com sucesso!');
        } else {
            throw new Exception("Erro ao excluir produto");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('erro', 'Erro ao excluir produto!');
    }

    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// ---------- ADICIONAR ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_produto'])) {
    $Tipo_mel      = trim($_POST['Tipo_mel']);
    $Data_embalado = $_POST['Data_embalado'];
    $Peso          = $_POST['Peso'];
    $Preco         = $_POST['Preco'];
    $Quantidade    = $_POST['Quantidade'];
    $id_apiario    = !empty($_POST['id_apiario']) ? (int)$_POST['id_apiario'] : null;

    try {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Imagem é obrigatória e precisa ser válida.");
        }

        // Redimensiona e garante saída coerente (sempre JPEG)
        $tipoFotoSaida = 'image/jpeg';
        $fotoBin = redimensionarImagem($_FILES['foto']['tmp_name'], 300, 400, $tipoFotoSaida);

        $nomeFoto = $_FILES['foto']['name']; // original name (opcional)
        // $tipoFotoSaida é 'image/jpeg'

        $pdo->beginTransaction();

        $sql = "INSERT INTO produto (Tipo_mel, Data_embalado, Peso, Preco, Quantidade, nome_foto, tipo_foto, foto) 
                VALUES (:Tipo_mel, :Data_embalado, :Peso, :Preco, :Quantidade, :nome_foto, :tipo_foto, :foto)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':Tipo_mel', $Tipo_mel);
        $stmt->bindParam(':Data_embalado', $Data_embalado);
        $stmt->bindParam(':Peso', $Peso);
        $stmt->bindParam(':Preco', $Preco);
        $stmt->bindParam(':Quantidade', $Quantidade);
        $stmt->bindParam(':nome_foto', $nomeFoto);
        $stmt->bindParam(':tipo_foto', $tipoFotoSaida);
        $stmt->bindParam(':foto', $fotoBin, PDO::PARAM_LOB);
        $stmt->execute();

        $id_produto = $pdo->lastInsertId();

        if (!empty($id_apiario)) {
            $sql_relacao = "INSERT INTO apiario_produto (id_apiario, id_produto) VALUES (:id_apiario, :id_produto)";
            $stmt_relacao = $pdo->prepare($sql_relacao);
            $stmt_relacao->bindParam(':id_apiario', $id_apiario);
            $stmt_relacao->bindParam(':id_produto', $id_produto);
            $stmt_relacao->execute();
        }

        $pdo->commit();
        set_flash('sucesso', 'Produto adicionado com sucesso!');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('erro', 'Erro ao adicionar produto! ' . $e->getMessage());
    }

    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// ---------- ALTERAR ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_produto'])) {
    $id_produto    = (int)$_POST['id_produto'];
    $Tipo_mel      = trim($_POST['Tipo_mel']);
    $Data_embalado = $_POST['Data_embalado'];
    $Peso          = $_POST['Peso'];
    $Preco         = $_POST['Preco'];
    $Quantidade    = $_POST['Quantidade'];
    $id_apiario    = !empty($_POST['id_apiario']) ? (int)$_POST['id_apiario'] : null;

    $temNovaImagem = isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK;

    try {
        $pdo->beginTransaction();

        if ($temNovaImagem) {
            $tipoFotoSaida = 'image/jpeg';
            $fotoBin = redimensionarImagem($_FILES['foto']['tmp_name'], 300, 400, $tipoFotoSaida);
            $nomeFoto = $_FILES['foto']['name'];

            $sql = "UPDATE produto 
                    SET Tipo_mel = :Tipo_mel, Data_embalado = :Data_embalado, 
                        Peso = :Peso, Preco = :Preco, Quantidade = :Quantidade,
                        nome_foto = :nome_foto, tipo_foto = :tipo_foto, foto = :foto
                    WHERE id_produto = :id_produto";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome_foto', $nomeFoto);
            $stmt->bindParam(':tipo_foto', $tipoFotoSaida);
            $stmt->bindParam(':foto', $fotoBin, PDO::PARAM_LOB);
        } else {
            $sql = "UPDATE produto 
                    SET Tipo_mel = :Tipo_mel, Data_embalado = :Data_embalado, 
                        Peso = :Peso, Preco = :Preco, Quantidade = :Quantidade
                    WHERE id_produto = :id_produto";
            $stmt = $pdo->prepare($sql);
        }

        $stmt->bindParam(':id_produto', $id_produto);
        $stmt->bindParam(':Tipo_mel', $Tipo_mel);
        $stmt->bindParam(':Data_embalado', $Data_embalado);
        $stmt->bindParam(':Peso', $Peso);
        $stmt->bindParam(':Preco', $Preco);
        $stmt->bindParam(':Quantidade', $Quantidade);
        $stmt->execute();

        // Atualiza relação com apiário
        $sql_delete_relacao = "DELETE FROM apiario_produto WHERE id_produto = :id_produto";
        $stmt_delete = $pdo->prepare($sql_delete_relacao);
        $stmt_delete->bindParam(':id_produto', $id_produto);
        $stmt_delete->execute();

        if (!empty($id_apiario)) {
            $sql_relacao = "INSERT INTO apiario_produto (id_apiario, id_produto) VALUES (:id_apiario, :id_produto)";
            $stmt_relacao = $pdo->prepare($sql_relacao);
            $stmt_relacao->bindParam(':id_apiario', $id_apiario);
            $stmt_relacao->bindParam(':id_produto', $id_produto);
            $stmt_relacao->execute();
        }

        $pdo->commit();
        set_flash('sucesso', 'Produto alterado com sucesso!');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('erro', 'Erro ao alterar produto! ' . $e->getMessage());
    }

    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// ---------- REPOR ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acresentar_produto'])) {
    $id_produto = (int)$_POST['id_produto'];
    $Quantidade = (float)$_POST['Quantidade'];

    try {
        $pdo->beginTransaction();

        $sql = "UPDATE produto SET Quantidade = Quantidade + :quantidade WHERE id_produto = :id_produto";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto);
        $stmt->bindParam(':quantidade', $Quantidade);
        $stmt->execute();

        $pdo->commit();
        set_flash('sucesso', 'Produto reposto com sucesso!');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('erro', 'Erro ao repor produto!');
    }

    header("Location: TELA_GERENCIAR_PRODUTOS.php");
    exit();
}

// ---------- CARREGAMENTOS DA PÁGINA ----------
$produtos = isset($_POST['busca']) ? buscarProdutos($pdo, $_POST['busca']) : buscarProdutos($pdo);
$apiarios = buscarApiarios($pdo);

// Edição
$produto_edicao = null;
$apiario_produto = null;
$imagemBase64 = null;

if (isset($_GET['editar'])) {
    $id_produto = (int)$_GET['editar'];

    $sql = "SELECT * FROM produto WHERE id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produto_edicao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto_edicao && isset($produto_edicao['foto'])) {
        $imagemBase64 = base64_encode($produto_edicao['foto']);
    }

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

// Repor
$produto_repor = null;
if (isset($_GET['repor'])) {
    $id_produto = (int)$_GET['repor'];

    $sql = "SELECT * FROM produto WHERE id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
    $produto_repor = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../ESTILOS/ESTILO_IMAGENS.css">
    <script src="../JS/mascaras.js"></script>
</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR PRODUTOS</h1>

        <?php if ($m = get_flash('sucesso')): ?>
            <div class="mensagem sucesso"><?php echo htmlspecialchars($m); ?></div>
        <?php endif; ?>
        <?php if ($m = get_flash('erro')): ?>
            <div class="mensagem erro"><?php echo htmlspecialchars($m); ?></div>
        <?php endif; ?>

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
                            <th>Apiários</th>
                            <th>Imagem do produto</th>
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
                            <td>R$ <?= number_format((float)$produto['Preco'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($produto['Quantidade']) ?></td>
                            <td><?= htmlspecialchars($produto['Apiarios'] ?? 'Não vinculado') ?></td>
                            <td>
                                <?php if (!empty($produto['foto'])): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($produto['foto']) ?>" alt="Foto do produto">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="TELA_GERENCIAR_PRODUTOS.php?repor=<?= htmlspecialchars($produto['id_produto']) ?>">Repor</a>
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

    <!-- Modal: Adicionar -->
    <div id="modalAdicionar" class="modal">
        <div class="modal-content">
            <h2>Adicionar Produto</h2>
            <form method="POST" action="TELA_GERENCIAR_PRODUTOS.php" enctype="multipart/form-data">
                <label for="Tipo_mel">Tipo de Mel:</label>
                <input type="text" name="Tipo_mel" required onkeypress="mascara(this, nomeM)">

                <label for="Data_embalado">Data Embalado:</label>
                <input type="date" name="Data_embalado" required>

                <label for="Peso">Peso (kg):</label>
                <input type="number" name="Peso" step="0.01" min="0" required>

                <label for="Preco">Preço (R$):</label>
                <input type="number" name="Preco" step="0.01" min="0" required>

                <label for="Quantidade">Quantidade:</label>
                <input type="number" name="Quantidade" min="0" required>

                <div class="imagem_produto">
                    <label for="foto">Imagem do produto:</label>
                    <input type="file" name="foto" accept="image/*" required>
                </div>

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

    <!-- Modal: Alterar -->
    <?php if ($produto_edicao): ?>
    <div id="modalAlterar" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Alterar Produto</h2>
            <form method="POST" action="TELA_GERENCIAR_PRODUTOS.php" enctype="multipart/form-data">
                <input type="hidden" name="id_produto" value="<?= $produto_edicao['id_produto'] ?>">
                
                <label for="Tipo_mel_editar">Tipo de Mel:</label>
                <input type="text" name="Tipo_mel" id="Tipo_mel_editar" value="<?= htmlspecialchars($produto_edicao['Tipo_mel']) ?>" required onkeypress="mascara(this, nomeM)">

                <label for="Data_embalado_editar">Data Embalado:</label>
                <input type="date" name="Data_embalado" id="Data_embalado_editar" value="<?= htmlspecialchars($produto_edicao['Data_embalado']) ?>" required>

                <label for="Peso_editar">Peso (kg):</label>
                <input type="number" name="Peso" id="Peso_editar" step="0.01" min="0" value="<?= htmlspecialchars($produto_edicao['Peso']) ?>" required>

                <label for="Preco_editar">Preço (R$):</label>
                <input type="number" name="Preco" id="Preco_editar" step="0.01" min="0" value="<?= htmlspecialchars($produto_edicao['Preco']) ?>" required>

                <label>Foto atual:</label>
                <?php if ($imagemBase64): ?>
                    <img src="data:image/jpeg;base64,<?= $imagemBase64 ?>" alt="Foto do produto" width="150" height="auto" />
                <?php else: ?>
                    <div>—</div>
                <?php endif; ?>

                <label for="nova_foto">Nova imagem (opcional):</label>
                <input type="file" name="foto" accept="image/*">

                <label for="Quantidade_editar">Quantidade:</label>
                <input type="number" name="Quantidade" id="Quantidade_editar" min="0" value="<?= htmlspecialchars($produto_edicao['Quantidade']) ?>" required>

                <label for="id_apiario_editar">Apiário de Origem:</label>
                <select name="id_apiario" id="id_apiario_editar">
                    <option value="">Selecione um apiário (opcional)</option>
                    <?php foreach ($apiarios as $apiario): ?>
                        <option value="<?= $apiario['id_apiario'] ?>" <?= ($apiario['id_apiario'] == $apiario_produto) ? 'selected' : '' ?>>
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

    <!-- Modal: Repor -->
    <?php if ($produto_repor): ?>
    <div id="modalRepor" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Repor Produto</h2>
            <form method="POST" action="TELA_GERENCIAR_PRODUTOS.php">
                <input type="hidden" name="id_produto" value="<?= $produto_repor['id_produto'] ?>">

                <label for="Quantidade_repor">Quantidade:</label>
                <input type="number" name="Quantidade" id="Quantidade_repor" min="0" required>

                <button type="submit" name="acresentar_produto" class="btn_acao">Repor</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalRepor')">Cancelar</button>
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
            // Redireciona sem parâmetros
            window.location.href = 'TELA_GERENCIAR_PRODUTOS.php';
        }

        // Fechar clicando fora
        window.onclick = function(event) {
            if (event.target.classList && event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                window.location.href = 'TELA_GERENCIAR_PRODUTOS.php';
            }
        }

        <?php if ($produto_edicao): ?>
        document.addEventListener('DOMContentLoaded', function() {
            abrirModal('modalAlterar');
        });
        <?php endif; ?>
    </script>
</body>
</html>