<?php

namespace hfc\database;

/**
 * 独立于DBMS的抽象数据库客户端。根据配置，调用专门针对DBMS的客户端完成所需功能，基本都是调用PDO完成。
 *
 * 其实，根据php一次一进程特性，不需要设计DatabaseResult，不需要游标，结果就放在这一个类里，
 * 一次执行就返回所有结果。但也免不了先取一部分，执行完另外一些事情后，再取一部分......的用法；
 * 典型的例子就是大数据量转存。所以，两种用法都设计了。
 *
 * 目前支持的数据库有Mysql(mysqli库)、Oracle(oci8)。
 *
 * php的持久连接真是鸡肋，还是弃之不用。
 *
 * 因为php的PDO没有对有些数据库驱动程序根本不能实现的功能作任何说明，也不抛出任何错误，
 * 所以设计了IClient接口，包装了PDO。
 *
 * @author Hoheart
 *        
 */
abstract class DatabaseClient {
	
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
	const ATTR_CURSOR = \PDO::ATTR_CURSOR;
	
	/**
	 * 游标类型常量
	 *
	 * @var integer
	 */
	const CURSOR_FWDONLY = \PDO::CURSOR_FWDONLY;
	const CURSOR_SCROLL = \PDO::CURSOR_SCROLL;

	/**
	 * 构造函数
	 *
	 * @param array $conf        	
	 */
	abstract public function __construct (array $conf);

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
	 *
	 * @param string $sql        	
	 * @param integer $start        	
	 * @param integer $size        	
	 * @throws DatabaseQueryException
	 *
	 * @return array
	 */
	abstract public function select ($sql, $start = 0, $size = self::MAX_ROW_COUNT);

	/**
	 * 选择一行。
	 *
	 * @param string $sql        	
	 * @throws DatabaseQueryException
	 *
	 * @return array
	 */
	public function selectRow ($sql) {
		$ret = $this->select($sql, 0, 1);
		
		return $ret[0];
	}

	/**
	 * 选择一个值
	 *
	 * @param string $sql        	
	 * @throws DatabaseQueryException
	 *
	 * @return object
	 */
	public function selectOne ($sql) {
		$row = $this->selectRow($sql);
		
		foreach ($row as $one) {
			return $one;
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