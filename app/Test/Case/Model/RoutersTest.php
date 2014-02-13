<?php
App::uses('Routers', 'Model');

/**
 * Systemevent Test Case
 *
 */
class RoutersTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.routers'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Routers = ClassRegistry::init('Routers');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Routers);

		parent::tearDown();
	}

}
