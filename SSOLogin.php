<?php
require_once("CAS/CAS.php");
require_once("settings.php");
// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

$userid = phpCAS::getUser();
$ttl = 60; //the number of seconds the temp url should live
echo "<p>userid: " . $userid;

header('Location: ' . "index.php");
