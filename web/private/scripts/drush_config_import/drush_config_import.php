<?php

// Import all config changes.
echo "Importing configuration from yml files...\n";
passthru('drush config-import -y');
echo "Import of configuration complete.\n";

// If using Config Split, you can target by environment and config.
if (defined('PANTHEON_ENVIRONMENT')) {
  switch (PANTHEON_ENVIRONMENT) {
    case 'test':
    case 'live':
      passthru('drush config-split-import live -y');
      break;
    default:
      passthru('drush config-split-import dev -y');
      break;
  }
}

// Clear all cache
echo "Rebuilding cache.\n";
passthru('drush cr');
echo "Rebuilding cache complete.\n";
