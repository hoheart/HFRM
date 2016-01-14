<?php

namespace HFC\Database;

use Framework\IService;

/**
 * 独立于DBMS的抽象数据库客户端。根据配置，调用专门针对DBMS的客户端完成所需功能，基本都是调用PDO完成。
 *
 * 其实，根据php一次一进程特性，不需要设计DatabaseResult，不需要游标，结果就放在这一个类里，
 * 一次执行就返回所有结果。但也免不了先取一部分，执行完另外一些事情后，再取一部分......的用法；
 * 典型的例子就是大数据量转存。所以，两种用法都设计了。
 *
 * 目前支持的数据库有Mysql(mysqli库)、Oracle(oci8)。
 *
 * php的持久连接真是鸡肋，弃之不用，用swoole框架的数据库连接池代替。
 *
 * 因为php的PDO没有对有些数据库驱动程序根本不能实现的功能作任何说明，也不抛出任何错误，
 * 所以设计了DatabaseClient抽象类，包装了PDO。
 *
 * 默认会关闭自动提交，如果脚本结束，没有错误，就提交，否则回滚。
 *
 * @author Hoheart
 *        
 */
abstract class DatabaseClient implements IService {
	
	/**
	 * 调用select函数时，获取的最大行数。
	 *
	 * @var integer
	 */
	const MAX_ROW_COUNT = 200;
	
	/**
	 * prepare用到的属性，游标
	 *
	 * @var integer
	 */
	const ATTR_CURSOR = 10; // \PDO::ATTR_CURSOR;
	
	/**
	 * 游标类型常量
	 *
	 * @var integer
	 */
	const CURSOR_FWDONLY = 0; // \PDO::CURSOR_FWDONLY;
	const CURSOR_SCROLL = 1; // \PDO::CURSOR_SCROLL;
	
	/**
	 * 是否已经停止
	 *
	 * @var boolean
	 */
	protected $mStoped = false;
	
	/**
	 *
	 * @var array
	 */
	protected $mConf = array();
	
	/**
	 * 是否自动提交
	 *
	 * @var boolean
	 */
	protected $mAutocommit = false;

	/**
	 * 构造函数
	 *
	 * @param array $conf        	
	 */
	public function __construct (array $conf) {
		$this->init($conf);
	}

	public function __destruct () {
		if (! $this->mAutocommit) {
			// 如果没有正常stop，说明出错了，事务还没有提交，应该回滚。
			if (! $this->mStoped) {
				$this->rollBack();
			}
		}
	}

	public function init (array $conf) {
		$this->mConf = $conf;
		
		if (array_key_exists('autocommit', $this->mConf)) {
			if ($this->mConf['autocommit']) {
				$this->mAutocommit = true;
			}
		}
	}

	public function start () {
		if (! $this->mAutocommit) {
			$this->beginTransaction();
		}
		
		$this->mStoped = false;
	}

	public function stop () {
		if (! $this->mStoped) {
			if (! $this->mAutocommit) {
				$this->commit();
			}
		}
		
		$this->mStoped = true;
	}

	/**
	 * 执行非Select语句。并返回影响的行数。
	 * 一般是Insert、Update、Delete之类。
	 * 如果要从Insert语句中返回insertedId，用query方法，从返回的statement里取得。
	 *
	 * @param string $sql        	
	 * @throws DatabaseQueryException
	 *
	 * @return integer
	 */
	abstract public function exec ($sql);

	/**
	 * 执行select语句，并返回结果数组。
	 * 当SQL语句里含有变量值，都需要用参数替换，不能直接写在SQL与距离。这样才能防止SQL注入攻击。
	 *
	 * @param string $sql        	
	 * @param array $inputParams        	
	 * @param integer $start        	
	 * @param integer $size        	
	 * @throws DatabaseQueryException
	 *
	 * @return array
	 */
	abstract public function select ($sql, $inputParams, $start = 0, $size = self::MAX_ROW_COUNT);

	/**
	 * 选择一行。
	 *
	 * @param string $sql        	
	 * @param boolean $isORM        	
	 * @throws DatabaseQueryException
	 *
	 * @return array
	 */
	public function selectRow ($sql, $inputParams = array(), $isORM = false) {
		$ret = $this->select($sql, $inputParams, 0, 1, $isORM);
		
		return $ret[0];
	}

	/**
	 * 选择一个值
	 *
	 * @param string $sql        	
	 * @param boolean $isORM        	
	 * @throws DatabaseQueryException
	 *
	 * @return object
	 */
	public function selectOne ($sql, $inputParams = array(), $isORM = false) {
		$row = $this->selectRow($sql, $inputParams, $isORM);
		
		if (is_array($row)) {
			foreach ($row as $one) {
				return $one;
			}
		} else {
			return null;
		}
	}

	/**
	 * 执行SQL语句，并返回DatabaseStatement对象。可以执行任意SQL语句，但建议Select语句用select函数。
	 *
	 * 注意：一般DBMS（比如mysql），当返回的DatabaseStatement消失（closeCursor）前，不能再执行其他SQL语句。
	 *
	 * 可以调用该函数执行Insert语句，从而可以从返回的DatabaseStatement中取得lastInsertId。
	 * 本来目前采用的是短连接，没有必要这么设计，但从原则上，这样设计比较有说服力。
	 *
	 * 如果只是查询，尽量使用select，效率高，也能保证内存不超过最大值。
	 *
	 * @param string $sql        	
	 * @param integer $cursorType        	
	 * @throws DatabaseQueryException
	 *
	 * @return DatabaseStatement
	 *
	 */
	abstract public function query ($sql, $cursorType = self::CURSOR_FWDONLY);

	/**
	 * 把普通sql语句转换成limit select语句。
	 *
	 * @param string $sql        	
	 * @param integer $start        	
	 * @param integer $size        	
	 *
	 * @return string
	 */
	abstract public function transLimitSelect ($sql, $start, $size);

	/**
	 * 开始一个事务。
	 * 不建议直接调用该函数，用DatabaseTransaction对象。
	 */
	abstract public function beginTransaction ();

	/**
	 * 回滚一个事务。
	 */
	abstract public function rollBack ();

	/**
	 * 提交一个事务。
	 */
	abstract public function commit ();

	/**
	 * 判断一个事务是否已经开始而没有提交。
	 */
	abstract public function inTransaction ();

	/**
	 * Quotes a string for use in a
	 * query.如果是date、time、datetime，转换成对应数据库的SQL语句。比如，mysql就直接转换成字符串，oracle要用todate函数。
	 */
	abstract public function change2SqlValue ($str, $type = 'string');
}
?>