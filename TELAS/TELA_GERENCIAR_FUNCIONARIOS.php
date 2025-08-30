<?php
session_start();
require_once 'conexao.php';

if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2) {
    echo "<script>alert('Acesso Negado!');window.location.href='MENU.php';</script>";
    exit();
}

$funcionarios = [];

if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["busca"])) {
    $busca = trim($_POST["busca"]);

    if(is_numeric($busca)) {
        $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca ORDER BY Nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca',$busca,PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM funcionario WHERE Nome LIKE :busca_nome ORDER BY Nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome',"$busca%",PDO::PARAM_STR);
    }
} else {
    $sql = "SELECT * FROM funcionario ORDER BY Nome ASC";
    $stmt = $pdo->prepare($sql);
}

$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Funcionários</title>
<link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
</head>
<body>
<?php include("MENU.php"); ?>
<h1>GERENCIAR FUNCIONÁRIOS</h1>

<form action="TELA_GERENCIAR_FUNCIONARIOS.php" method="POST">
    <label for="busca">Digite o ID ou Nome do funcionário:</label>
    <input type="text" id="busca" name="busca">
    <button type="submit">Buscar</button>
</form>

<?php if(!empty($funcionarios)): ?>
<table border="1">
<thead>
<tr>
    <th>ID</th><th>Nome</th><th>CPF</th><th>Data Contratação</th>
    <th>Cargo</th><th>Salário</th><th>Telefone</th><th>Email</th><th>Ações</th>
</tr>
</thead>
<tbody>
<?php foreach($funcionarios as $f): ?>
<tr>
    <td><?=$f['id_funcionario']?></td>
    <td><?=$f['Nome']?></td>
    <td><?=$f['CPF']?></td>
    <td><?=$f['Data_contratacao']?></td>
    <td><?=$f['Cargo']?></td>
    <td><?=$f['Salario']?></td>
    <td><?=$f['Telefone']?></td>
    <td><?=$f['Email']?></td>
    <td>
        <a href="../FUNCIONARIO/alterar_funcionario.php?id=<?=$f['id_funcionario']?>">Alterar</a>
        <a href="../FUNCIONARIO/excluir_funcionario.php?id=<?=$f['id_funcionario']?>" onclick="return confirm('Excluir este funcionário?')">Excluir</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="../FUNCIONARIO/cadastrar_funcionario.php">Cadastrar Novo Funcionário</a>
<?php else: ?>
<p>Nenhum funcionário encontrado.</p>
<?php endif; ?>
</body>
</html>
