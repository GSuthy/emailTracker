<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
    public $helpers = array('Js' => array('JQuery'));
	public $components = array('Auth', 'DebugKit.Toolbar');

	public function beforeFilter() {
		//Force authentication to get to the page
		$this->Auth->authenticate = array('Cas');
    
        $this->set('authUser', $this->Auth->user());

        $userInfo = $this->Auth->user();
        //Only uncomnment the line below if working on localhost
        // $userInfo["memberOf"] = "infra_communication";
        $userRoles = explode(',', $userInfo['memberOf']);
        $this->set('authorized', in_array("infra_communication", $userRoles) || in_array("EAMP", $userRoles) || in_array("csr01", $userRoles) || in_array("csce", $userRoles));
        $this->set('queues_authorized', in_array("infra_communication", $userRoles) || in_array("csr01", $userRoles) || in_array("csce", $userRoles));
	}

    public function isAuthorizedQueues() {
        $this->Auth->authenticate = array('Cas');
       
        $userInfo = $this->Auth->user();
        // Only uncomment the line below if working on localhost
        // $userInfo["memberOf"] = "infra_communication";
        $userRoles = explode(',', $userInfo['memberOf']);
        return in_array("infra_communication", $userRoles) || in_array("csr01", $userRoles) || in_array("csce", $userRoles);
    }
}
