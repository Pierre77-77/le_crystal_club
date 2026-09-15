<?php
/**
 * Helper SMTP léger pour envoyer des mails authentifiés (sans dépendance externe).
 */

function send_mail_via_smtp($to, $subject, $body, $config, $reply_to = null)
{
    if (!$config || !isset($config['HOST'])) {
        error_log("SMTP Error: Configuration SMTP manquante ou invalide.");
        return false;
    }

    if ($reply_to === null) {
        $reply_to = $config['FROM_MAIL'];
    }

    $host = $config['HOST'];
    $port = $config['PORT'];
    $from_mail = $config['FROM_MAIL'];
    $from_name = $config['FROM_NAME'];
    $password = $config['PASS'];
    $username = $config['USER'];

    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        error_log("SMTP Error: Connexion impossible à $host ($errstr)");
        return false;
    }

    if (!function_exists('read_smtp_response')) {
        function read_smtp_response($socket) {
            $response = "";
            while ($str = fgets($socket, 515)) {
                $response .= $str;
                if (substr($str, 3, 1) == " ") { break; }
            }
            return $response;
        }
    }

    read_smtp_response($socket); // Banner

    fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
    read_smtp_response($socket);

    fputs($socket, "AUTH LOGIN\r\n");
    read_smtp_response($socket);

    fputs($socket, base64_encode($username) . "\r\n");
    read_smtp_response($socket);

    fputs($socket, base64_encode($password) . "\r\n");
    $response = read_smtp_response($socket);

    if (strpos($response, '235') !== 0) {
        error_log("SMTP Auth Failed: $response");
        fclose($socket);
        return false;
    }

    fputs($socket, "MAIL FROM: <$from_mail>\r\n");
    read_smtp_response($socket);

    fputs($socket, "RCPT TO: <$to>\r\n");
    read_smtp_response($socket);

    fputs($socket, "DATA\r\n");
    read_smtp_response($socket);

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "From: \"$from_name\" <$from_mail>\r\n";
    $headers .= "Reply-To: $reply_to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "Date: " . date("r") . "\r\n";
    $headers .= "\r\n";
    $headers .= $body . "\r\n";
    $headers .= ".\r\n";

    fputs($socket, $headers);
    $result = read_smtp_response($socket);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (strpos($result, '250') === 0);
}
