<?php

namespace Framework\Router;

/**
 * 正则路由器
 * Interface IRegexRouter
 * @package Framework\Router
 */
interface IRegexRouter {

    /**
     * 格式化请求
     * @param $manager
     * @param $request
     * @return array|boolean
     */
    public function parseRequest(RegexUrlManager $manager, Request $request);

    /**
     * 创建url
     * @param $manager
     * @param $route
     * @param $params
     * @return string|boolean
     */
    public function createUrl($manager, $route, $params);

}