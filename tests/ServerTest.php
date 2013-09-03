<?php

class SlimTest extends PHPUnit_Framework_TestCase {

    public function setUp() {


    }

	/************************************************
	 * INSTANTIATION
	 ************************************************/
    
    public function testWrongKey() {

    	$request = array(
    		'secretKey' => 'secrettestkey'
    		);

    	// INVALID
		$restServer = new \SmfApi\Server\SmfRestServer($request, null);
		$this->assertFalse($restServer->validateSecretKey());
		// VALID
    	$restServer = new \SmfApi\Server\SmfRestServer($request, 'secrettestkey');
    	$this->assertTrue($restServer->validateSecretKey());

    }
}

?>