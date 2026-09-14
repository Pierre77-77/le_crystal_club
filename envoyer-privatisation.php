<?php
declare(strict_types=1);

const RECIPIENT = 'contact@lecrystalbar.com';
const FORM_URL = 'formulaire.html';

function redirectToForm(string $status): never
{
    header('Location: ' . FORM_URL . '?status=' . rawurlencode($status), true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
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
$headers = [
    'From: Le Crystal <contact@lecrystalbar.com>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];

if (!mail(RECIPIENT, $subject, $body, implode("\r\n", $headers))) {
    redirectToForm('error');
}

redirectToForm('success');
