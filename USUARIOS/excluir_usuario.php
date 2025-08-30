<?php
session_start();
require_once '../telas/conexao.php';

//VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

//INICIALIZA AS VARIAVEIS
$usuario = null;

//BUSCA TODOS OS USUÁRIOS CADASTRADOS EM ORDEM ALFABÉTICA
$sql = "SELECT * FROM usuario ORDER BY nome ASC";
$stmt = $pdo->prepare($sql);
$stmt-> execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SE U id FOR PASSADO VIA GET, EXCLUI O usuario
if(isset($_GET["id"]) && is_numeric( $_GET["id"] )){
    $id_usuario = $_GET["id"];

    // EXCLUI O USUARIO DO BANCO DE DADOS
    $sql = "DELETE FROM usuario WHERE id_usuario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Usuário excluido com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_USUARIOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir usuário!');</script>";
}}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="estilo1.css">
</head>
<body>
    <h2>Excluir Usuários</h2>
    <?php if (!empty($usuarios)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Ações</th>
            </tr>

            <?php foreach($usuarios as $usuario): ?>
                <tr>
                    <td><?=htmlspecialchars($usuario['id_usuario'])?></td>
                    <td><?=htmlspecialchars($usuario['nome'])?></td>
                    <td><?=htmlspecialchars($usuario['email'])?></td>
                    <td><?=htmlspecialchars($usuario['id_perfil'])?></td>
                    <td>
                        <a href="excluir_usuario.php?id=<?= htmlspecialchars($usuario['id_usuario'])?>"onclick="return confirm('Tem certeza que deseja excluir esse usuário?')"><button class="excluir">Excluir</button></a>
                    </td>
                </tr>
                <?php endforeach; ?>
        </table>
        <?php else: ?>
            <p>Nenhum usuário encontrado</p>
    <?php endif; ?>
 
    <address>
            Gustavo Wendt /estudante / tecnico em sistemas 
    </address>

</body>
</html>