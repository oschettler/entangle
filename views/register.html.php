<?php 
echo partial('edit_profile', array(
  'page_title' => 'Register account',
  'action' => '/user/register',
  'user' => (object)array(
    'username' => '',
    'email' => '',
    'realname' => '',
  )
)); 