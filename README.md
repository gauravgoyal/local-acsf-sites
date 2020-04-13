## Clone sites from ACSF to local environment
---------

This is an [Acquia BLT](https://github.com/acquia/blt) plugin providing ACSF multisite integration.

This plugin is **community-created** and **community-supported**. Acquia does not provide any direct support for this software or provide any warranty as to its stability.

## Installation and usage

In your project, require the plugin with Composer:

`composer require gauravgoyal/local-acsf-sites`

Clone a site locally by using below command

`blt recipes:acsf:clone:site @target_env @sitename`

For e.g. to clone a site example from 01live environment, command would be

`blt recipes:acsf:clone:site 01live example`

Run `vagrant up --provision` to create a vhost and database entry.

visit `local.example.com` to checkout the site.

## How does it work?
* Above command uses `recipes:multisite:init` internally to create a new site with new db.
* Multisite directory is ignored in .gitignore file
* Pre Sites PHP hook is used to populate the sites into  site variable
