<?php
class LoginController extends AppController {
	public function index() {
		App::import('Vendor', 'phpCAS', array('file' => 'CAS/CAS.php'));
	}
}