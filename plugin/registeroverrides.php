<?php
// registeroverrides
//
// In Joomla, the class overrides have higher priority than namespace and prefix registers
// inspect cms file for possible higher priority core classes when overrides are not activated
//

$isadmin = strpos(JPATH_THEMES, "administrator") !== false; // kludge

$s = JPATH_ROOT."/plugins/system/coreoverrides";

if (($isadmin && file_exists($s."/enabledbackend.txt")) ||
  (!$isadmin && file_exists($s."/enabledfrontend.txt"))) {

  __registerOverriddenPrefix('J', $s . '/overrides/libraries/joomla');
  __registerOverriddenPrefix('J', $s . '/overrides/libraries/cms');
  __registerOverriddenNamespace('Joomla\\CMS', dirname(__FILE__) . '/overrides/libraries/src');
  __registerOverriddenClasses($s . "/overrides/components");
  __registerOverriddenClasses($s . "/overrides/administrator/components");
  __registerOverriddenClasses($s . "/overrides/plugins");
  __registerOverriddenClasses($s . "/overrides/modules");
}

//
// helper functions
//

/**
 * Function to rename original class to Orig<original class name> so it can be extended with yout hack,
 * and still be updateable.
 *
 * @param string $classname      Name of the class to be overridden
 * @param string $overridefile   File with the overidden file
 */
function __loadClassInMemoryAndRename($classname, $overridefile)
{
  $pathpart = "/overrides";
  $pos = strpos($overridefile, $pathpart);
  $s = file_get_contents(JPATH_ROOT.substr($overridefile, $pos + strlen($pathpart)));

  $pos = strpos($s, "<?php");
  if ($pos !== false) {
    $s = substr_replace($s, "", $pos, 5);
  }
  $s = preg_replace("/class[ \t]*".$classname."/", "class Orig".$classname, $s, 1);
  eval($s);
}

function __registerOverriddenPrefix($prefix, $dir)
{
  if (!file_exists($dir)) return;
  JLoader::registerPrefix($prefix, $dir, false, true);
}

function __registerOverriddenNamespace($namespace, $dir)
{
  if (!file_exists($dir)) return;
  JLoader::registerNamespace($namespace, $dir, false, true, 'psr4');
}


/**
 *
 * Recursively walk through the tree, inspect every php file for classname, and when found, register it
 *
 * @param string $dir  path to overrides, can contain subdirectories
 */
function __registerOverriddenClasses($dir)
{
  if (!file_exists($dir)) return;
  $content = scandir($dir);
  if ($content) {
    foreach ($content as $path) {
      if ($path == "." || $path == "..") {
        continue;
      }
      $fullpath = $dir."/".$path;
      if (is_dir($fullpath)) {
        __registerOverriddenClasses($fullpath);
      }
      if (strpos($path, ".php") != false) {
        // method to determine classname is not foolproof
        // alternative is to use token_get_all, but seems overkill
        $phpfile = file_get_contents($fullpath);
        preg_match("/(?:\n|\n\r|\r)(?:abstract |)class (\w*)/", $phpfile, $matches);
        if (is_array($matches)) {
          $classname = $matches[1];
          JLoader::register($classname,  $fullpath);
        }
      }
    }
  }
}