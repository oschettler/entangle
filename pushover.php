<?php

require_once 'app/api.php';

/**
 * Send an alert (from NewRelic) via Pushover
 */
on('POST', '/:site', function () {
  error_log(json_encode(params()) . "\n", 3, "/tmp/entangle.log");
  
  $alert = params('alert');
  
  if (!empty($alert)) {
    $api = new API;
    $api->call(config('pushover.api_url'), http_build_query(array(
      'user' => config('pushover.user_key'),
      'token' => config('pushover.app_key'),
      'message' => json_encode($alert),
    )));
  }
});