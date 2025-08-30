<?php
    session_start();
    require_once 'conexao.php';

    if($_SESSION['perfil']!=1 && $_SESSION['perfil']!=2) {
        echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
        exit();
    }

    // INICIALIZA A VARIAVEL PARA EVITAR ERROS
    $usuarios = [];

    // SE O FORMULARIO FOR ENCIADO, BUSCA O USUARIO PELO ID OU NOME

    if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty( $_POST["busca"] )) {
        $busca = trim($_POST["busca"]);

        // VERIFICA SE A BUSCA É UM NÚMERO OU UM NOME

        if(is_numeric($busca)) {
            $sql = "SELECT * FROM usuario WHERE id_usuario = :busca ORDER BY nome ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome ORDER BY nome ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
        }
    } else {
        $sql = "SELECT * FROM usuario ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINGMEL</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_USUARIOS.css">
</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR USUARIOS</h1>
    </main>

    <div class="tabela_usuarios">

    <?php if(!empty($usuarios)):?>

        <form action="TELA_GERENCIAR_USUARIOS.php" method="POST">
            <label for="busca">Digite o id ou NOME(opcional):</label>
            <input type="text" id="busca" name="busca">
            <button type="submit">Buscar</button>
        </form>

        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nome</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Perfil</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario):?>
                    <tr>
                        <td><?=htmlspecialchars($usuario['id_usuario'])?></td>
                        <td><?=htmlspecialchars($usuario['nome'])?></td>
                        <td><?=htmlspecialchars($usuario['email'])?></td>
                        <td><?=htmlspecialchars($usuario['id_perfil'])?></td>
                        <td>
                            <a href="../USUARIOS/alterar_usuario.php?id=<?=htmlspecialchars($usuario['id_usuario'])?>">Alterar</a>
                            <a href="../USUARIOS/excluir_usuario.php?id=<?=htmlspecialchars($usuario['id_usuario'])?>"onclick="return confirm('Tem certeza que deseja excluir esse usuario')">Excluir</a>
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../USUARIOS/cadastrar_usuario.php?id=<?=htmlspecialchars($usuario['id_usuario'])?>">Cadastrar</a>
    <?php else:?>
        <p>Nenhum usuario encontrado</p>
    <?php endif; ?>
    </div>
</body>
</html>