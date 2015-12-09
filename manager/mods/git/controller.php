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
			ini_set('max_execution_time', '120');
			set_time_limit(120);
			$this->$_do();
		}
		else {
			$this->gitV->renderError('undefined ' . $_do);
		}
	}

	/**
	 * получение и выдача на экран главной страницы
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
	 */
	public function commit() {

		$text = myRoute::getRequest('text', 'str', 'text');

		$res = $this->gitM->makeCommit($text);

		$this->processStdJson($res, 'cant fetch commit result');
	}

	/**
	 * смерживаемся с локальной веткой
	 */
	public function merge_local() {

		$branchName = myRoute::getRequest('branch_name', 'str', false);

		$res = $this->gitM->mergeLocal($branchName);

		$this->processStdJson($res, 'cant fetch merge result');
	}

	/**
	 * создание нового бранча
	 */
	public function add_branch() {

		$text = myRoute::getRequestParams('text', 'str', 'text');

		$res = $this->gitM->addNewBranch($text);

		if (!$res) {
			$this->gitV->renderError('no datas recieved');
			return;
		}

		$this->gitV->renderIndexPage($res);
	}

	/**
	 * пушим сами себя в себя
	 */
	public function push_self() {

		$res = $this->gitM->pushSelf();

		$this->processStdJson($res, 'cant make push self');
	}

	/**
	 * спуливаемся с удаленной веткой
	 */
	public function pull() {

		$branchName = myRoute::getRequest('branch_name', 'str', false);

		$res = $this->gitM->pullOrigin($branchName);

		$this->processStdJson($res, 'cant make pull');
	}

	/**
	 * получаем список отложенных сташей
	 */
	public function stash_list() {

		$res = $this->gitM->getStashList();

		$this->processStdJson($res, 'cant get stash list');
	}

	/**
	 * откладываем текущие грязные правки
	 */
	public function stash_save() {

		$res = $this->gitM->getStashSave();

		$this->processStdJson($res, 'cant do stash save');
	}

	/**
	 * применяем и удаляем последние грязные правки
	 */
	public function stash_pop() {

		$res = $this->gitM->getStashPop();

		$this->processStdJson($res, 'cant do stash pop');
	}

	/**
	 * прибиваем все грязные отложенности
	 */
	public function stash_clear() {

		$res = $this->gitM->getStashClear();

		$this->processStdJson($res, 'cant do stash clear');
	}

	/**
	 * сменить текущий репозиторий и открыть титульную страницу
	 */
	public function change_rep() {
		$repName = myRoute::getRequestParams('rep_name', 'str', false);
		$this->gitM->changeRepository($repName);
		header('Location: ' . myRoute::getRoute('git'));
	}

	/**
	 * проверить, чтоб на всех файлах не было лишних владельцев
	 */
	public function chk_chown() {
		$res = $this->gitM->checkErrorsOwners();
		$this->processStdJson($res, 'cant chk_owner');
	}
	
	/**
	 * посмотреть последние коммиты
	 */
	public function git_log() {
		$res = $this->gitM->gitLog();
		$this->processStdJson($res, 'cant get log');
	}

	/**
	 * сообщение про недоступность системы
	 */
	public function unavailability() {
		echo '<h1>unavailability</h1>';
		echo 'try this:<br>';
		echo 'git config user.email "my@email.here"<br>';
		echo 'git config user.name "RomanSh"<br>';
		echo 'or add section user in .git/config<br><br>';
		echo '<a href="' . myRoute::getRoute('git') . '">Click here to try again</a>';
		// может выведем немного из лога апача
		echo '<hr>';
		echo 'last logs:<textarea style="width:100%;" rows="11">' . $this->gitM->readApacheErrorLog() . '</textarea>';
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
		elseif (isset($res['error'])) {
			$id = isset($res['error']['id']) ? $res['error']['id'] : -1;
			$msg = isset($res['error']['message']) ? $res['error']['message'] : $dfltMsg;
			myOutput::jsonError($msg, $id);
		}
		else {
			myOutput::jsonSuccess($res);
		}
	}

}
