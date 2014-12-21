<?php

namespace orm;

use hfc\util\Sequence;

/**
 *
 *
 * 根据AtrributeMap把数据对象持久化到PHP数组文件中。
 *
 * @deprecated 该类暂时废弃
 * @author Hoheart
 *        
 */
class PhpPersistence extends AbstractPersistence {
	
	/**
	 * 保存的文件夹。
	 */
	protected $mSaveDir = null;
	protected $mSequenceDir = null;

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
	 * 设置产生序列所需要的文件夹。
	 *
	 * @param string $dir        	
	 */
	public function setSequenceDir ($dir) {
		$this->mSequenceDir = $dir;
	}

	static public function ArrayToCode ($arr) {
		return self::MapToCode($arr);
	}

	/**
	 * 把php的关联数组转成php代码。
	 *
	 * @param array $arr        	
	 * @return string
	 */
	static public function MapToCode ($arr) {
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
			} else if (is_object($strVal)) {
				$cls = get_class($strVal);
				if ('DateTime' == $cls) {
					$strVal = $strVal->format('Y-m-d H:i:s');
					$strVal = "'$strVal'";
				}
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
	public function add ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		if ($this->getPropertyVal($dataObj, 'mSaved')) {
			return - 1;
		}
		// 只要开始保存这个对象，就设置保存成功，否则会死循环。
		$this->setPropertyVal($dataObj, 'mSaved', true);
		
		if (null == $clsDesc) {
			$descFactory = DescFactory::Instance();
			$clsDesc = $descFactory->getDesc(get_class($dataObj)); // clsDesc再怎么着也会返回一个默认的值
		}
		
		$oldMap = self::Read2Map($clsDesc->persistentName, $this->mSaveDir);
		if (null == $oldMap) {
			$oldMap = array();
		}
		$map = $this->createSaveMap($dataObj, $clsDesc, $isSaveSub);
		
		// 对于只有一个键作为主键的，使用key=>value形式保存的数组。对于多个主键的，用主键连接的方式作索引
		$pkeyVal = null;
		if (is_array($clsDesc->primaryKey)) {
			$pkeyValArr = array();
			foreach ($clsDesc->primaryKey as $key) {
				$pkeyValArr[] = $dataObj->$key;
			}
			
			$pkeyVal = implode(',', $pkeyValArr);
		} else {
			$pkey = $clsDesc->primaryKey;
			$pkeyVal = $dataObj->$pkey;
		}
		
		// 如果是更新，返回更新的条数。否则返回-1，自增长字段放入dataObj属性中。
		$ret = - 1;
		if (key_exists($pkeyVal, $oldMap)) {
			$ret = 1;
		}
		$oldMap[$pkeyVal] = $map;
		
		$this->write2File($oldMap, $clsDesc);
		
		return $ret;
	}

	public function update ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		$this->add($dataObj, $isSaveSub, $clsDesc);
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
	static public function Read2Map ($fname, $dir) {
		if (empty($fname)) {
			return array();
		} else {
			$fname = $fname . '.php';
			$path = $dir . $fname;
		}
		
		if (file_exists($path)) {
			return include $path;
		}
		
		return false;
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
	 *        	要寻找的数组。注意，数组中存放的是map形式，不是对象。
	 * @param ClassDesc $classDesc        	
	 *
	 */
	protected function findObjIndexForMultiPK ($obj, array $map, ClassDesc $classDesc) {
		if (! is_array($map) || empty($map)) {
			return - 1;
		}
		
		$keyArr = array();
		foreach ($classDesc->primaryKey as $key) {
			$keyArr[$classDesc->attribute[$key]->persistentName] = $obj->$key;
		}
		
		$i = 0;
		foreach ($map as $o) {
			$allSame = true;
			
			foreach ($keyArr as $key => $val) {
				if ($o[$key] != $val) {
					$allSame = false;
					
					break;
				}
			}
			
			if ($allSame) {
				return $i;
			}
			
			++ $i;
		}
		
		return - 1;
	}

	/**
	 * 创建出要保存的键值对（php数组）。
	 *
	 * @param object $dataObj        	
	 * @param ClassDesc $classDesc        	
	 * @param boolean $isSaveSub        	
	 * @return array 对于只有一个键作为主键的，返回key=>value数组。
	 */
	public function createSaveMap ($dataObj, ClassDesc $classDesc, $isSaveSub = false) {
		$map = array();
		
		foreach ($classDesc->attribute as $attr) {
			$persistentName = $attr->persistentName; // 注意，empty不会调用__get方法
			if (empty($persistentName) && ! $isSaveSub) {
				continue;
			}
			
			$name = $attr->name;
			$val = $this->getPropertyVal($dataObj, $name);
			if ('class' == $attr->var) {
				if (! $isSaveSub) {
					continue;
				}
				if (empty($val)) {
					continue;
				}
				
				// 首先保存关连的另外一个类。
				if (is_array($val)) {
					foreach ($val as $oneObj) {
						$this->save($oneObj, $isSaveSub);
					}
				} else {
					$this->save($val, $attr->belongClass, $isSaveSub);
				}
				
				// 保存完，在map里就不需要再保留了。
				continue;
			} else if (empty($attr->persistentName)) {
				continue;
			} else {
				if (null === $val && $attr->autoIncrement) {
					$val = $this->autoIncrement($classDesc->persistentName);
					$dataObj->$name = $val;
				} else {
					switch ($attr->var) {
						case 'date':
							$val = $val->format('Y-m-d');
							break;
						case 'datetime':
							$val = $val->format('Y-m-d H:i:s');
							break;
						case 'time':
							$val = $val->format('H:i:s');
							break;
					}
				}
			}
			
			$map[$attr->persistentName] = $val;
		}
		
		return $map;
	}

	protected function autoIncrement ($name) {
		$s = Sequence::Instance($this->mSequenceDir);
		
		return $s->next($name);
	}
}
?>