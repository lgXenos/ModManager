<?php

/**
 * класс обработки гит-фронтэнда
 */
class gitActionController {

	public $gitV = false;

	/**
	 * поиск и выполнение заказанного метода из текущего view
	 * 
	 */
	public function __construct() {

		$_do = myRoute::getRequest('_do', 'str', 'getIndexPage');
		$this->gitV = new gitActionView();

		if (method_exists($this, $_do)) {
			$this->$_do();
		} else {
			$this->gitV->renderError('undefined');
		}
	}

	/**
	 * получение и выдача на экран главной страницы
	 * 
	 * @return type
	 */
	public function getIndexPage() {

		$gitM = new gitActionModel();

		$res = $gitM->getIndexData();

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
		$gitM = new gitActionModel();

		$res = $gitM->makeCommit($text);

		if(!$res){
			myOutput::jsonError('cant fetch commit result');
		}
		else {
			myOutput::jsonSuccess($res);
		}

		$this->gitV->renderIndexPage($res);
	}

	public function renderCSS() {
		$this->gitV->renderCSS();
	}

	public function renderJS() {
		$this->gitV->renderJS();
	}

}
