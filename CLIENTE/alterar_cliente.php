<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

$cliente = null;

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    if (!empty($_POST['busca_cliente'])) {
        $busca = trim($_POST["busca_cliente"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM cliente WHERE id_cliente = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM cliente WHERE Nome LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$cliente){
            echo "<script>alert('Cliente não encontrado!');</script>";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"]=="GET") {
    if (!empty($_GET['id'])) {
        $busca = trim($_GET["id"]);

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM cliente WHERE id_cliente = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM cliente WHERE Nome LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$cliente){
            echo "<script>alert('Cliente não encontrado!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Cliente</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="../JS/mascaras.js"></script>
</head>
<body>
<?php include("../TELAS/MENU.php"); ?>

<h2>Alterar Cliente</h2>

<form action="alterar_cliente.php" method="POST">
    <label for="busca_cliente">Digite o ID ou Nome do Cliente:</label>
    <input type="text" id="busca_cliente" name="busca_cliente" required>
    <button type="submit">Buscar</button>
</form>

<?php if($cliente): ?>
    <form action="processa_alteracao_cliente.php" method="POST">
        <input type="hidden" name="id_cliente" value="<?=htmlspecialchars($cliente['id_cliente'])?>" >

        <label>Nome:</label>
        <input type="text" name="nome" value="<?=htmlspecialchars($cliente['Nome'])?>" required oninput="mascara(this, nomeM)" maxlength="10"><br>

        <label>CPF:</label>
        <input type="text" name="cpf" value="<?=htmlspecialchars($cliente['CPF'])?>" required oninput="mascara(this, cpfM)" maxlength="14"><br>

        <label>Telefone:</label>
        <input type="text" name="telefone" value="<?=htmlspecialchars($cliente['Telefone'])?>" oninput="mascara(this, data)" maxlength="15"oninput="mascara(this, telefoneM)" ><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?=htmlspecialchars($cliente['Email'])?>"><br>

        <label>Data Nascimento:</label>
        <input type="date" name="data_nascimento" value="<?=htmlspecialchars($cliente['Data_nascimento'])?>" oninput="mascara(this, dataM)" maxlength="10"><br>

        <label>Endereço:</label>
        <input type="text" name="endereco" value="<?=htmlspecialchars($cliente['Endereco'])?>"><br>

        <button type="submit">Alterar</button>
        <button type="reset">Cancelar</button>
    </form>
<?php endif; ?>
</body>
</html>
