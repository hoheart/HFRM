<?php

namespace orm;

/**
 * 抽象的数据工厂
 *
 * @author Hoheart
 *        
 */
abstract class AbstractDataFactory {

	/**
	 * 不用把数据对象转成对象格式，直接返回取得数组。为了保持数据的顺序，不用主键作索引。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 * @param ClassDesc $clsDesc        	
	 * @return array
	 */
	abstract public function getDataMapList ($className, Condition $condition = null, 
			ClassDesc $clsDesc = null);
}
?>