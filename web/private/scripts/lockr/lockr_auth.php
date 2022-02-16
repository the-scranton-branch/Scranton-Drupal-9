<?php

print("\n==== Start Lockr Auth ====\n");

// Check for Pantheon environment
if (!empty($_ENV['PANTHEON_ENVIRONMENT']) && in_array($_ENV['PANTHEON_ENVIRONMENT'], ['dev'])) {
    // Render Environment name with link to site, <https://{ENV}-{SITENAME}.pantheon.io|{ENV}>
    $url = 'https://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io';

    // Get secrets
    $secrets = json_decode(file_get_contents('https://dev-dunder-mifflin-legacy-site.pantheonsite.io/quicksilver.php?name=scranton-drupal-9'), 1);
    var_dump($secrets);

    // Assign token
    $token = $secrets['lockr']['token'];

    echo "Running Lockr authentication...\n";
    passthru('drush lockr:register-token ' . $token);
    echo "Lockr auth complete.\n";
}
