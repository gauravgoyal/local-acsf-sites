<?php

/**
 * @file
 * Poulate the local multisite array.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Drupal\Component\Serialization\Yaml;

if (EnvironmentDetector::isLocalEnv()) {
  $multisite_config = __DIR__ . '/../../blt/local.multisite.yml';

  if (file_exists($multisite_config)) {
    $multisite = Yaml::decode(file_get_contents($multisite_config));
    foreach ($multisite['sites'] as $site) {
      $uri = ($site == 'default') ? 'default' : 'local.' . $site . '.com';
      $sites[$uri] = $site;
    }
  }
}
