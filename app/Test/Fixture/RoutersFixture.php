<?php
/**
 * SystemeventFixture
 *
 */
class RoutersFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'ID' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
		'CustomerID' => array('type' => 'biginteger', 'null' => true, 'default' => null),
		'ReceivedAt' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'DeviceReportedTime' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'Facility' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 6),
		'Priority' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 6),
		'FromHost' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 60, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'Message' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'NTSeverity' => array('type' => 'integer', 'null' => true, 'default' => null),
		'Importance' => array('type' => 'integer', 'null' => true, 'default' => null),
		'EventSource' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 60, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'EventUser' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 60, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'EventCategory' => array('type' => 'integer', 'null' => true, 'default' => null),
		'EventID' => array('type' => 'integer', 'null' => true, 'default' => null),
		'EventBinaryData' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'MaxAvailable' => array('type' => 'integer', 'null' => true, 'default' => null),
		'CurrUsage' => array('type' => 'integer', 'null' => true, 'default' => null),
		'MinUsage' => array('type' => 'integer', 'null' => true, 'default' => null),
		'MaxUsage' => array('type' => 'integer', 'null' => true, 'default' => null),
		'InfoUnitID' => array('type' => 'integer', 'null' => true, 'default' => null),
		'SysLogTag' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 60, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'EventLogType' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 60, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'GenericFileName' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 60, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'SystemID' => array('type' => 'integer', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'ID', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'ID' => 1,
			'CustomerID' => '',
			'ReceivedAt' => '2014-02-11 01:50:52',
			'DeviceReportedTime' => '2014-02-11 01:50:52',
			'Facility' => 1,
			'Priority' => 1,
			'FromHost' => 'Lorem ipsum dolor sit amet',
			'Message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'NTSeverity' => 1,
			'Importance' => 1,
			'EventSource' => 'Lorem ipsum dolor sit amet',
			'EventUser' => 'Lorem ipsum dolor sit amet',
			'EventCategory' => 1,
			'EventID' => 1,
			'EventBinaryData' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'MaxAvailable' => 1,
			'CurrUsage' => 1,
			'MinUsage' => 1,
			'MaxUsage' => 1,
			'InfoUnitID' => 1,
			'SysLogTag' => 'Lorem ipsum dolor sit amet',
			'EventLogType' => 'Lorem ipsum dolor sit amet',
			'GenericFileName' => 'Lorem ipsum dolor sit amet',
			'SystemID' => 1
		),
	);

}
