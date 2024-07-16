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
    private const ENVIRONMENT = 'production';
    private const BASE_URL = 'http://localhost';
    private const DEVELOPMENT_SERVER_BASE_URL = 'http://localhost:5000'; // only http localhost urls
    private const TIMEZONE = 'Asia/Kolkata';


}