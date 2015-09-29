<?php
namespace HapiClient\Tests;

use HapiClient\Http;
use HapiClient\Http\Auth;
use HapiClient\Hal;
use HapiClient\Exception;

class ClientTest extends \PHPUnit_Framework_TestCase {
	const APIURL = 'https://api-sandbox.slimpay.net';
	const PROFILEURL = 'https://api.slimpay.net/alps/v1';
	const APPID = 'democreditor01';
	const APPSECRET = 'demosecret01';
	const CREDITOR_REFERENCE = 'democreditor';
	const SCOPE = 'api';
	const REL_NS = 'https://api.slimpay.net/alps#';
	
	private $hapiClient;
	
	/**
	 * @before
	 */
    public function initClient() {
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
	public function oneFollowWithGet() {
		// Follow the get-creditors link
		$rel = new Hal\CustomRel(self::REL_NS . 'get-creditors');
		$follow = new Http\Follow($rel, 'GET', ['reference' => self::CREDITOR_REFERENCE]);
		$creditor = $this->hapiClient->sendFollow($follow);
		
		$this->assertEquals(self::CREDITOR_REFERENCE, $creditor->getState()['reference']);
	}

	/**
	 * @test
	 */
	public function twoFollowsWithGet() {
		// Follow the get-creditors then get-mandates links
		$rel = new Hal\CustomRel(self::REL_NS . 'get-creditors');
		$follow1 = new Http\Follow($rel, 'GET', ['reference' => self::CREDITOR_REFERENCE]);
		
		$rel = new Hal\CustomRel(self::REL_NS . 'get-mandates');
		$follow2 = new Http\Follow($rel, 'GET', ['rum' => '1']);
		
		$mandate = $this->hapiClient->sendFollow([$follow1, $follow2]);
		
		$this->assertEquals('1', $mandate->getState()['rum']);
	}

	/**
	 * @test
	 */
	public function oneWrongFollowWithGet() {
		// Follow the get-creditors link
		$rel = new Hal\CustomRel(self::REL_NS . 'get-creditors');
		$follow = new Http\Follow($rel, 'GET', ['reference' => 'noaccesstothiscreditor']);
		
		try {
			$this->hapiClient->sendFollow($follow);
		} catch (Exception\HttpClientErrorException $e) {
			$this->assertEquals(403, $e->getStatusCode());
			$this->assertEquals('Forbidden', $e->getReasonPhrase());
		}
	}

	/**
	 * @test
	 */
	public function refreshResource() {
		// Follow the get-creditors link
		$rel = new Hal\CustomRel(self::REL_NS . 'get-creditors');
		$follow = new Http\Follow($rel, 'GET', ['reference' => self::CREDITOR_REFERENCE]);
		$creditor = $this->hapiClient->sendFollow($follow);
		
		// Create the same Resource but with no state
		// to simulate a "partial embedded resource".
		$creditorToRefresh = new Hal\Resource(
			[],
			$creditor->getAllLinks(),
			$creditor->getAllEmbeddedResources()
		);
		$this->assertNotEquals($creditor, $creditorToRefresh);
		
		// Try to "refresh" it.
		$refreshedCreditor = $this->hapiClient->refresh($creditorToRefresh);
		
		// Compare the original creditor with the refreshed one
		$this->assertEquals($creditor, $refreshedCreditor);
	}
}