<?php
session_start();
require_once 'conexao.php';

if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2) {
    echo "<script>alert('Acesso Negado!');window.location.href='MENU.php';</script>";
    exit();
}

$clientes = [];

if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["busca"])) {
    $busca = trim($_POST["busca"]);

    if(is_numeric($busca)) {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :busca ORDER BY Nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca',$busca,PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM cliente WHERE Nome LIKE :busca_nome ORDER BY Nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome',"$busca%",PDO::PARAM_STR);
    }
} else {
    $sql = "SELECT * FROM cliente ORDER BY Nome ASC";
    $stmt = $pdo->prepare($sql);
}

$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Clientes</title>
<link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
</head>
<body>
<?php include("MENU.php"); ?>
<h1>GERENCIAR CLIENTES</h1>

<form action="TELA_GERENCIAR_CLIENTES.php" method="POST">
    <label for="busca">Digite o ID ou Nome do cliente:</label>
    <input type="text" id="busca" name="busca">
    <button type="submit">Buscar</button>
</form>

<?php if(!empty($clientes)): ?>
<table border="1">
<thead>
<tr>
    <th>ID</th><th>Nome</th><th>CPF</th><th>Telefone</th>
    <th>Email</th><th>Data Nasc.</th><th>Endereço</th><th>Ações</th>
</tr>
</thead>
<tbody>
<?php foreach($clientes as $c): ?>
<tr>
    <td><?=htmlspecialchars($c['id_cliente'])?></td>
    <td><?=htmlspecialchars($c['Nome'])?></td>
    <td><?=htmlspecialchars($c['CPF'])?></td>
    <td><?=htmlspecialchars($c['Telefone'])?></td>
    <td><?=htmlspecialchars($c['Email'])?></td>
    <td><?=htmlspecialchars($c['Data_nascimento'])?></td>
    <td><?=htmlspecialchars($c['Endereco'])?></td>
    <td>
        <a href="../CLIENTE/alterar_cliente.php?id=<?=$c['id_cliente']?>">Alterar</a>
        <a href="../CLIENTE/excluir_cliente.php?id=<?=$c['id_cliente']?>" onclick="return confirm('Excluir este cliente?')">Excluir</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="../CLIENTE/cadastrar_cliente.php">Cadastrar Novo Cliente</a>
<?php else: ?>
<p>Nenhum cliente encontrado.</p>
<?php endif; ?>
</body>
</html>
