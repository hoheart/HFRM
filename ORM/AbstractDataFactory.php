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
	 * 根据类的唯一主键的值进行查询
	 *
	 * @param integer $id        	
	 * @return DataClass 返回一个对象
	 */
	abstract public function get ($className, $id);

	abstract public function where ($className, Condition $cond = null);
}
?>