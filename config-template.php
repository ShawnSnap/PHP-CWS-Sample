<?php
/**
 * This is a configuration file for the sample application.
 *
 * Please note that these values must be provided to you by your Solutions Engineer at EVO Snap*.  Copy this file to
 * "config.php" and fill in the data below.
 *
 * This application will not work without these values.
 */

use Evosnap\Cws\Model\Rest\CwsClientConfig;

// The Identity Token identifies you to EVO Snap*'s platform.
$identityToken = '';
// The Application Profile ID identifies the specific application to EVO Snap*.  This would be different for every
// application you write, but in certification you just reuse the same one.
$applicationProfileId = '';
// The Service ID identifies which service (NGTrans, eServices, Banamex, etc) you are transacting against.  This is
// defined during boarding.
$serviceId = '';
// The Merchant Profile ID is set up during merchant boarding and identifies a specific merchant within your
// application.
$merchantProfileId = '';

$clientConfig = new CwsClientConfig();

$clientConfig->live = false; // This decides which platform to run against:  True is Production, False is Certification.
$clientConfig->proxy = false; // Do you want to activate a proxy, like Charles?  This can be useful during development.
    $clientConfig->proxyHost = '127.0.0.1'; // If you are using a proxy, where is it?
    $clientConfig->proxyPort = 8888;       //  What port is it?
    $clientConfig->trustAll = true;       //   Does it have valid certificates, or should we just trust whatever?