<?php
// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Autoload do Composer (ajuste o caminho se necessário)
require 'vendor/autoload.php';

/**
 * Gera uma senha temporária aleatória com o tamanho especificado.
 */
function gerarSenhaTemporaria($tamanho = 8) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $tamanho);
}

/**
 * Envia um e-mail com a senha temporária usando PHPMailer.
 */
function EnvioEmail($destinatario, $senha) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gustavowendt14@gmail.com';    // Substitua pelo seu e-mail Gmail
        $mail->Password = 'yama lybx fbsa abxi';          // Substitua pela App Password do Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Configurações do remetente e destinatário
        $mail->setFrom('gustavowendt14@gmail.com', 'Gustavo');
        $mail->addAddress($destinatario);

        // Conteúdo do e-mail
        $mail->isHTML(false);
        $mail->Subject = 'Sua senha temporaria ';
        $mail->Body    = "Olá, $destinatario. Sua nova senha temporária é: $senha";

        $mail->send();
        echo "E-mail enviado com sucesso!";
    } catch (Exception $e) {
        echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
    }
}
?>
