<?php

session_start();

// https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
// if (empty($_SESSION['token'])) {
//     $_SESSION['token'] = bin2hex(random_bytes(32));
// }
// $token = $_SESSION['token'];

// main global variables used within app
$email_address_err = $price_err = $initial_payment_percent_err = $annual_payment_percent_err = $months_err = '';
$email_address = $price = $initial_payment_percent = $annual_payment_percent = $months = '';
$email_address_unsafe = $price_unsafe = $initial_payment_percent_unsafe = $annual_payment_percent_unsafe = $months_unsafe = '';

