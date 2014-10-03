<?php

namespace ORM;

/**
 * 根据AtrributeMap把数据对象持久化到数据库中。
 *
 * @author Hoheart
 *        
 */
class DatabasePersistence {

	public function __construct () {
	}

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new DatabasePersistence();
		}
		
		return $me;
	}

	/**
	 * 调用read2Map，读出原有的，进行合并，再保存。
	 *
	 * @param object $dataObj        	
	 * @param string $className        	
	 * @param boolean $isDelete        	
	 */
	public function save ($dataObj, $className = null, $isDelete = false) {
	}

	/**
	 * 删除数据对象，调用save方法实现。
	 *
	 * @param object $dataObj        	
	 * @param string $className        	
	 */
	public function delete ($dataObj, $className = null) {
	}

	/**
	 * 过指定的类名和键值对删除对象。其过程和delete类似，只是在找要删除的行时，
	 * 是通过键值对来找。为保证数据的完整性，该方法要根据ClassAtrribute对比键，
	 * 如果传入的键值对里包含了非键的键值对，不做处理。
	 *
	 * @param string $class        	
	 * @param array $map        	
	 */
	public function deleteByKey ($className, array $map) {
	}
}
?>