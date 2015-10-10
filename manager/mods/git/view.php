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

				// проверяем наличие в массиве списка индексов и превращаем или в $текст или в $$переменную
				$indexArr = array('local','remote');
				foreach($indexArr as $_f){
					$$_f  = isset($_v[$_f]) ? $_i : '';
					if (isset($_v[$_f]) AND $_v[$_f] > 1) {
						$$_f = '<a href="https://redmine.suffra.com/issues/' . $_v[$_f] . '" target="_blank">' . $$_f . '</a>';
					}
				}


				if (!$current) {
					$current = isset($_v['current']) ? $_i : false;
				}

				$html .= '
					<tr>
						<td>
							<a class="button showOnHover" href="#" data-type="local" data-name="'. $_i .'">+</a> 
								|
							' . $local . '
						</td>
						<td>&nbsp;</td>
						<td>
							<a class="button showOnHover" href="#" data-type="remote" data-name="'. $_i .'">+</a> 
								|
							' . $remote . '
						</td>
					</tr>
				';
			}

			$currentHtml = '<i>cant parse name of current branch :(</i>';
			if ($current) {
				$currentHtml = '
					<a class="button js_git_commit" href="#" title="сделать commit -am {коммент}">commit</a> |
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
				<div class="scrollerXY" style="height:170px;">
					<div class="asConsole js_myConsole">
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
					<tr>
						<th>local branches</th>
						<th width="70px">&nbsp;</th>
						<th>remotes branches</th>
					</tr>
					' . $html . '
				</table>
			';

		}

		myOutput::addCSS('main.css');
		myOutput::addJS('main.js');
		myOutput::outFullHtml($html, 'MyGit / RomanSh');
	}

}
