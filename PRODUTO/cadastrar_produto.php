<?php

function redimensionarImagem($imagem, $largura, $altura) {
    // Descobre o tipo MIME real da imagem
    $tipo = mime_content_type($imagem);

    switch ($tipo) {
        case 'image/jpeg':
            $imagemOriginal = imagecreatefromjpeg($imagem);
            break;
        case 'image/png':
            $imagemOriginal = imagecreatefrompng($imagem);
            break;
        case 'image/gif':
            $imagemOriginal = imagecreatefromgif($imagem);
            break;
        default:
            throw new Exception("Formato de imagem não suportado: " . $tipo);
    }

    // Pega as dimensões originais
    list($larguraOriginal, $alturaOriginal) = getimagesize($imagem);

    // Cria uma nova imagem em branco com fundo transparente (se for PNG/GIF)
    $novaImagem = imagecreatetruecolor($largura, $altura);

    if ($tipo == 'image/png' || $tipo == 'image/gif') {
        imagecolortransparent($novaImagem, imagecolorallocatealpha($novaImagem, 0, 0, 0, 127));
        imagealphablending($novaImagem, false);
        imagesavealpha($novaImagem, true);
    }

    // Copia e redimensiona
    imagecopyresampled(
        $novaImagem,
        $imagemOriginal,
        0, 0, 0, 0,
        $largura, $altura,
        $larguraOriginal, $alturaOriginal
    );

    // Inicia buffer
    ob_start();

    // Salva de acordo com o tipo original
    switch ($tipo) {
        case 'image/jpeg':
            imagejpeg($novaImagem);
            break;
        case 'image/png':
            imagepng($novaImagem);
            break;
        case 'image/gif':
            imagegif($novaImagem);
            break;
    }

    $dadosImagem = ob_get_clean();

    // Libera memória
    imagedestroy($novaImagem);
    imagedestroy($imagemOriginal);

    return $dadosImagem;
}



session_start();
require_once '../telas/conexao.php';

//VERIFICA SE O USUÁRIO TENHA PERMISSÃO
//SUPONDO QUE O PERFIL "1" SEJA O ADM


if($_SESSION['perfil']!= 1){
    echo "Acesso Negado";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['foto'])) {
    if ($_FILES['foto']['error'] == 0) {
        $tipo_mel   = $_POST["tipo_mel"];
        $data_embalado = $_POST["data_embalado"];
        $Peso       = $_POST["Peso"];
        $Preco      = $_POST["Preco"];
        $Quantidade = $_POST["Quantidade"];

        $nomeFoto = $_FILES['foto']['name'];
        $tipoFoto = mime_content_type($_FILES['foto']['tmp_name']); // mais seguro que $_FILES['foto']['type']

        // Redimensiona a imagem
        $foto = redimensionarImagem($_FILES['foto']['tmp_name'], 300, 400);

        $sql = "INSERT INTO produto (tipo_mel, data_embalado, Peso, Preco, Quantidade, nome_foto, tipo_foto, foto)
                VALUES (:tipo_mel, :data_embalado, :Peso, :Preco, :Quantidade, :nome_foto, :tipo_foto, :foto)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tipo_mel', $tipo_mel);
        $stmt->bindParam(':data_embalado', $data_embalado);
        $stmt->bindParam(':Peso', $Peso);
        $stmt->bindParam(':Preco', $Preco);
        $stmt->bindParam(':Quantidade', $Quantidade);
        $stmt->bindParam(':nome_foto', $nomeFoto);
        $stmt->bindParam(':tipo_foto', $tipoFoto);
        $stmt->bindParam(':foto', $foto, PDO::PARAM_LOB);

        if ($stmt->execute()) {
            echo "<script>alert('Produto cadastrado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar o produto');</script>";
        }
    } else {
        echo "Erro no upload da foto! Código: " . $_FILES['foto']['error'];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="mascaras.js"></script>
</head>
<body>

<?php include("../TELAS/MENU.php"); ?>

    <h2>Cadastrar produto</h2>
    <form action="cadastrar_produto.php" method="POST" enctype="multipart/form-data">

        
        <label for="tipo_mel">tipo_mel:</label>
        <input type="text" id="tipo_mel" name="tipo_mel" required onkeypress ="mascara(this, nomeM)">
        
        
        <label for="data_embalado">Data em que foi embalado:</label>
        <input type="text" id="data_embalado" name="data_embalado" required>

        <label for="Peso">Peso:</label>
        <input type="text" id="Peso" name="Peso" required >

        <label for="Preco">Preço:</label>
        <input type="text" id="Preco" name="Preco" required >
        
        <label for="Quantidade">Quantidade:</label>
        <input type="text" id="Quantidade" name="Quantidade" required >
        
        <label for="foto">Foto:</label>
        <input type="file" id="foto" name="foto" required >    

        <button type="submit">Salvar</button>

        <button type="reset">Cancelar</button>
    </form>

    <address>
            Gustavo Wendt /estudante / tecnico em sistemas 
    </address>
</body>
</html>