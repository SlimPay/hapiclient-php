<?php
namespace HapiClient\tests;

use HapiClient\Http;
use HapiClient\Http\Auth;
use HapiClient\Exception;

class WrongCredentialsTest extends \PHPUnit_Framework_TestCase
{
    const APIURL = 'https://api-sandbox.slimpay.net';
    const PROFILEURL = 'https://api.slimpay.net/alps/v1';
    const APPID = 'democreditor01';
    const APPSECRET = 'wrongsecret';
    const SCOPE = 'api';
    
    private $hapiClient;
    
    /**
     * @before
     */
    public function initClient()
    {
        $this->hapiClient = new Http\HapiClient(
            self::APIURL,
            '/',
            self::PROFILEURL,
            new Auth\Oauth2BasicAuthentication(
                '/oauth/token',
                self::APPID,
                self::APPSECRET,
                self::SCOPE
            )
        );
    }

    /**
     * @test
     */
    public function test()
    {
        try {
            $this->hapiClient->getEntryPointResource();
            throw new \Exception('HttpClientErrorException was not raised while using wrong credentials.');
        } catch (Exception\HttpClientErrorException $e) {
            $this->assertEquals(401, $e->getStatusCode());
            $this->assertEquals('Unauthorized', $e->getReasonPhrase());
        }
    }
}
