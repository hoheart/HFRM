<?php

namespace icms\Evaluation {

	use Condition\Item;

	/**
	 * 条件类，存放key=>value形式的条件元素，key和value之间的比较关系有=、!=、<>、>、<、LIKE，
	 * 个条件元素之间可以用OR和AND进行连接。
	 * 还可以包含子条件。
	 * 本类只是简单的存放，不做相同条件合并等计算操作。
	 *
	 * @author Hoheart
	 *        
	 */
	class Condition {
		
		/**
		 * 操作类型常量
		 *
		 * @var string
		 */
		const OPERATION_EQUAL = '=';
		const OPERATION_INEQUAL = '!=';
		const OPERATION_INEQUAL1 = '<>';
		const OPERATION_GREATER = '>';
		const OPERATION_LESS = '<';
		const OPERATION_LIKE = 'LIKE';
		
		/**
		 * 子条件组合关系类型
		 */
		const RELATIONSHIP_AND = 0;
		const RELATIONSHIP_OR = 1;
		
		/**
		 * 条件项目的列表
		 *
		 * @var array
		 */
		protected $itemList;
		
		/**
		 * 条件项目的组合关系。
		 *
		 * @var integer
		 */
		protected $relationship;
		
		/**
		 * 子条件
		 *
		 * @var array
		 */
		protected $children;
		
		/**
		 * 支持的操作。
		 *
		 * @var array
		 */
		protected static $SupportOperation = array(
			self::OPERATION_EQUAL,
			self::OPERATION_INEQUAL,
			self::OPERATION_INEQUAL1,
			self::OPERATION_GREATER,
			self::OPERATION_LESS,
			self::OPERATION_LIKE
		);
		
		/**
		 * 支持的子条件之间的关系。
		 *
		 * @var array
		 */
		protected static $SupportRelationship = array(
			self::RELATIONSHIP_AND,
			self::RELATIONSHIP_OR
		);

		public function __construct ($str = null) {
			if (! empty($str)) {
				foreach (self::$SupportOperation as $op) {
					$pos = strpos($str, $op);
					if ($pos >= 0) {
						$item = new Item();
						$item->key = trim(substr($str, 0, $pos));
						$opLen = strlen($op);
						$item->operation = $op;
						$item->value = trim(substr($str, $pos + $opLen));
						
						$this->itemList[] = $item;
						
						return;
					}
				}
			}
			
			// 默认值
			$this->relationship = self::RELATIONSHIP_AND;
			$this->children = array();
		}

		public function __get ($name) {
			return $this->$name;
		}

		/**
		 * 添加子条件
		 *
		 * @param Condition $cond        	
		 */
		public function addChild (Condition $cond) {
			$this->children[] = $cond;
		}

		public function add ($key, $operation, $value) {
			if (in_array($operation, self::$SupportRelationship)) {
				$item = new Item();
				$item->operation = $operation;
				$item->key = $key;
				$item->value = $value;
				
				$this->itemList[] = $item;
			} else {
				throw new \Exception();
			}
		}

		public function setRelationship ($relation) {
			if (in_array($relation, self::$SupportRelationship)) {
				$this->relationship = $relation;
			} else {
				throw new \Exception();
			}
		}

		/**
		 * 只是简单地比较每个条件元素是否相等，不做相同合并的计算。
		 *
		 * @param Condition $cond        	
		 * @return boolean
		 */
		public function equal (Condition $cond) {
			if (count($this->itemList) != count($cond->itemList)) {
				return false;
			}
			if (count($this->children) != count($cond->children)) {
				return false;
			}
			
			$hisItemList = $cond->itemList;
			foreach ($this->itemList as $item) {
				$found = false;
				$hisItemCount = count($hisItemList);
				for ($i = 0; $i < $hisItemCount; ++ $i) {
					if ($item->equal($hisItemList[$i])) {
						unset($hisItemList[$i]);
						$found = true;
						
						break;
					}
				}
				
				if (! $found) {
					return false;
				}
			}
			
			$hisChildren = $cond->children;
			foreach ($this->children as $child) {
				$found = false;
				for ($i = 0; $i < $hisCCount; ++ $i) {
					if ($child->equal($hisChildren[$i])) {
						unset($hisChildren[$i]);
						$found = true;
						
						break;
					}
				}
				
				if (! $found) {
					return false;
				}
			}
			
			return true;
		}
	}
}

namespace Condition {

	class Item {
		
		/**
		 * 键
		 *
		 * @var string
		 */
		public $key;
		
		/**
		 * 对应键的搜索值
		 *
		 * @var string
		 */
		public $value;
		
		/**
		 * 操作。
		 *
		 * @var string
		 */
		public $operation;

		public function equal (Item $item) {
			if ($this->key == $item->key && $this->value == $item->value &&
					 $this->operation == $item->operation) {
				return true;
			}
			
			return false;
		}
	}
}
?>