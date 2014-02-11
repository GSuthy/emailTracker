<?php
App::uses('Systemevent', 'Model');

/**
 * Systemevent Test Case
 *
 */
class SystemeventTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.systemevent'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Systemevent = ClassRegistry::init('Systemevent');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Systemevent);

		parent::tearDown();
	}

}
