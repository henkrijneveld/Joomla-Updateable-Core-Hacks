# Joomla-Updateable-Core-Hacks
Probably not as it is meant to be, but this plugin will enable you to create updateable corehacks, well sort of. Will for sure need some tweaking with release 3.9. Made as a Proof Of Concept for the use of the Joomla! autoloaders.
## Purpose
Sometimes you simply have to make a core hack. Mostly, it will be in the component or module
files of a 3rd party supplier. And sometimes, it has to be made in the core itself.
Ofcourse, the problem with these hacks are twofold:
1. They are not updateable, you will lose your changes when a new Joomla or component update is installled
2. You operate beyond the public and published "stable" interface. So you never know when your adaptations will break.

This plugin solves problem 1 (somewhat), but not 2 (which is totally impossible).
As a bonus, you can activate- and deactiver your corehacks with a single button!

## History
This plugin started as a way to learn the Joomla! autoloader, and the changes that are made to facilitate the new J4.

## In case of emergency
It is always possible things go wrong, and you are locked out of your site in the 
frontend and the backend. In that case, if the fault lies within this plugin, the following
two actions combined will restore your site:
1. Go to the /libraries directory, and copy the cms[joomlaversionnumber].php file to cms.php, for example: cp cms3.8.0.php cms.php
2. Go to the /plugins/system/coreoverrides directory, and rename the registeroverridesfile.php to registeroverrides.php.bak

## Inner working
The overrides are loaded by registering the overriding files in the autoloader. These files
reside in the overrides directory of the plugin. As the "normal" overrides, you just
copy the directory structure and the file.

There are, however, two ways to override a corefile. The first one is the same as
the normal way: copy the whole file, and alter it to your desire.

Sometimes, however, this file can be quite large and you only want to make one small
adjustment (like logging al events in the JPlugin class). In that case, you can use the
method as shown in overrides/libraries/joomla/event/dispatcher.php. The __loadClassInMemoryAndRename("JEventDispatcher", __ FILE __) function
will define the orginal classname as <orig>Classname, so you can extend it. Only issue here:
as this old class is eval'd, it cannot be debugged with the normal debugger. Your extended class will be debuggable, however.

## Altering the cms.php
The first moment you can change the core is in a system plugin. At that moment, a lot of core files are
already loaded and cannot be changed any more. To circumvent this problem, a change to cms.php must be made.
The plugin will check if the cms is changed, and if not, will change it automatically. A backup will be written to
the same directory with the name cms<versionnumber>.php

## Activating and deactivating the plugin
WHen in cms.php the overrides are loaded, the database connection is still not set up.
As I don't like to make an extra database connection, a workaround is implemented. The netresult
is that the enabled state is not know. Deactivating the plugin will have no effect.
The correct way to disable or enable the plugin is to use the plugin parameters for the frontend en backend.


## Installation
Make a zip file of the plugin directory, and use the Joomla installer,

 or

Copy the plugin directory to the plugins/system/coreoverrides directory and do a discover

The first method is preferred.

## Upgrade
Backup your overrides directory. (Re-)Install the plugin. Copy the backed-up overrides directory in the plugin.

## Caveats
1. This plugin is not battle tested, and meant for educational use only. Use in other circumstance at your own peril.
2. The overrides are part of the plugin directory. This means: when you uninstall this plugin, your overrides are gone also. So, make a backup before uninstalling!
3. It is advised not to override the backend when not necessary. 
4. Upgrading is as of yet not automatic
5. Only php files are supported
6. In principle it should be possible to override classes of 3rd party component suppliers. However,
a lot of them use direct require_once's, so the it cant be overridden with the autoloader.


