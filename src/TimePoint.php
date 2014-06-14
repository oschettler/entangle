<?php

namespace Entangle;

class TimePoint
{
  public $type;
  public $event;
  public $title;

  function __construct($type, $event, $title = NULL) {
    $this->type = $type;
    $this->event = $event;
    $this->title = $title;
  }
}
