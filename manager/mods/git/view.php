<?php

class gitActionView {

	/**
	 * рендеринг страницы ошибки
	 * 
	 * @param type $text
	 */
	public function renderError($text) {
		myOutput::out($text);
	}

	/**
	 * рендеринг главной страницы веток и операций
	 * 
	 * @param type $res
	 */
	public function renderIndexPage($res) {
		$html = '';
		if (is_array($res)) {

			$current = false;

			// перебор имеющихся веток
			foreach ($res['branches'] as $_i => $_v) {


				$local = isset($_v['local']) ? $_i : '';
				if (isset($_v['local']) AND $_v['local'] > 1) {
					$local = '<a href="https://redmine.suffra.com/issues/' . $_v['local'] . '" target="_blank">' . $local . '</a>';
				}

				$remote = isset($_v['remote']) ? $_i : '';
				if (isset($_v['remote']) AND $_v['remote'] > 1) {
					$remote = '<a href="https://redmine.suffra.com/issues/' . $_v['remote'] . '" target="_blank">' . $remote . '</a>';
				}

				if (!$current) {
					$current = isset($_v['current']) ? $_i : false;
				}

				$html .= '
					<tr>
						<td>
							<a class="button showOnHover" href="' . myRoute::getRoute('git', 'delBranch', array('alias' => $_i, 'type' => 'branch-D')) . '">X</a> 
								|
							' . $local . '
						</td>
						<td width="70px">&nbsp;</td>
						<td>
							<a class="button showOnHover" href="' . myRoute::getRoute('git', 'delBranch', array('alias' => $_i, 'type' => 'push:')) . '">X</a> 
								|
							' . $remote . '
						</td>
					</tr>
				';
			}

			$currentHtml = '<i>cant parse name of current branch :(</i>';
			if ($current) {
				$currentHtml = '
					<a class="button" href="' . myRoute::getRoute('git', 'push') . '">push</a> |
					<a class="button js_git_commit" href="#">commit</a> |
					on branch: <strong>' . $current . '</strong>
				';
			}

			$_s = '';
			foreach ($res['status'] as $_i => $_v) {
				$_s .= $_v . '<br>';
			}
			if($_s==''){
				$_s = 'already up to date';
			}
			$status = '
			<div class="spacer">
				<div class="scrollerXY" style="max-height:150px;">
					<div class="asConsole js_myConsole">
						~$ git status -s --no-column<br>
						'.$_s.'
					</div>
				</div>
			</div>
			';
            
			$html = '
				<div class="currFicha spacer">' . $currentHtml . '</div>
				'.$status.'
				<div class="spacer">
					<a class="button" href="' . myRoute::getRoute('git', 'update_remotes') . '">
						git remote update, git remote prune origin
					</a>
				</div>
				<table class="mainTable">
					' . $html . '
				</table>
			';

		}

		myOutput::addCSS('main.css');
		myOutput::addJS('main.js');
		myOutput::outFullHtml($html, 'MyGit / RomanSh');
	}

}
