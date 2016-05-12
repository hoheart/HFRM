<?php

namespace Framework\Module;

interface IModule {

    /**
     * 装载模块。
     * @param string $alias
     * @param array $moduleConf 模块在系统的app配置文件中的配置
     */
	public function load ($alias, $moduleConf);

	/**
	 * 取得模块的描述
	 */
	public function getDesc ();

	/**
	 * 取得模块提供的服务
	 *
	 * @param string $name
	 *        	服务的名称
	 */
	public function getService ($name);
	
	/**
	 * 释放该模块。该模块下所有的服务将停止。
	 */
	public function release();
}