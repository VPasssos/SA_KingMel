<?php
session_start();
require_once '../telas/conexao.php';

if($_SESSION['perfil'] != 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id_produto"];
    $tipo = $_POST["tipo"];
    $data = $_POST["data_embalado"];
    $peso = $_POST["peso"];
    $preco = $_POST["preco"];
    $qtd = $_POST["quantidade"];

    // Verifica se uma nova imagem foi enviada
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $nomeFoto = $_FILES['foto']['name'];
        $tipoFoto = mime_content_type($_FILES['foto']['tmp_name']);
        $foto = file_get_contents($_FILES['foto']['tmp_name']);

        $sql = "UPDATE produto 
                SET Tipo_mel=:tipo, Data_embalado=:data, Peso=:peso, Preco=:preco, Quantidade=:qtd,
                    nome_foto=:nome_foto, tipo_foto=:tipo_foto, foto=:foto
                WHERE id_produto=:id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome_foto', $nomeFoto);
        $stmt->bindParam(':tipo_foto', $tipoFoto);
        $stmt->bindParam(':foto', $foto, PDO::PARAM_LOB);
    } else {
        // Mantém a imagem atual
        $sql = "UPDATE produto 
                SET Tipo_mel=:tipo, Data_embalado=:data, Peso=:peso, Preco=:preco, Quantidade=:qtd
                WHERE id_produto=:id";
        $stmt = $pdo->prepare($sql);
    }

    // Bind dos outros parâmetros
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':peso', $peso);
    $stmt->bindParam(':preco', $preco);
    $stmt->bindParam(':qtd', $qtd);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Produto alterado com sucesso!');window.location.href='../TELAS/TELA_GERENCIAR_PRODUTOS.php';</script>";
    } else {
        echo "<script>alert('Erro ao alterar o produto!');</script>";
    }
}
?>
