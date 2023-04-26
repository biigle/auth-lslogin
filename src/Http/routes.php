<?php

$router->get('auth/lslogin/redirect', [
   'as'   => 'lslogin-redirect',
   'uses' => 'LSLoginController@redirect',
]);

$router->get('auth/lslogin/callback', [
   'as'   => 'lslogin-callback',
   'uses' => 'LSLoginController@callback',
]);

$router->get('auth/lslogin/register', [
   'as'   => 'lslogin-register-form',
   'uses' => 'RegisterController@showRegistrationForm',
]);

$router->post('auth/lslogin/register', [
   'as'   => 'lslogin-register',
   'uses' => 'RegisterController@register',
]);
