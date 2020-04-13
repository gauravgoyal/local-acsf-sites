<?php

namespace GauravGoyal\LAS\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Consolidation\AnnotatedCommand\CommandData;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines commands in the "custom" namespace.
 */
class CloneSiteCommand extends BltTasks {

  /**
   * Clone ACSF site to local from target environment.
   *
   * @param string $env
   *   Environment id to use for clonning. For e.g.,  "01live".
   * @param string $sitename
   *   Sitename to clone for e.g., 'example'.
   *
   * @command recipes:acsf:clone:site
   *
   * @aliases racs clone
   *
   * @description Clone ACSF site to local
   */
  public function cloneSite($env, $sitename) {
    if ($sitename == 'default') {
      throw new \RuntimeException('You cannot run this command for the default site.');

    }
    $localEnvUrl = "http://local.$sitename.com";
    $remoteAlias = "$sitename.$env";
    $localAlias = "self";
    $repoRoot = $this->getConfigValue('repo.root');
    $this->say("Starting clonning of site <comment> @$sitename.$env </comment>");
    $this->invokeCommand("recipes:multisite:init", [
      [
        'site-dir' => $sitename,
        'site-uri' => $localEnvUrl,
        'remote-alias' => $remoteAlias,
        'local-alias' => $localAlias,
      ],
    ]);

    $gitIgnoreContent = [];
    $gitIgnoreContent[] = "\n# Ignoring $sitename.";
    $gitIgnoreContent[] = "docroot/sites/$sitename/";
    $gitIgnoreContent[] = "docroot/sites/default/blt.yml";

    $this->say("Adding pre-sites-php hook");
    $fs = new Filesystem();

    $preSitesPhpHook = $repoRoot . '/factory-hooks/pre-sites-php';

    if (!$fs->exists($preSitesPhpHook)) {
      $this->say('Creating pre-sites-php hook to populate sites');
      $this->taskFilesystemStack()
        ->mkdir($preSitesPhpHook)
        ->stopOnFail()
        ->run();
    }
    $this->say('pre-sites-php hook already exists');

    $filename = 'local-multisite.php';
    $localPreSitePhpFile = $preSitesPhpHook . '/' . $filename;
    $sourceMultiSitePhp = "../../../../scripts/$filename";

    if (!$fs->exists($localPreSitePhpFile)) {
      $this->say('Creating $localPreSitePhpFile hook to populate sites data');
      $this->taskFilesystemStack()
        ->copy($sourceMultiSitePhp, $localPreSitePhpFile)
        ->stopOnFail()
        ->run();
      $gitIgnoreContent[] = "\n# Ignoring local multisite php hook.";
      $gitIgnoreContent[] = "factory-hooks/pre-sites-php/$filename";
    }

    $filename = 'local.multisite.yml';
    $localMultisiteConfig = "$repoRoot/blt/local.multisite.yml";
    $sourceMultiSiteConfigPath = "../../../../scripts/$filename";

    if (!$fs->exists($localMultisiteConfig)) {
      $this->say('Creating $localMultisiteConfig file to populate sites data');
      $this->taskFilesystemStack()
        ->copy($sourceMultiSiteConfigPath, $localMultisiteConfig)
        ->stopOnFail()
        ->run();
      $gitIgnoreContent[] = $localMultisiteConfig;
    }

    $data = YamlMunge::parseFile($localMultisiteConfigPath);
    \array_push($data['sites'], $sitename);
    YamlMunge::writeFile($localMultisiteConfigPath, $data);

    $gitIgnoreFile = $repoRoot . '/.gitignore';

    foreach ($gitIgnoreContent as $content) {
      $fs->appendToFile($gitIgnoreFile, $content . "\n");
    }
  }

  /**
   * Validate environment for ACSF clone site command.
   *
   * @hook pre-command recipes:acsf:clone:site
   */
  public function preCommand(CommandData $commandData) {
    $arguments = $commandData->arguments();
    $sitename = $arguments['sitename'];
    $fs = new FileSystem();
    $multisiteDir = $this->getConfigValue('docroot');
    $siteDir = $multisiteDir . ' / sites / default / ' . $sitename;
    if ($fs->exists($siteDir)) {
      throw new RuntimeException('Multisite already exists .');
    }
  }

}
