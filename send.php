<?php
declare(strict_types=1);

// const RECIPIENT = 'kitagaito@tom-z.co.jp';
const RECIPIENT = 'kunimura@cvcompany-inc.com';
const SITE_NAME = '株式会社トム財産ネットワークス';

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function showMessage(string $title, string $message, int $statusCode = 400): never
{
    http_response_code($statusCode);
    $safeTitle = escape($title);
    $safeMessage = escape($message);
    $safeSiteName = escape(SITE_NAME);
    echo <<<HTML
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle}｜{$safeSiteName}</title>
  <style>
    body { margin: 0; padding: 48px 20px; color: #1f2b3a; font-family: sans-serif; line-height: 1.8; }
    main { max-width: 680px; margin: 0 auto; }
    a { color: #1c5f92; }
  </style>
</head>
<body>
  <main>
    <h1>{$safeTitle}</h1>
    <p>{$safeMessage}</p>
    <p><a href="contact.html">お問い合わせページへ戻る</a></p>
  </main>
</body>
</html>
HTML;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showMessage('アクセスできません', 'お問い合わせフォームから送信してください.');
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$prefecture = trim((string)($_POST['prefecture'] ?? ''));
$concerns = array_map('trim', (array)($_POST['concern'] ?? []));
$discovery = trim((string)($_POST['discovery'] ?? ''));
$discoveryOther = trim((string)($_POST['discovery_other'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$requests = array_map('trim', (array)($_POST['request'] ?? []));
$method = trim((string)($_POST['method'] ?? ''));

foreach ([$name, $phone, $email, $prefecture, $discovery, $discoveryOther, $message, $method] as $value) {
    if (preg_match('/[\r\n]/', $value) === 1) {
        showMessage('送信できません', '入力内容に使用できない文字が含まれています.');
    }
}

if ($name === '' || $email === '' || $message === '') {
    showMessage('入力内容をご確認ください', 'お名前、メールアドレス、お問い合わせ内容は必須です.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showMessage('入力内容をご確認ください', 'メールアドレスの形式が正しくありません.');
}

if (!function_exists('mb_send_mail')) {
    showMessage('送信できませんでした', 'サーバーのメール送信機能が有効になっていません。管理会社へご確認ください.');
}

mb_language('Japanese');
mb_internal_encoding('UTF-8');

$formatList = static function (array $values): string {
    return $values === [] ? 'なし' : implode('、', array_filter($values, static fn ($value): bool => $value !== ''));
};

$subject = 'Webサイトからのお問い合わせ';
$body = implode("\n", [
    'Webサイトからお問い合わせがありました。',
    '',
    'お名前: ' . $name,
    '電話番号: ' . ($phone !== '' ? $phone : '未入力'),
    'メールアドレス: ' . $email,
    '物件の所在都道府県: ' . ($prefecture !== '' ? $prefecture : '未選択'),
    'お悩みの種類: ' . $formatList($concerns),
    '知ったきっかけ: ' . ($discovery !== '' ? $discovery : '未選択'),
    'その他のきっかけ: ' . ($discoveryOther !== '' ? $discoveryOther : '未入力'),
    '現在の具体的な状況:',
    $message,
    '',
    'ご要望: ' . $formatList($requests),
    '希望する相談方法: ' . ($method !== '' ? $method : '未選択'),
]);
$body = mb_convert_encoding($body, 'JIS', 'UTF-8');

$headers = implode("\r\n", [
    'From: ' . mb_encode_mimeheader(SITE_NAME, 'UTF-8') . ' <' . RECIPIENT . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=ISO-2022-JP',
    'Content-Transfer-Encoding: 7bit',
]);

if (!mb_send_mail(RECIPIENT, $subject, $body, $headers)) {
    showMessage('送信できませんでした', 'メールを送信できませんでした。お手数ですが、時間をおいて再度お試しください.');
}

header('Location: contact.html?sent=1', true, 303);
exit;