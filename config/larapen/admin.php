<?php
/*
 * JobClass - Job Board Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com/jobclass
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

return [
	
    /*
     |--------------------------------------------------------------------------
     | ADMIN - Look & feel customizations
     |--------------------------------------------------------------------------
     |
     */
	
    // Project name. Shown in the breadcrumbs and a few other places.
    'project_name' => 'JobClass',

	// Logo
	'logo' => [
		'dark'  => 'app/default/backend/logo_dark.png',
		'light' => 'app/default/backend/logo_light.png',
	],

	// Logos Text
    'logo_lg'   => '<b>Job</b>Class',
    'logo_mini' => '<b>JBC</b>',
	
    // Developer or company name. Shown in footer.
    'developer_name' => 'BeDigit',
	
    // Developer website. Link in footer.
    'developer_link' => 'https://bedigit.com',
	
    // Show powered by Laravel in the footer?
    'show_powered_by' => true,
	
    // The AdminLTE skin. Affects menu color and primary/secondary colors used throughout the application.
    'skin' => 'skin-purple',
    // Options: skin-black, skin-blue, skin-purple, skin-red, skin-yellow, skin-green, skin-blue-light, skin-black-light, skin-purple-light, skin-green-light, skin-red-light, skin-yellow-light
	
    // Admin toggle navigation
    'toggle_navigation' => 'Toggle Navigation',
	
    // How many items should be shown by default by the Datatable?
    'default_page_length' => 25,
	
	// Where do you want to redirect the user by default, after a CRUD entry is saved in the Add or Edit forms?
	'default_save_action' => 'save_and_back', // options: save_and_back, save_and_edit, save_and_new

	// Don't allow edits on these language files
	// Language files to NOT show in the LangFile Manager
	'language_ignore' => ['.DS_Store', 'routes', 'messages'],

	// Translatable
	'show_translatable_field_icon'     => true,
	'translatable_field_icon_position' => 'right', // left or right
	
	
	/*
    |--------------------------------------------------------------------------
    | Disallow the user interface for creating/updating permissions or roles.
    |--------------------------------------------------------------------------
    | Roles and permissions are used in code by their name
    | - ex: $user->hasPermissionTo('edit articles');
    |
    | So after the developer has entered all permissions and roles, the administrator should either:
    | - not have access to the panels
    | or
    | - creating and updating should be disabled
    */
	
	'allow_permission_create' => true,
	'allow_permission_update' => true,
	'allow_permission_delete' => true,
	'allow_role_create'       => true,
	'allow_role_update'       => true,
	'allow_role_delete'       => true,
	
	
    /*
    |--------------------------------------------------------------------------
    | Registration Open
    |--------------------------------------------------------------------------
    |
    | Choose wether new users are allowed to register.
    | This will show up the Register button in the menu and allow access to the
    | Register functions in AuthController.
    |
    */
	
    'registration_open' => false,
	
	
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    */
	
    // Fully qualified namespace of the User model
    'user_model_fqn' => '\App\Models\User',
	
];
