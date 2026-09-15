<?php
// config/lecrystalbar/mail_config.php

// Chargement sécurisé du mot de passe (fichier hors de portée du web, voir security/lecrystalbar/.htaccess)
require_once __DIR__ . '/../../security/lecrystalbar/smtp_secret.php';

// Identifiants SMTP pour l'envoi de mails via le serveur OVH
$SMTP_CONF = [
    'HOST' => 'ssl://ssl0.ovh.net',
    'PORT' => 465,
    'USER' => 'contact@lecrystalbar.com',
    'PASS' => SMTP_PASSWORD,
    'FROM_MAIL' => 'contact@lecrystalbar.com',
    'FROM_NAME' => 'Le Crystal Bar',
    'TO_ADMIN' => 'contact@lecrystalbar.com',
];
