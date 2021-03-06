<?php

// Get Pantheon site metadata
$req = pantheon_curl('https://api.live.getpantheon.com/sites/self/attributes', null, 8443);
$meta = json_decode($req['body'], true);
$title = $meta['label'];
$email = $_POST['user_email'];

// Install from profile.
echo "Installing default profile...\n";
passthru('drush site:install demo_umami --account-mail ' . $email . ' --site-name ' . $title . ' --account-name superuser -y');
echo "Import of configuration complete.\n";

// Clear all cache
echo "Rebuilding cache.\n";
passthru('drush cr');
echo "Rebuilding cache complete.\n";
