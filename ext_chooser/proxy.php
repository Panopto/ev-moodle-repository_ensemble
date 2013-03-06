<?php

require_once(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))) . '/config.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once('Zend/Http/Client.php');

$api_url = !empty($_GET['request']) ? urldecode($_GET['request']) : '';

$ensembleURL = get_config('ensemble', 'ensembleURL');
$serviceUser = get_config('ensemble', 'serviceUser');
$servicePass = get_config('ensemble', 'servicePass');

// Fail if we're missing our required urls
if (empty($ensembleURL) || empty($api_url)) {
    header('Missing url parameter', true, 400);
    exit;
}

// Fail if our service account isn't configured
if (empty($serviceUser) || empty($servicePass)) {
    header('Missing service account configuration', true, 400);
    exit;
}

// Only service requests for our configured ensemble url
if (preg_match('#^' . preg_quote($ensembleURL) . '#i', $api_url) !== 1) {
    header('URL mismatch', true, 400);
    exit;
}

$client = new Zend_Http_Client($api_url);
// Construct basic auth header for configured service account
$client->setHeaders('Authorization', 'Basic ' . base64_encode($serviceUser . ':' . $servicePass));

// TODO - Append user filter for currently logged in Moodle user

$response = $client->request();

foreach ($response->getHeaders() as $header => $value) {
  header($header . ': ' . $value);
}

// Set response status.
header($response->getMessage(), true, $response->getStatus());

// Print actual data.
print $response->getBody();

exit;

?>
