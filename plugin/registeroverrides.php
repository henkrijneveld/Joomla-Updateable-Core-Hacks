<?php
// registeroverrides
//
// In Joomla, the class overrides have higher priority than namespace and prefix registers
// inspect cms file for possible higher priority core classes when overrides are not activated
//

$isadmin = strpos(JPATH_THEMES, "administrator") !== false; // kludge

if (($isadmin && file_exists(dirname(__FILE__)."/enabledbackend.txt")) ||
  (!$isadmin && file_exists(dirname(__FILE__)."/enabledfrontend.txt"))) {
  JLoader::registerPrefix('J', dirname(__FILE__) . '/overrides/libraries/joomla', false, true);
  JLoader::registerPrefix('J', dirname(__FILE__) . '/overrides/libraries/cms', false, true);
  JLoader::registerNamespace('Joomla\\CMS', dirname(__FILE__) . '/overrides/libraries/src', false, true, 'psr4');

  __registerOverriddenClasses(dirname(__FILE__) . "/overrides/components");
  __registerOverriddenClasses(dirname(__FILE__) . "/overrides/plugins");
  __registerOverriddenClasses(dirname(__FILE__) . "/overrides/modules");
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
  $pathpart = "/coreoverrides/overrides";
  $pos = strpos($overridefile, $pathpart);
  $s = file_get_contents(JPATH_ROOT.substr($overridefile, $pos + strlen($pathpart)));

  $pos = strpos($s, "<?php");
  if ($pos !== false) {
    $s = substr_replace($s, "", $pos, 5);
  }
  $s = preg_replace("/class[ \t]*".$classname."/", "class Orig".$classname, $s, 1);
  eval($s);
}

/**
 *
 * Recursively walk through the tree, inspect every php file for classname, and when found, register it
 *
 * @param string $dir  path to overrides, can contain subdirectories
 */
function __registerOverriddenClasses($dir)
{
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