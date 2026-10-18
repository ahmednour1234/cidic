<?php

return [
    'users' => [
        'subtitle' => 'Users are disabled, never deleted, and inherit the branch of whoever created them.',
        'count' => ':count users',
        'add' => 'Add user',
        'name' => 'Name',
        'name_placeholder' => 'User\'s name',
        'email' => 'Email address',
        'password' => 'Password',
        'password_placeholder' => 'At least 8 characters',
        'department' => 'Department',
        'existing' => 'Current users',
        'you' => 'You',
        'active' => 'Active',
        'disabled' => 'Disabled',
        'email_short' => 'Email',
        'new_password' => 'New password',
        'new_password_placeholder' => 'Leave blank to keep the current one',
        'save' => 'Save changes',
        'confirm_disable' => 'Disable this user? They will no longer be able to log in.',
        'confirm_enable' => 'Enable this user?',
        'disable' => 'Disable',
        'enable' => 'Enable',
        'cannot_disable_self' => 'You cannot disable your own account',
        'empty' => 'No users yet.',
    ],

    'coordinators' => [
        'subtitle' => 'The nationalities linked to a coordinator decide what they see, upload and delete.',
        'count' => ':count nationalities',
        'public_page' => 'Public page',
        'save' => 'Save nationalities',
        'empty' => 'No active coordinators.',
    ],

    'departments' => [
        'coordination' => 'Coordination',
        'customer_service' => 'Customer service',
        'branch_manager' => 'Branch manager',
    ],

    'flash' => [
        'created' => 'User created.',
        'updated' => 'User updated.',
        'cannot_disable_self' => 'You cannot disable your own account.',
        'enabled' => 'User enabled.',
        'disabled' => 'User disabled.',
        'nationalities_updated' => 'Coordinator nationalities updated.',
    ],
];
