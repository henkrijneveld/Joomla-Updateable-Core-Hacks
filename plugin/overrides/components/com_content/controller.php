<?php
__loadClassInMemoryAndRename("ContentController", __FILE__, "/");

class ContentController extends OrigContentController
{
  public function __construct($config = array())
  {
    echo "In the overridden one!";
    parent::__construct($config);
  }
}
