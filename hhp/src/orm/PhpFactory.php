<?php

namespace orm;

/**
 * 从存放于PHP的数组中取出各种数据类的工厂类。
 *
 * @author Hoheart
 * @deprecated 该类暂时废弃
 *            
 */
class PhpFactory extends AbstractDataFactory {
	
	/**
	 * 保存数据的文件夹。
	 *
	 * @var string
	 */
	protected $mSaveDir = null;

	protected function __construct () {
	}

	/**
	 * 取得单一实例
	 *
	 * @return \icms\Evaluation\PhpFactory
	 */
	static public function Instance () {
		static $me = null;
		if (null === $me) {
			$me = new PhpFactory();
		}
		
		return $me;
	}

	/**
	 * 设置存放数据类对象的文件夹
	 *
	 * @param string $dir        	
	 * @return \icms\Evaluation\PhpFactory
	 */
	public function setSaveDir ($dir) {
		$this->mSaveDir = $dir;
		
		return $this;
	}

	/**
	 * 取得存放数据类对象的文件夹
	 *
	 * @return string
	 */
	public function getSaveDir () {
		return $this->mSaveDir;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \icms\Evaluation\AbstractDataFactory::getDataMapList()
	 */
	public function getDataMapList ($className, Condition $condition = null, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$descFactory = DescFactory::Instance();
			$clsDesc = $descFactory->getDesc($className);
		}
		
		// 1.读取所有值
		$map = PhpPersistence::read2Map($clsDesc->persistentName, $this->mSaveDir);
		
		// 2.根据条件过滤。
		if (! empty($map)) {
			$arr = $this->filterResult($map, $clsDesc, $condition);
		} else {
			$arr = $map;
		}
		
		return $arr;
	}

	/**
	 * 根据传入的条件，对map进行过滤
	 *
	 * @param array $map        	
	 * @param ClassDesc $clsDesc        	
	 * @param Condition $condition        	
	 * @return array multitype:
	 */
	public function filterResult (array $map, ClassDesc $clsDesc, Condition $condition = null) {
		if (null == $condition) {
			return $map;
		}
		
		$arr = array();
		foreach ($condition->itemList as $item) {
			$condOp = $item->operation;
			$condKey = $clsDesc->attribute[$item->key]->persistentName;
			$condVal = $item->value;
			
			$childRet = array();
			foreach ($map as $index => $row) {
				// 没有要搜索的关键字，表示所有值都不符合条件。
				if (! array_key_exists($condKey, $row)) {
					return array();
				}
				
				switch ($condOp) {
					case Condition::OPERATION_EQUAL:
						if ($row[$condKey] == $condVal) {
							$childRet[$index] = $row;
						}
						break;
					case Condition::OPERATION_GREATER:
						if ($row[$condKey] > $condVal) {
							$childRet[$index] = $row;
						}
						break;
					case Condition::OPERATION_INEQUAL:
					case Condition::OPERATION_INEQUAL1:
						if ($row[$condKey] != $condVal) {
							$childRet[$index] = $row;
						}
						break;
					case Condition::OPERATION_LESS:
						if ($row[$condKey] < $condVal) {
							$childRet[$index] = $row;
						}
						break;
					case Condition::OPERATION_LIKE:
						if (strpos($row[$condKey], $condVal) > 0) {
							$childRet[$index] = $row;
						}
						break;
				}
			}
			
			$arr = $this->combinConditionItem($arr, $childRet, $condition->relationship);
		}
		
		if (is_array($condition->children)) {
			foreach ($condition->children as $child) {
				$childRet = $this->filterResult($map, $clsDesc, $child);
				
				$arr = $this->combinConditionItem($arr, $childRet, $condition->relationship);
			}
		}
		
		return $arr;
	}

	/**
	 * 根据关系，组合两个数组。
	 *
	 * @param array $ret1        	
	 * @param array $ret2        	
	 * @param integer $relationship        	
	 * @return array
	 */
	protected function combinConditionItem (array $ret1, array $ret2, $relationship) {
		$arr = array();
		
		if (Condition::RELATIONSHIP_AND == $relationship) {
			if (empty($ret1) || empty($ret2)) {
				$arr = empty($ret1) ? $ret2 : $ret1;
			} else {
				$arr = array_intersect_key($ret1, $ret2);
			}
		} else {
			$arr = $ret1 + $ret2;
		}
		
		return $arr;
	}
}
?>