<?php

require(getenv('DOCROOT') . 'private/scripts/vendor/autoload.php');

use Entrecore\GTMetrixClient\GTMetrixClient;
use Entrecore\GTMetrixClient\GTMetrixTest;

print("\n==== Start GTMetrix Report ====\n");
$text = "No results.";

// Check for Pantheon environment
if (!empty($_ENV['PANTHEON_ENVIRONMENT']) && in_array($_ENV['PANTHEON_ENVIRONMENT'], ['test', 'live'])) {
  // Render Environment name with link to site, <https://{ENV}-{SITENAME}.pantheon.io|{ENV}>
  $url = 'https://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io';

  // Get secrets
  $secrets = json_decode(file_get_contents('https://dev-dunder-mifflin-legacy-site.pantheonsite.io/quicksilver.php?name=scranton-drupal-9'), 1);
  var_dump($secrets);

  $client = new GTMetrixClient();
  $client->setUsername($secrets['gtmetrix']['username']);
  $client->setAPIKey($secrets['gtmetrix']['apikey']);

  $client->getLocations();
  $client->getBrowsers();
  $test = $client->startTest($url);

  // Wait for result
  while (
    $test->getState() != GTMetrixTest::STATE_COMPLETED &&
    $test->getState() != GTMetrixTest::STATE_ERROR
  ) {
    $client->getTestStatus($test);
    sleep(5);
  }

  function scoreThreshold($num)
  {
    $emoji = ":red-flag:";
    switch ($num) {
      case $num >= 85:
        $emoji = ":check:";
        break;
      case $num >= 70:
        $emoji = ":warning:";
        break;
    }
    return $emoji;
  }


  // Gather data
  $gtmetrix = [
    'reportUrl' => $test->getReportUrl(),
    'pagespeedScore' => $test->getPagespeedScore(),
    'pagespeedIcon' => scoreThreshold($test->getPagespeedScore()),
    'yslowScore' => $test->getYslowScore(),
    'yslowIcon' => scoreThreshold($test->getYslowScore()),
  ];

  var_dump($gtmetrix);

  $text = <<<LMAO
    - Report URL: {$gtmetrix['reportUrl']}
    - PageSpeed Score ({$gtmetrix['pagespeedIcon']}): {$gtmetrix['pagespeedScore']}
    - YSlow Score ({$gtmetrix['yslowIcon']}): {$gtmetrix['yslowScore']}
  LMAO;
}

/**
 * Send a notification to slack
 */
$post = [
  "Site" => $_ENV['PANTHEON_SITE_NAME'],
  "Dashboard Link" => 'https://dashboard.pantheon.io/sites/' . $_ENV['PANTHEON_SITE'] . '#' . $_ENV['PANTHEON_ENVIRONMENT'] . '/deploys',
  "Environment" => $_ENV['PANTHEON_ENVIRONMENT'],
  "Description" => $text,
];

// Initiate request
$payload = json_encode($post);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $secrets['gtmetrix']['slack_url']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Watch for messages with `terminus workflows watch --site=SITENAME`
print("\n==== Posting to Slack ====\n");
$result = curl_exec($ch);
print("RESULT: $result");
// $payload_pretty = json_encode($post,JSON_PRETTY_PRINT); // Uncomment to debug JSON
// print("JSON: $payload_pretty"); // Uncomment to Debug JSON
print("\n===== Post Complete! =====\n");
curl_close($ch);



//   $client->attach([
//     'fallback' => 'GTMetrix Page Performance',
//     'text' => 'GTMetrix Page Performance',
//     'color' => 'success',
//     'fields' => [
//       [
//         'title' => 'Pagespeed Score',
//         'value' => $pagespeedScore,
//       ],
//       [
//         'title' => 'YSlow Score',
//         'value' => $yslowScore,
//       ]
//     ]
//   ])->attach([
//     'fallback' => 'Report URL: ' . $reportUrl,
//     'text' => 'Report URL: ' . $reportUrl,
//     'color' => 'success',
//     'mrkdwn_in' => ['text']
//   ])->send('GTMetrix Report: ' . $_ENV['PANTHEON_SITE_NAME']);
// }

print("\n==== End GTMetrix Report ====\n");
