<?php

const CACHE = false;

date_default_timezone_set('America/Toronto');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');


if(!isset($_GET['id'])) throw_404();


if(!is_scraper()) redirect('https://agenda.deuxpardeux.quebec/?id=' . $_GET['id']);


$obscur = file_get_contents(__DIR__ . '/bt1oh97j7X.bin');
$inflate = gzdecode("\x1f\x8b" . $obscur);
$base64 = str_rot13($inflate);
$json = base64_decode($base64);
$CONFIG = json_decode($json);

$cachefile = __DIR__ . '/temp/' . $_GET['id'] . '.dat';
if(CACHE && is_file($cachefile)) {
	$results = file_get_contents($cachefile);
} else {
	$chnd = curl_init('https://www.googleapis.com/calendar/v3/calendars/' . urlencode($CONFIG->CALENDAR_ID) . '/events/' . urlencode($_GET['id']) . '?key=' . urlencode($CONFIG->GOOGLE_API_KEY));
	curl_setopt_array($chnd, [
		CURLOPT_AUTOREFERER    => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
	]);

	$results = curl_exec($chnd);
	$code = curl_getinfo($chnd, CURLINFO_HTTP_CODE);

	if($code != 200) throw_404();

	if(CACHE && !is_dir(pathinfo($cachefile, PATHINFO_DIRNAME))) mkdir(pathinfo($cachefile, PATHINFO_DIRNAME), 0777, true);
	file_put_contents($cachefile, $results);
}

$data = json_decode($results);

list($html, $links) = extract_hrefs_by_text($data->description, [
	'image-couverture',
	'image-calendrier',
	'image-carte',
	'image-scraper'
]);

$ogimage = $links['image-scraper']  ?? $links['image-couverture'];

$html = preg_replace('/^(?:\s*<br\b[^>]*>\s*)+/i', '', $html);
$html = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/msi', '', $html);
$html = trim($html);

$text = strip_tags($html);
$text = preg_replace('/[\r\n\t\s]+/msi', ' ', $text);
$text = trim($text);

if(strlen($text) > 256) $text = substr($text, 0, 256) . '...';

list($place, $street, $city, $province, $postal, $country) = parse_google_address($data->location);

define('URL', 'https://' . $_SERVER['SERVER_NAME'] . '/'. $_GET['id'] . '/');
define('TITLE', trim($data->summary));
define('DESCRIPTION', $text);
define('IMAGE_URL', $ogimage);
define('IMAGE_ALT', trim($data->summary));
define('THEME_COLOR_HEX', '#d4efff');

$schema = [
	"@context" => "https://schema.org",
	"@type" => "Event",
	"name" => TITLE,
	"description" => DESCRIPTION,
	"url" => URL,
	"image" => [IMAGE_URL],
	"startDate" => $data->start->dateTime,
	"endDate" => $data->end->dateTime,
	"eventStatus" => "https://schema.org/EventScheduled",
	"eventAttendanceMode" => "https://schema.org/OfflineEventAttendanceMode",
	"location" => [
		"@type" => "Place",
		"name" => $place,
		"address" => [
			"@type" => "PostalAddress",
			"streetAddress" => $street,
			"addressLocality" => $city,
			"addressRegion" => $province,
			"postalCode" => $postal,
			"addressCountry" => $country,
		],
	],
	"organizer" => [
		"@type" => "Organization",
		"name" => "Deux par deux",
		"url" => "https://deuxpardeux.quebec/",
	],
];

$schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
define('SCHEMA', indent($schema_json, 1, chr(9)));

include(__DIR__ . '/template.php');





function throw_404()
{
	header("HTTP/1.0 404 Avez-vous perdu votre poisson ?");
	readfile(__DIR__ . '/404.html');
	exit;
}


function redirect($url)
{
	header('location: ' . $url);
	exit();
}


function is_scraper()
{
	$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
	if (!empty($_SERVER['HTTP_X_FACEBOOK_EXTERNAL_HIT'])) return 'facebook';
	
	$patterns = [
		'facebook'  => '/facebookexternalhit|facebot/i',
		'twitter'   => '/twitterbot/i',
		'reddit'    => '/redditbot/i',
		'discord'   => '/discordbot/i',
		'slack'     => '/slackbot|slack-imgproxy/i',
		'linkedin'  => '/linkedinbot/i',
		'pinterest' => '/pinterestbot/i',
		'whatsapp'  => '/whatsapp/i',
		'telegram'  => '/telegrambot/i',
		'google'    => '/googlebot|adsbot-google|mediapartners-google/i',
	];

	foreach ($patterns as $name => $re) if ($ua && preg_match($re, $ua)) return $name;

	return false;
}


function extract_href_by_text(string $html, string $text): array
{
    $hrefs = [];
	$re = '~<a\b[^>]*\bhref\s*=\s*(["\'])([^"\']+)\1[^>]*>\s*' . preg_quote($text, '~') . '\s*</a>~i';
    $newHtml = preg_replace_callback($re, function ($m) use (&$hrefs) {
        $hrefs[] = $m[2];
        return '';
    }, $html);
    return [$newHtml ?? $html, $hrefs[0] ?? null];
}


function extract_hrefs_by_text(string $html, array $text): array
{
	$hrefs = [];
	foreach($text as $tag) list($html, $hrefs[$tag]) = extract_href_by_text($html, $tag);
	return [$html, $hrefs];
}


function esc(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


function parse_google_address($str)
{
	$regex = '/^\s*(?:(?<place>(?!\d)[^,]+?),\s*)?(?<street>[^,]+?),\s*(?<city>[^,]+?),\s*(?<province>AB|BC|MB|NB|NL|NS|NT|NU|ON|PE|QC|SK|YT|Québec|Quebec|QC\.?)(?:\s+(?<postal>[A-Z]\d[A-Z][ -]?\d[A-Z]\d))?(?:,\s*(?<country>Canada))?\s*$/iu';
	if(!preg_match($regex, $str, $m)) return array_fill(0, 6, null);
	return [
		trim($m['place']) ?: null,
		trim($m['street']) ?: null,
		trim($m['city']) ?: null,
		trim($m['province']) ?: null,
		trim($m['postal']) ?: null,
		trim($m['country']) ?: null,
	];
}


function indent($str, $num, $esc = ' ')
{
	$lines = explode("\n", $str);
	foreach($lines as $k => $v) $lines[$k] = str_repeat($esc, $num) . $v;
	return join("\n", $lines);
}