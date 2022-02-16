<?php

echo "Enabling Lockr...\n";
passthru('drush en lockr -y');
echo "\n";

echo "Clearing cache...\n";
passthru('drush cr -y');
echo "\n";
