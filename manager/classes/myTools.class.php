<?php

class myTools {

	/**
	 * доклеивает второй массив к первому, без сохранения индексов
	 * 
	 * @param type $param
	 */
	public static function arraysUnionWithoutIndex($array1, $array2) {
		foreach ($array2 as $val) {
			$array1[] = $val;
		}
		
		return $array1;
	}

}
