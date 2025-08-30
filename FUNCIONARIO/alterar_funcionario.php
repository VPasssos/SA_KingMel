<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

$funcionario = null;

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    if (!empty($_POST['busca_funcionario'])) {
        $busca = trim($_POST["busca_funcionario"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM funcionario WHERE Nome LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$funcionario){
            echo "<script>alert('Funcionário não encontrado!');</script>";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"]=="GET") {
    if (!empty($_GET['id'])) {
        $busca = trim($_GET["id"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM funcionario WHERE Nome LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$funcionario){
            echo "<script>alert('Funcionário não encontrado!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Funcionário</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="../JS/mascaras.js"></script>
</head>
<body>
<?php include("../TELAS/MENU.php"); ?>

<h2>Alterar Funcionário</h2>

<form action="alterar_funcionario.php" method="POST">
    <label for="busca_funcionario">Digite o ID ou Nome do Funcionário:</label>
    <input type="text" id="busca_funcionario" name="busca_funcionario" required>
    <button type="submit">Buscar</button>
</form>

<?php if($funcionario): ?>
    <form action="processa_alteracao_funcionario.php" method="POST">
        <input type="hidden" name="id_funcionario" value="<?=htmlspecialchars($funcionario['id_funcionario'])?>" >

        <label>Nome:</label>
        <input type="text" name="nome" value="<?=htmlspecialchars($funcionario['Nome'])?>" required oninput="mascara(this, nomeM)" maxlength="10"><br>

        <label>CPF:</label>
        <input type="text" name="cpf" value="<?=htmlspecialchars($funcionario['CPF'])?>" required oninput="mascara(this, cpfM)" maxlength="14"><br>

        <label>Data Contratação:</label>
        <input type="date" name="data_contratacao" value="<?=htmlspecialchars($funcionario['Data_contratacao'])?>" oninput="mascara(this, dataM)" maxlength="10"><br>

        <label>Cargo:</label>
        <input type="text" name="cargo" value="<?=htmlspecialchars($funcionario['Cargo'])?>" ><br>

        <label>Salário:</label>
        <input type="number" step="0.01" name="salario" value="<?=htmlspecialchars($funcionario['Salario'])?>"><br>

        <label>Telefone:</label>
        <input type="text" name="telefone" value="<?=htmlspecialchars($funcionario['Telefone'])?>" oninput="mascara(this, telefoneM)" maxlength="15"><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?=htmlspecialchars($funcionario['Email'])?>"><br>

        <button type="submit">Alterar</button>
        <button type="reset">Cancelar</button>
    </form>
<?php endif; ?>
</body>
</html>
