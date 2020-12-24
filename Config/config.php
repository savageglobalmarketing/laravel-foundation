<?php

return [
    'name' => 'Foundation',

    'frontend' => [
        /*----------------------------------------------------------------
         | App Url
         |----------------------------------------------------------------
         | Front end domain user for consuming app.
         */
        'url' => env('SGM_FRONTEND_URL', 'http://localhost:8080'),

        /*----------------------------------------------------------------
         | App e-mail verification
         |----------------------------------------------------------------
         | Url to handle e-mail verification
         |
         | This url is used e-mail verification message.
         */
        'email_verify_url' => env('SGM_EMAIL_VERIFY_URL', '/verify-email'),

        /*----------------------------------------------------------------
         | App password reset
         |----------------------------------------------------------------
         | Url to handle password redefinition.
         |
         | This is sent on password reset message.
         */
        'reset_url' => env('SGM_RESET_URL', '/reset'),
    ],

    'api' => [
        'prefix' => env('SGM_API_PREFIX', '')
    ],

    'developers' => [
        'taylor@laravel.com',
    ],

    /*----------------------------------------------------------------
     | User Resource
     |----------------------------------------------------------------
     | This package assumes you are using API Resources.
     |
     | Here you must specify the UserResource class that will be used
     | to get Userstamps information.
     */
    'user_resource_class' => SavageGlobalMarketing\Auth\Transformers\UserStampsResource::class,

    /*----------------------------------------------------------------
     | Custom error handler class
     |----------------------------------------------------------------
     | This package ships a custom error handler to solve a problem
     | with QueryException caring Uuid.
     |
     | If you by any reason needs to use a different handler or just
     | stay with the default Laravel handler, specify it here
     */
    'error_handler' => SavageGlobalMarketing\Foundation\Exceptions\Handler::class,

    /*----------------------------------------------------------------
     | Default model fields
     |----------------------------------------------------------------
     | Automatically adds specified fields to the list provided on
     | creating model command.
     */
    'default_model_fields' => [
        // 'unsignedBigInteger:tenant_id',
    ],

    /*----------------------------------------------------------------
     | Policy bypass
     |----------------------------------------------------------------
     | When true, policies will be created with all methods returning
     | true. Useful for testing without ACL setup.
     */
    'bypass_policy' => env('SGM_FOUNDATION_BYPASS_POLICY', false),

    /*----------------------------------------------------------------
     | Pagination setup
     |----------------------------------------------------------------
     | Setup pagination for json:api specs.
     */
    'pagination' => [
        /*
         * The maximum number of results that will be returned
         * when using the JSON API paginator.
         */
        'max_results' => 30,

        /*
         * The default number of results that will be returned
         * when using the JSON API paginator.
         */
        'default_size' => 30,

        /*
         * The key of the page[x] query string parameter for page number.
         */
        'number_parameter' => 'number',

        /*
         * The key of the page[x] query string parameter for page size.
         */
        'size_parameter' => 'size',

        /*
         * The name of the macro that is added to the Eloquent query builder.
         */
        'method_name' => 'jsonPaginate',

        /*
         * Here you can override the base url to be used in the link items.
         */
        'base_url' => '',

        /*
         * The name of the query parameter used for pagination
         */
        'pagination_parameter' => 'page',
    ],


    'upload_disk' => env('FOUNDATION_UPLOAD_DISK', 'local')
];
