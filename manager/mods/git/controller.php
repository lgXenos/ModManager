<?php

/**
 * класс обработки гит-фронтэнда
 */
class gitActionController {

	public $gitV = false;
	public $gitM = false;

	/**
	 * поиск и выполнение заказанного метода из текущего view
	 * 
	 */
	public function __construct() {

		$_do = myRoute::getRequest('_do', 'str', 'getIndexPage');
		$this->gitV = new gitActionView();
		$this->gitM = new gitActionModel();

		if (method_exists($this, $_do)) {
			$this->$_do();
		} else {
			$this->gitV->renderError('undefined ' . $_do);
		}
	}

	/**
	 * получение и выдача на экран главной страницы
	 * 
	 * @return type
	 */
	public function getIndexPage() {

		$res = $this->gitM->getIndexData();

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}

	/**
	 * обновляет ремоутные ветки и чистит удаленные
	 * 
	 * @return type
	 */
	public function update_remotes() {
		$res = $this->gitM->getUpdateRemotesStatus();

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}

	/**
	 * спуливаемся с веткой
	 * 
	 * @return type
	 */
	public function checkout() {

		$params = myRoute::getRequest('params', 'arr', []);
		$branchName = isset($params['branch_name']) ? $params['branch_name'] : '';
		
		$res = $this->gitM->checkoutBranch($branchName);

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}
	

	/**
	 * убиваем удаленную ветку
	 * 
	 * @return type
	 */
	public function delete_remote() {

		$params = myRoute::getRequest('params', 'arr', []);
		$branchName = isset($params['branch_name']) ? $params['branch_name'] : '';
		
		$res = $this->gitM->deleteRemote($branchName);

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}
	
	/**
	 * убиваем удаленную ветку
	 * 
	 * @return type
	 */
	public function delete_local() {

		$params = myRoute::getRequest('params', 'arr', []);
		$branchName = isset($params['branch_name']) ? $params['branch_name'] : '';
		
		$res = $this->gitM->deleteLocal($branchName);

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}
	
	/**
	 * коммит текущего состояния
	 * 
	 * @return type
	 */
	public function commit() {

		$text = myRoute::getRequest('text', 'str', 'text');

		$res = $this->gitM->makeCommit($text);

		$this->processStdJson($res, 'cant fetch commit result');
	}

	/**
	 * пушим сами себя в себя
	 * 
	 * @return type
	 */
	public function push_self() {

		$res = $this->gitM->pushSelf();

		$this->processStdJson($res, 'cant make push self');
	}

	/**
	 * спуливаемся с веткой
	 * 
	 * @return type
	 */
	public function pull() {

		$branchName = myRoute::getRequest('branch_name', 'str', false);
		
		$res = $this->gitM->pullOrigin($branchName);

		$this->processStdJson($res, 'cant make pull');
	}
	
	/**
	 * сообщение про недоступность системы
	 */
	public function unavailability() {
		echo 'try this:<br>';
		echo 'git config user.email "my@email.here"<br>';
		echo 'git config user.name "RomanSh"<br>';
		echo 'or add section user in .git/config<br><br>';
		exit('unavailability');
	}

	/**
	 * обрабатывает стандартной логикой ответ 
	 * и выводит стандартный JSON на этот случай
	 * 
	 * @param type $res
	 * @param type $dfltMsg
	 */
	public function processStdJson($res, $dfltMsg = 'unknown error') {
		if (!$res) {
			myOutput::jsonError($dfltMsg);
		} 
		elseif(isset($res['error'])){
			$id = isset($res['error']['id']) ? $res['error']['id'] : -1;
			$msg = isset($res['error']['message']) ? $res['error']['message'] : $dfltMsg;
			myOutput::jsonError($msg, $id);
		} 
		else {
			myOutput::jsonSuccess($res);
		}
	}

}
