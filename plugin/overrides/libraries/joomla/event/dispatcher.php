<?php

__loadClassInMemoryAndRename("JEventDispatcher", __FILE__);

class JEventDispatcher extends OrigJEventDispatcher {
  public function trigger($event, $args = array()) {
    JLog::add($event, JLog::INFO, 'coreoverrides');
    return parent::trigger($event, $args);
  }
}

