<?php
App::uses('AppController', 'Controller');

class UsersController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('logout');
	}

	public function login() {
		if ($this->Auth->login()) {
			//Make sure the user gets redirected
            $authRedirect = $this->Auth->redirectUrl();
			if (!empty($authRedirect) && $authRedirect != '/') {
				return $this->redirect($authRedirect);
			}
			else {
				$this->redirect(array('controller'=>'search', 'action'=>'index'));
			}
		} 
		else {
			$this->Session->setFlash(__('Invalid username or password, try again'));
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}
}