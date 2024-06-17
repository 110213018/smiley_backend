<?php
    // Allow from any origin
    header("Access-Control-Allow-Origin: *");
    // Allow the following request methods
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    // Allow the following request headers
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
    $serverHost = "localhost";
    $user = "root";
    $password = "";
    $database = "smiley";

    $connectNow = new mysqli($serverHost, $user, $password, $database);