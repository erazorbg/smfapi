Simple Machines Forum(SMF) 'REST' API for SMF 2.0
====================================================

**Note:** This is a fork of the SMF REST API by Andre Nickatina. Our intention is to upgrade and maintain the API.

Use this to integrate your SMF version 2.0 forum with 3rd party software
If you need help using this script or integrating your forum with other
software, feel free to contact andre@r2bconcepts.com

 * @package   SMF 2.0 'REST' API
 * @author    Simple Machines http://www.simplemachines.org
 * @author    Andre Nickatina <andre@r2bconcepts.com>
 * @copyright 2011 Simple Machines
 * @link      http://www.simplemachines.org Simple Machines
 * @link      http://www.r2bconcepts.com Red2Black Concepts
 * @license   http://www.simplemachines.org/about/smf/license.php BSD
 * @version   0.1.2

## Installation with [composer](http://getcomposer.org)
 
 add this to your composer.json file:
``` json
"require": 
{
        "pc-freak/smfapi": "dev-master"
},
"repositories": 
[
	{
            "type": "vcs",
            "url" : "https://github.com/pc-freak/smapi.git"
	}
]

```

run 

``` bash

php composer update

```

## Configuration

### 1. Upload the content of the Server folder on your SMF installation folder
### 2. __(optional)__ Change the path to your Settings.php
``` php 

//path/to/your/smf/api/folder/smf_2_api.php

// manually add the location of your Settings.php here
if (!isset($settings_path) || empty($settings_path)) {
    // specify the settings path here if it's not in smf root and you want to speed things up
    // $settings_path = $_SERVER['DOCUMENT_ROOT'] . /path/to/Settings.php
    if (isset($settings_path) && !file_exists($settings_path)) {
        unset($settings_path);
    }
}

```

### 3. __(optional)__ In case you use nginx as webserver configure it to emulate the .htaccess

``` bash
Satisfy Any

RewriteEngine on

RewriteRule .* index.php [L]
 
```
### 4. set your api key in the SmfRestServer

``` php 
define ('SECRET_KEY', 'Put your secret key here'); // set your secret key here
```

## Usage

``` php

<?php

$client = new SmfApi\Client\SmfRestClient($apiServerUrl, $secretKey);

```

 
