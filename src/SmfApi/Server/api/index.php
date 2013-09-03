<?php

/**
 * Simple Machines Forum(SMF) 'REST' API for SMF 2.0
 *
 * Use this to integrate your SMF version 2.0 forum with 3rd party software
 * If you need help using this script or integrating your forum with other
 * software, feel free to contact andre@r2bconcepts.com
 *
 * @package   SMF 2.0 'REST' API
 * @author    Simple Machines http://www.simplemachines.org
 * @author    Andre Nickatina <andre@r2bconcepts.com>
 * @copyright 2011 Simple Machines
 * @link      http://www.simplemachines.org Simple Machines
 * @link      http://www.r2bconcepts.com Red2Black Concepts
 * @license   http://www.simplemachines.org/about/smf/license.php BSD
 * @version   0.1.0
 *
 * NOTICE OF LICENSE
 ***********************************************************************************
 * This file, and ONLY this file is released under the terms of the BSD License.   *
 *                                                                                 *
 * Redistribution and use in source and binary forms, with or without              *
 * modification, are permitted provided that the following conditions are met:     *
 *                                                                                 *
 * Redistributions of source code must retain the above copyright notice, this     *
 * list of conditions and the following disclaimer.                                *
 * Redistributions in binary form must reproduce the above copyright notice, this  *
 * list of conditions and the following disclaimer in the documentation and/or     *
 * other materials provided with the distribution.                                 *
 * Neither the name of Simple Machines LLC nor the names of its contributors may   *
 * be used to endorse or promote products derived from this software without       *
 * specific prior written permission.                                              *
 *                                                                                 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"     *
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE       *
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE      *
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE        *
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR             *
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE *
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)     *
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT      *
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT   *
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. *
 **********************************************************************************/

// set your secret key here
define ('SECRET_KEY', 'Put your secret key here'); 

error_reporting(E_ALL | E_STRICT);

require_once 'SmfRestServer.php';

$restServer = new \SmfApi\Server\SmfRestServer($_REQUEST, SECRET_KEY);
$restServer->run();
