<?php
App::uses('AppModel', 'Model');

/**
 * Class Routers
 * This model class is used to interface with the routers table referenced in the database.php file.
 * Class methods are called from RoutersController.php
 */

class GatewayQueues extends AppModel {
    public $name = 'GatewayQueues';
    public $useDbConfig = 'gatewayqueues';
    public $useTable = 'gatewayqueues';
	public $primaryKey = 'server';
}

