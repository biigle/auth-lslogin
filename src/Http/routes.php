<?php

$router->get('auth/lslogin/redirect', [
   'as'   => 'lslogin-redirect',
   'uses' => 'LSLoginController@redirect',
]);

$router->get('auth/lslogin/callback', [
   'as'   => 'lslogin-callback',
   'uses' => 'LSLoginController@callback',
]);
