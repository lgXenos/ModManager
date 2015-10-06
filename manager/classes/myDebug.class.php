<?php

class myDebug {

	private static $instance = false;

	/**
	 * singleton
	 * 
	 * @return class instance
	 */
	public static function getInstance() {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * exit + backtrace
	 * 
	 * @param type $msg
	 * @param type $withTrace
	 */
	public static function myExit($msg = 'myExitHere', $withTrace = true) {
		echo $msg;
		if ($withTrace) {
			$trace = self::getTrace();
			unset($trace[0]);
			self::iout($trace);
		}
		exit();
	}

	/**
	 * получаем обратный trace до точки
	 * 
	 * @param type $maxDepth
	 * @return string
	 */
	public static function getTrace($maxDepth = 99) {

		$bt = debug_backtrace(0, $maxDepth);

		$resArr = array();
		foreach ($bt as $_b => $_bv) {
			$item = array('call' => $_bv['function']);
			if (isset($_bv['file'])) {
				$item['from'] = array(
					'file' => $_bv['file'],
					'line' => $_bv['line'],
				);
			}

			$resArr[] = $item;
		}

		return $resArr;
	}

	/**
	 * делает обалденно красивый вардамп с раскрывающимися свойствами
	 * не вываливая на экран сразу ВЕСЬ массив / объект
	 * 
	 * @param type $what
	 */
	public static function iout($what) {
		$args = func_get_args();
		$count = count($args);
		$result = array();
		for ($i = 0; $i < $count; $i++) {
			switch (gettype($args[$i])) {
				case "boolean" :
					$result[] = ($args[$i]) ? 'true' : 'false';
					break;
				case "NULL" :
					$result[] = "NULL";
					break;
				default :
					$result[] = print_r($args[$i], true);
			}
		}
		$result = print_r($result, true);
		$clickevent = "onclick='javascript: iout_toggle(event);'";
		$result = preg_replace("/\s*?\[(.*)\] \=> (.*?)\n/mi", "\n<div class='legend' " . $clickevent . ">[\$1] => \$2</div>\n", $result);
		$result = preg_replace("/(<div class='legend' " . preg_quote($clickevent) . ">.*<\/div>)\n\s*?\(/mi", "\n<div class='object'><div class='hilite'>\$1</div><div class='children' style='display: none'>\n", $result);
		$result = preg_replace("/\n\s*?\)\n/", "\n</div></div>\n", $result);
		$result = preg_replace("/Array\n\(\n/i", "\n<div class='result'><div class='object'><div class='legend' " . $clickevent . ">IOUT - Result</div><div class='children'>\n", $result);
		echo '
				<style type="text/css">
					div.legend {
						cursor: default;
						cursor: expression("hand");
						padding-top: 2px;
						padding-bottom: 2px;
					}
					div.legend span {
						margin-left: 5px;
					}
					div.object {
						font-size: 12px;
						font-family: courier new;
					}
					div.children {
						margin-left: 50px;
						padding-top: 1px;
						padding-bottom: 1px;
						border-left: 1px solid #f9f9f9;
						min-height: 5px;
						height: expression("5px");
					}
					div.result {
						border: 1px solid #f2f2f2;
						padding: 10px;
					}
					div.hilite {
						color: #050;
					}
				</style>
				<script language="javascript">
					function iout_toggle(e) {
						var parent = null;
						if(e.srcElement)
							parent = e.srcElement.parentElement;    
						else {
							parent = e.currentTarget.parentNode;
						}
						if(parent.className == "hilite") {
							if(e.srcElement)
								parent = parent.parentElement;
							else
								parent = parent.parentNode;
							var children = parent.childNodes[1];
							children.style.display = children.style.display == "" ? "none" : "";
						}
					}
				</script>
			' . $result . '</div>';
	}

	private function __construct() {
		
	}

	private function __clone() {
		
	}

	private function __wakeup() {
		
	}
	
}
