<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../config/smtp_helper.php';

const RECIPIENT = 'contact@lecrystalbar.com';
const FORM_URL = 'formulaire.html';
const LOG_FILE = __DIR__ . '/../logs/privatisation.log';
const RATE_LIMIT_DIR = __DIR__ . '/.rate-limit';
const RATE_LIMIT_MIN_INTERVAL = 30; // secondes minimum entre deux envois
const RATE_LIMIT_MAX_PER_WINDOW = 5; // envois max par fenêtre
const RATE_LIMIT_WINDOW = 3600; // durée de la fenêtre en secondes

function redirectToForm(string $status): never
{
    header('Location: ' . FORM_URL . '?status=' . rawurlencode($status), true, 303);
    exit;
}

function logPrivatisationRequest(string $status, string $name, string $email, string $phone, int|false $guests, string $eventDate, string $privateType, array $options, string $message): void
{
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0700, true);
    }
    $line = implode(' | ', [
        date('Y-m-d H:i:s'),
        'status=' . $status,
        'ip=' . ($_SERVER['REMOTE_ADDR'] ?? ''),
        'nom=' . $name,
        'email=' . $email,
        'tel=' . $phone,
        'personnes=' . ($guests !== false ? (string) $guests : ''),
        'date=' . $eventDate,
        'type=' . $privateType,
        'options=' . implode(', ', $options),
        'message=' . str_replace(["\r", "\n"], ' ', $message),
    ]);
    file_put_contents(LOG_FILE, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function checkRateLimit(string $ip): bool
{
    if (!is_dir(RATE_LIMIT_DIR)) {
        mkdir(RATE_LIMIT_DIR, 0700, true);
    }
    $file = RATE_LIMIT_DIR . '/' . hash('sha256', $ip) . '.json';
    $handle = fopen($file, 'c+');
    if ($handle === false) {
        return true; // ne bloque pas l'envoi si le stockage est indisponible
    }
    flock($handle, LOCK_EX);
    $raw = stream_get_contents($handle);
    $data = $raw !== false && $raw !== '' ? json_decode($raw, true) : null;
    $now = time();
    $lastSent = (int) ($data['last'] ?? 0);
    $windowStart = (int) ($data['window_start'] ?? $now);
    $count = (int) ($data['count'] ?? 0);

    if ($now - $windowStart > RATE_LIMIT_WINDOW) {
        $windowStart = $now;
        $count = 0;
    }

    $allowed = ($now - $lastSent) >= RATE_LIMIT_MIN_INTERVAL && $count < RATE_LIMIT_MAX_PER_WINDOW;

    if ($allowed) {
        $count++;
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode(['last' => $now, 'window_start' => $windowStart, 'count' => $count]));
    }

    flock($handle, LOCK_UN);
    fclose($handle);

    return $allowed;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
if ($clientIp === '' || !checkRateLimit($clientIp)) {
    redirectToForm('too_many');
}

if (!empty($_POST['website'])) {
    redirectToForm('success');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$guests = filter_input(INPUT_POST, 'guests', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$eventDate = trim((string) ($_POST['event-date'] ?? ''));
$privateType = trim((string) ($_POST['private-type'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$consent = isset($_POST['consent']);

$allowedTypes = [
    'Soirée privée',
    'Anniversaire',
    'Enterrement de vie de célibataire',
    "Événement d'entreprise",
    'Autre événement',
];
$allowedOptions = [
    'Soirée dansante',
    "Présence d'un DJ",
    'Open bar',
    'Restauration sur mesure',
    'Karaoké',
    'Animation ou spectacle',
    'Soirée casino',
    'Photographe',
];
$options = array_values(array_intersect($allowedOptions, (array) ($_POST['options'] ?? [])));

if ($name === '' || strlen($name) > 120 || preg_match('/[\r\n]/', $name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) > 40 || preg_match('/[\r\n]/', $phone) || $phone === '' || $guests === false || !in_array($privateType, $allowedTypes, true) || !$consent) {
    redirectToForm('invalid');
}

if ($eventDate !== '') {
    $date = DateTime::createFromFormat('Y-m-d', $eventDate);
    if (!$date || $date->format('Y-m-d') !== $eventDate) {
        redirectToForm('invalid');
    }
}

if (strlen($message) > 4000) {
    redirectToForm('invalid');
}

$body = implode("\n", [
    'Nouvelle demande de privatisation',
    '',
    'Nom et prénom : ' . $name,
    'E-mail : ' . $email,
    'Téléphone : ' . $phone,
    'Nombre de personnes : ' . $guests,
    'Date souhaitée : ' . ($eventDate !== '' ? $eventDate : 'Non précisée'),
    'Type de privatisation : ' . $privateType,
    'Options : ' . ($options ? implode(', ', $options) : 'Aucune'),
    '',
    'Demande complémentaire :',
    $message !== '' ? $message : 'Aucune',
]);

$subject = 'Demande de privatisation - ' . $name;

$sent = send_mail_via_smtp(RECIPIENT, $subject, $body, $SMTP_CONF, $email);
logPrivatisationRequest($sent ? 'success' : 'error', $name, $email, $phone, $guests, $eventDate, $privateType, $options, $message);

if (!$sent) {
    redirectToForm('error');
}

redirectToForm('success');
