<?php

/*
 * permission authentication is dependant on api authentication
 * so to enable permission authentication both (enable_api_auth AND enable_permissions_auth) should be set to true
 *
 * but to disable API authentication you can simply set enable_api_auth=true
 *
 * */

return [
    'name'                    => 'User',
    'enable_api_auth'         => true,
    'enable_permissions_auth' => false,
    'enable_permissions_auth' => false,
    'super_admin_role'        => ''
];
