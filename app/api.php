<?php

/**
 * Base class for HTTP/JSON-based APIs
 */
class API {
  var $user_agent = 'entangle';

  /**
   * Call REST API, using file_get_contents 
   * and a stream_context for POST data or additional headers.
   *
   * Does not use CURL as libcurl has disabled https for PUT
   * in many installations.
   */
  function call($url, $post=FALSE, $headers=array()) {
    $context_opt = array();
  
    if (strpos($url, ' ') !== FALSE) {
      list($verb, $url) = explode(' ', $url);
    }
    else {
      $verb = 'GET';
    }
   
    //$scheme = parse_url($url, PHP_URL_SCHEME);
    $scheme = 'http'; // This needs to be "http" even for "https"
    $context_opt = array($scheme => array()); 
  
    if ($post) {
      $context_opt[$scheme] = array(
        'method' => 'POST',
        'content' => json_encode($post),
      );
      $headers[] = 'Content-Type: application/json';
    }
    
    if ($verb != 'GET') {
      $context_opt[$scheme]['method'] = $verb;
    }
  
    $headers[] = 'Accept: application/json';
    $headers[] = 'User-Agent: ' . $this->user_agent;
   
    if(!empty($_SESSION['access_token'])) {
      $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
    }
   
    $context_opt[$scheme] += array(
      'header' => join('', array_map(function ($h) {
        return "$h\r\n";
      }, $headers))
    );
   
    $context = stream_context_create($context_opt);
    return json_decode(file_get_contents($url, /*include_path*/FALSE, $context));
  }

}
