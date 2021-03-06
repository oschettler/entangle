<?php
/**
 * entangle! Connected timelines on the web
 *
 * Copyright (C) 2014 Olav Schettler https://entangle.de
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */  

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