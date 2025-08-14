<?php
/* send.php – Gasthaus zur Eiche (.net @ Crioco)
 * Versand von Formularen per E-Mail an info@landbaeckerei-oetzmann.de
 * PHP mail() auf Crioco-Server
 */

declare(strict_types=1);
mb_internal_encoding('UTF-8');

// -------- Einstellungen --------
$TO        = 'info@landbaeckerei-oetzmann.de';
$SUBJECT   = 'Anfrage über die Website – Gasthaus zur Eiche';
$FROM_MAIL = 'info@landbaeckerei-oetzmann.de';   // Absender aus eurer Domain
$FROM_NAME = 'Website Gasthaus zur Eiche';
// --------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Methode nicht erlaubt.';
  exit;
}

// Honeypot (Bots füllen "company")
if (!empty($_POST['company'] ?? '')) {
  http_response_code(200);
  echo 'Danke.';
  exit;
}

// Felder
$name     = trim((string)($_POST['name'] ?? ''));
$email    = trim((string)($_POST['email'] ?? ''));
$occasion = trim((string)($_POST['occasion'] ?? ''));
$message  = trim((string)($_POST['message'] ?? ''));
$page     = trim((string)($_POST['page'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
  http_response_code(400);
  echo 'Bitte füllen Sie alle Pflichtfelder aus.';
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
  exit;
}

// Body (Plaintext)
$lines = [
  'Neue Anfrage über die Website',
  '--------------------------------------------',
  "Name:     $name",
  "E-Mail:   $email",
  $occasion !== '' ? "Anlass:   $occasion" : null,
  $page     !== '' ? "Seite:    $page"     : null,
  "IP:       " . ($_SERVER['REMOTE_ADDR'] ?? '-'),
  "Datum:    " . date('Y-m-d H:i:s'),
  '',
  'Nachricht:',
  $message,
];
$body = implode("\n", array_filter($lines, fn($l) => $l !== null));

// Header
$from    = sprintf('From: %s <%s>', $FROM_NAME, $FROM_MAIL);
$replyTo = sprintf('Reply-To: %s', $email);
$headers = [
  $from,
  $replyTo,
  'MIME-Version: 1.0',
  'Content-Type: text/plain; charset=UTF-8',
  'Content-Transfer-Encoding: 8bit'
];
$headersStr = implode("\r\n", $headers);

// Versand
$sent = @mail($TO, '=?UTF-8?B?'.base64_encode($SUBJECT).'?=', $body, $headersStr);

// Danke-Seite
if ($sent) {
  ?><!doctype html>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Danke – Nachricht gesendet</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:#fafafa;color:#111;display:grid;place-items:center;min-height:100vh;padding:24px}
    .card{max-width:680px;background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.04)}
    a{color:#065f46}
  </style>
  <div class="card">
    <h1 style="margin:0 0 8px 0;font-size:24px;">Vielen Dank!</h1>
    <p>Ihre Nachricht wurde erfolgreich gesendet. Wir melden uns so schnell wie möglich.</p>
    <p style="margin-top:16px"><a href="https://www.gasthaus-zur-eiche.net/#contact">Zurück zur Website</a></p>
  </div>
  <?php
} else {
  http_response_code(500);
  echo 'Leider ist ein Fehler beim Versand aufgetreten. Bitte schreiben Sie an: info@landbaeckerei-oetzmann.de';
}
