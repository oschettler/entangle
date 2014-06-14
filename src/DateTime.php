<?php

namespace Entangle;

class DateTime
{
  private $date;

  function __construct($date) {
    if (is_object($date)) {
      $this->date = $date;
    }
    else {
      if (preg_match('/^\d{4}$/', $date)) {
        $date .= '-01-01';
      }
      else
      if (preg_match('/^\d{4}-\d{2}$/', $date)) {
        $date .= '-01';
      }

      $this->date = new \DateTime($date);
    }
  }

  function add($interval) {
    $result = clone($this->date);
    return new DateTime($result->add($interval));
  }

  function format($format) {
    return $this->date->format($format);
  }

  function diff($datetime) {
    $result = clone($this->date);
    return new DateTime($result->diff($datetime->date()));
  }

  function date() {
    return $this->date;
  }

  function __get($name) {
    if (in_array($name, ['y', 'm', 'd'])) {
      return $this->date->{$name};
    }
    return NULL;
  }
}
