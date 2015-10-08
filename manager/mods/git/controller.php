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
			$this->gitV->renderError('undefined '.$_do);
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
	 * коммит текущего состояния
	 * 
	 * @return type
	 */
	public function commit() {

		$text = myRoute::getRequest('text', 'str', 'text');

		$res = $this->gitM->makeCommit($text);

		if(!$res){
			myOutput::jsonError('cant fetch commit result');
		}
		else {
			myOutput::jsonSuccess($res);
		}
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
	
	public function update_remotes() {
		$res = $this->gitM->getUpdateRemotesStatus();

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}

}
