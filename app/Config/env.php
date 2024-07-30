<?php

namespace App\Config;

/**
 * Constants of this file must be private.
 * 
 * This call is for defining constant variables, which will be used if the variable doesnt defined in the .env file or .env file is missing.
 * 
 * The data from this file will be put inside PHP Environment.
 * 
 * The data fron .env file overrides the data from this file.
 * 
 * Do not use environment variables here, it will return null as environment variable will not be set when core app uses this class.
 */
class env
{
    public const array VARS = [


        'ENVIRONMENT' => 'production',


        'BASE_URL' => 1,


        'DEVELOPMENT_SERVER_BASE_URL' => 'http://localhost:5000',


        'TIMEZONE' => 'Asia/Kolkata',



        'session.cookie_name' => 'cosmic_session',


        'security.csrf.token_name' => 'cosmic_token',
        'security.csrf.expire' => 0, // seconds  // 0 means unlimited
        'security.csrf.regenerate_token' => false,
        'security.csrf.redirect_back_on_failure' => false,



    ];
}