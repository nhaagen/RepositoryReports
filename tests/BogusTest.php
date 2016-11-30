<?php

/**
 * Sample for PHP Unit tests
 */
class BogusTest extends PHPUnit_Framework_TestCase {

	public function test_successfull() {
		$test_var = "Peter";

		$this->assertEqual("Peter", $test_var);
	}

	public function test_failed() {
		try {
			$this->checkValue("Bernd");
			$this->assertFalse("Should have raised.");
		}
		catch (Exception $e) {}
	}

	protected function checkValue($value) {
		if($value != "Peter") {
			throw new Exception("Value is wrong");
		}
	}
}