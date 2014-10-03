<?php

namespace ORM;

/**
 * 根据AtrributeMap把数据对象持久化到PHP数组文件中。
 *
 * @author Hoheart
 *        
 */
class PhpPersistence extends AbstractPersistence {
	
	/**
	 * 保存的文件夹。
	 */
	protected $mSaveDir;

	/**
	 * 取得唯一实例
	 *
	 * @return \icms\Evaluation\PhpPersistence
	 */
	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new PhpPersistence();
		}
		
		return $me;
	}

	/**
	 * 取得保存的文件夹
	 */
	public function getSaveDir () {
		return $this->mSaveDir;
	}

	/**
	 * 设置保存的文件夹
	 *
	 * @param string $dir        	
	 * @return \icms\Evaluation\PhpPersistence
	 */
	public function setSaveDir ($dir) {
		$this->mSaveDir = $dir;
		
		return $this;
	}

	/**
	 * 把php的关联数组转成php代码。
	 *
	 * @param array $arr        	
	 * @return string
	 */
	static public function mapToCode ($arr) {
		if (! is_array($arr)) {
			$strVal = $arr;
			if (is_string($strVal)) {
				$strVal = str_replace('\\', '\\\\', $strVal);
				$strVal = str_replace('\'', '\\\'', $strVal);
				$strVal = "'$strVal'";
			} else if (is_bool($strVal)) {
				$strVal = $strVal ? 'true' : 'false';
			} else if (null === $strVal) {
				$strVal = 'null';
			}
			return $strVal;
		}
		
		$code = '';
		foreach ($arr as $key => $val) {
			$strKey = self::mapToCode($key);
			
			$strVal = self::mapToCode($val);
			
			if (! empty($code)) {
				$code .= ',';
			}
			
			$code .= "$strKey=>$strVal";
		}
		
		$code = "array($code)";
		
		return $code;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \icms\Evaluation\AbstractPersistence::save()
	 */
	public function save ($dataObj, $className = null, $isSaveSub = false, $isDelete = false, 
			ClassDesc $clsDesc = null) {
		if (null == $className) {
			$className = get_class($dataObj);
		}
		
		if (null == $clsDesc) {
			$descFactory = DescFactory::Instance();
			$clsDesc = $descFactory->getDesc($className);
			if (null == $clsDesc) {
				throw new \Exception('can not found class desc. class: ' . $className);
			}
		}
		
		$map = $this->createSaveMap($dataObj, $clsDesc, $isSaveSub);
		
		$oldMap = self::read2Map($clsDesc->persistentName, $this->mSaveDir);
		if (null == $oldMap) {
			$oldMap = array();
		}
		
		// 对于只有一个键作为主键的，使用key=>value形式保存的数组。
		$hasKey = 1 == count($clsDesc->primaryKey) ? true : false;
		$pkey = null;
		$pkeyVal = null;
		if ($hasKey) { // 有主键作索引，直接用索引。
			$pkey = $clsDesc->primaryKey[0];
			$pkeyVal = $dataObj->$pkey;
			
			if ($isDelete) {
				unset($oldMap[$pkeyVal]);
			} else {
				$oldMap[$pkeyVal] = $map;
			}
		} else { // 没有主键作索引，挨个寻找
			$index = $this->findObjIndex($dataObj, null, $clsDesc, $oldMap);
			if ($index >= 0) {
				if ($isDelete) {
					unset($oldMap[$index]);
				} else {
					array_splice($oldMap, $index, 1, $map);
				}
			} else {
				if ($isDelete) {
					return;
				} else { // 添加
					$oldMap[] = $map;
				}
			}
		}
		
		$this->write2File($oldMap, $clsDesc);
	}

	/**
	 * 过指定的类名和键值对删除对象。其过程和delete类似，只是在找要删除的行时，
	 * 是通过键值对来找。为保证数据的完整性，该方法要根据ClassAtrribute对比键，
	 * 如果传入的键值对里包含了非键的键值对，不做处理。
	 *
	 * @param string $class        	
	 * @param Condition $condition        	
	 */
	public function delete ($className, Condition $condition = null) {
		$clsDesc = DescFactory::Instance()->getDesc($className);
		if (null == $clsDesc) {
			throw new \Exception('can not found class desc on delete. class: ' . $className);
		}
		
		$oldMap = self::read2Map($clsDesc->persistentName, $this->mSaveDir);
		if (! is_array($oldMap)) {
			return;
		}
		
		$factory = PhpFactory::Instance();
		$condMap = $factory->filterResult($oldMap, $clsDesc, $condition);
		
		$map = array_diff_key($oldMap, $condMap);
		
		$this->write2File($map, $clsDesc);
	}

	/**
	 * 把php的关联数组保存为代码形式写入文件。
	 *
	 * @param array $map        	
	 * @param ClassDesc $clsDesc        	
	 */
	protected function write2File (array $map, ClassDesc $clsDesc) {
		$filePath = $clsDesc->persistentName;
		
		// 清空文件，不考虑
		$fp = fopen($this->mSaveDir . $filePath . '.php', 'w+');
		flock($fp, LOCK_EX);
		
		$code = self::mapToCode($map);
		
		fwrite($fp, '<?php return ' . $code . ';');
		
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
	 * 把类的所有对象读取到数组。其实就是include。
	 *
	 * @param string $fname        	
	 * @param string $dir        	
	 */
	static public function read2Map ($fname, $dir) {
		if (empty($fname)) {
			return array();
		} else {
			$fname = $fname . '.php';
			$path = $dir . $fname;
		}
		
		return include $path;
	}

	/**
	 * 找出不是一个键作主键的对象在存储数组中的位置。如果该数据类的主键只有一个键，是不需要找的，
	 * 存放的时候直接存放了索引。
	 * 根据传入的对象（或键值对），再对比ClassAttribute的键，找出该对象（键值对）所在的位置。
	 * obj和map参数只传一个，如果两个都不是null,obj有效。
	 * attributeMap的目的是对比主键，先找主键，再找键，再对比其他元素。
	 *
	 * @param object $obj        	
	 * @param array $map        	
	 * @param ClassDesc $classDesc        	
	 * @param array $map
	 *        	要寻找的数组。注意，数组中存放的是map形式，不是对象。
	 */
	protected function findObjIndex ($obj = null, array $map = null, ClassDesc $classDesc, array $map) {
		$keyArr = array();
		
		foreach ($attributeMap as $attr) {
			if (ClassAttribute::ATTRIBUTE_KEY == $attr->attribute ||
					 ClassAttribute::ATTRIBUTE_AUTO_INCREMENT == $attr->attribute) {
				$key = $attr->name;
				$val = null;
				if (! empty($map)) {
					$val = $map[$key];
				} else {
					$val = $obj->$key;
				}
				
				if (! empty($val)) {
					$keyArr[$key] = $val;
				}
			}
		}
		
		$i = 0;
		foreach ($objArr as $o) {
			$allSame = true;
			
			foreach ($keyArr as $key => $val) {
				if (! empty($map)) {
					if ($map[$key] != $val) {
						$allSame = false;
					}
				} else {
					if ($obj->$key != $val) {
						$allSame = false;
					}
				}
			}
			
			if ($allSame) {
				return $i;
			}
			
			++ $i;
		}
		
		return - 1;
	}
}
?>