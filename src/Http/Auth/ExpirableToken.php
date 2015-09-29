<?php
namespace HapiClient\Http\Auth;

class ExpirableToken {
	private $value;
	private $expirationTime;
	
	/**
	 * @param $value			The token value
	 * @param $expirationTime	The token expiration timestamp
	 */
	public function __construct($value, $expirationTime) {
		$this->value = trim($value);
		$this->expirationTime = (int) $expirationTime;
	}

	/**
	 * @return	The token value
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return	The token expiration timestamp
	 */
	public function getExpirationTime() {
		return $this->expirationTime;
	}
	
	/**
	 * The magic setter is overridden to insure immutability.
	 */
    public function __set($name, $value) { }
	
	/**
	 * Checks if the token is still valid until the given time limit.
	 * @param $timeLimit	The timestamp representation of the limit
	 * @return boolean
	 */
	public function isValidUntil($timeLimit) {
		return !empty($this->value) && $this->expirationTime > $timeLimit;
	}
}
