<?php

namespace Framework;

/**
 * 之所以设计这个，是为了解决个服务之间的先后依赖关系，所以，先启动的服务，必须后停止。
 *
 * @author Hoheart
 *        
 */
interface IService {

	/**
	 * 对服务进行初始化。比如：建立连接以用于发送请求数据包。
	 *
	 * @param array $conf        	
	 */
	public function init (array $conf = array());

	/**
	 * 启动服务。
	 */
	public function start ();

	/**
	 * 停止服务，比如：提交事务，但不断开连接。
	 *
	 * @param bool $normal
	 *        	是否正常结束，比如数据库对象，如果不是正常结束，应该回滚 。
	 */
	public function stop ($normal = true);
}