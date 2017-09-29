<?php
/**
 * Core overrides plugin for Joomla
 *
 * @author     Henk Rijneveld (henk@henkrijneveld.nl)
 * @license    GNU Public License version 3 or later
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');


/**
 * Class PlgSystemCoreOverrides
 *
 * @since  September 2014
 */

class PlgSystemCoreOverrides extends JPlugin
{

  public $logline = true; // if true loglines are default written. Can be overridden with logline GET paramater.
  public $logdisplay = true; // if true a notice is placed on the html output. Can be overridden with logdisplay GET parameter.

  protected $comment = "// File modified by coreoverrides plugin";

  /**
   * Constructor.
   *
   * @param   object &$subject The object to observe.
   * @param   array $config An optional associative array of configuration settings.
   */
  public function __construct(& $subject, $config)
  {
    parent::__construct($subject, $config);

    $this->verifyEnabledStatus("backend");
    $this->verifyEnabledStatus("frontend");
    $this->setLogger();
    $this->hackcms();
  }

  protected function verifyEnabledStatus($app)
  {
    $enabledparameter = ($this->params->get("enabled_$app", 0) == 1);
    $enabledfilename = dirname(__FILE__)."/enabled$app.txt";
    $enabledfile = file_exists($enabledfilename);

    if ($enabledparameter == $enabledfile) return;

    if ($enabledparameter) {
      file_put_contents($enabledfilename, "coreoverrides enabled in $app");
    } else {
      unlink($enabledfilename);
    }


    if ($app == "backend" && JFactory::getApplication()->isClient('administrator')) {
      JFactory::getApplication()->redirect(JUri::current());
    }

    if ($app == "frontend" && JFactory::getApplication()->isClient('site')) {
      JFactory::getApplication()->redirect(JUri::current());
    }
  }

  protected function hackcms()
  {
    $cmssource = file_get_contents(JPATH_LIBRARIES . '/cms.php');

    if (strstr($cmssource, "/coreoverrides/") !== false) {
      return;
    }

    $registeroverridefile = JPATH_ROOT.'/plugins/system/coreoverrides/registeroverrides.php';
    if (!file_exists($registeroverridefile)) {
      if (JFactory::getApplication()->isClient('administrator')) {
        JFactory::getApplication()->enqueueMessage(JText::_("Override not installed. No registeroverrides.php found"), 'warning');
      }
      return;
    }


    $backupfile = JPATH_LIBRARIES . '/cms.'.JVERSION.'.php';
    if (!file_exists($backupfile) && !copy(JPATH_LIBRARIES . '/cms.php', $backupfile)) {
      $this->logaline("Copy of cms.php failed. No core overrides available", "error");
      return;
    }

    $linesin = preg_split("/\r\n|\n|\r/", $cmssource);

    foreach ($linesin as &$linein) {
      if (strstr($linein, "<?php") !== false) {
        $linein .= PHP_EOL.$this->comment . PHP_EOL;
        break;
      }
    }

    $registeroverride = "JPATH_ROOT.'/plugins/system/coreoverrides/registeroverrides.php'";

    array_push($linesin, <<<EOT
//code inserted by coreoverrides plugin    
if (file_exists($registeroverride)) {
  require_once $registeroverride;    
}
EOT
  );

    file_put_contents(JPATH_LIBRARIES . '/cms.php', implode(PHP_EOL, $linesin));

    JFactory::getApplication()->redirect(JUri::current());
  }

  protected function setLogger()
  {
    $this->logdisplay = JRequest::getBool('logdisplay', $this->logdisplay);

    jimport('joomla.log.log');

    JLOG::addLogger(
      array(
        'text_file' => 'coreoverrides.txt'
      ),
      JLog::ALL,
      array('coreoverrides')
    );
  }

  /**
   * Method to log and/or display a logline
   *
   * @param   string  $event  Name of the event
   *
   */
  protected function logaline($event, $type = 'notice')
  {
    if ($this->logdisplay) {
      JFactory::getApplication()->enqueueMessage(JText::_($event), $type);
    }

    if (!$this->logline) return;

    $event = '[' . $this->getid() . '] ' . $event;

    JLog::add($event, JLog::WARNING, 'coreoverrides');
  }


  /**
   * Method to construct an id for quick reference in logfile
   *
   * @param   string  $event  Name of the event
   *
   * @return null
   */
  protected function getid()
  {
    $request = null;

    if (isset($_SERVER['REQUEST_URI']))
    {
      $request .= $_SERVER['REQUEST_URI'];
    }

    if (isset($_SERVER['HTTP_HOST']))
    {
      $request .= $_SERVER['HTTP_HOST'];
    }

    if (isset($_SERVER['REMOTE_ADDR']))
    {
      $request .= $_SERVER['REMOTE_ADDR'];
    }

    return(substr(md5($request), 0, 5));
  }
}
