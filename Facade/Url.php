<?php

/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2016/3/15
 * Time: 17:30
 */
namespace Framework\Facade;

use Framework\Router\RegexUrlManager;

class Url {

	/**
	 * 根据路由配置rules.php生产url
	 *
	 * 如 配置文件
	 * [
	 * '/' => 'UserPage/Index/Index/index',
	 * '/<city:\w+>/mendian' => 'UserPage/Shop/Index/index',
	 * ]
	 *
	 * 则
	 * Url::to(['UserPage/Index/Index/index']);
	 * 返回: /
	 *
	 *
	 * Url::to(['UserPage/Index/Index/index', 'test' => 1]);
	 * 返回: /?test=1
	 *
	 *
	 * Url::to(['UserPage/Index/Index/index', 'test' => 1, '#' => 'tag']);
	 * 返回: /?test=1#tag
	 *
	 * Url::to(['UserPage/Shop/Index/index', 'city' => "beijing"]);
	 * 返回: /beijing/mendian
	 *
	 * * Url::to(['UserPage/Shop/Index/index', 'city' => "beijing", "test" =>
	 * "111"]);
	 * 返回: /beijing/mendian?test=111
	 *
	 * Url::to(['UserPage/Index/Index/index', 'test' => 1], true);
	 * 返回：http://www.example.com/?test=1
	 *
	 * Url::to(['UserPage/Index/Index/index', 'test' => 1], 'https');
	 * 返回：https://www.example.com/?test=1
	 *
	 * @param string|array $route
	 *        	use a string to represent a route (e.g. `index`,
	 *        	`site/index`),
	 *        	or an array to represent a route with query parameters (e.g.
	 *        	`['site/index', 'param1' => 'value1']`).
	 * @param boolean|string $scheme
	 *        	the URI scheme to use in the generated URL:
	 *        	
	 *        	- `false` (default): generating a relative URL.
	 *        	- `true`: returning an absolute base URL whose scheme is the
	 *        	same as that in [[\yii\web\UrlManager::hostInfo]].
	 *        	- string: generating an absolute URL with the specified scheme
	 *        	(either `http` or `https`).
	 *        	
	 * @return string the generated URL
	 * @throws InvalidParamException a relative route is given while there is no
	 *         active controller
	 */
	public static function to ($route, $scheme = false) {
		$route = (array) $route;
		if ($scheme) {
			return (new RegexUrlManager())->createAbsoluteUrl($route, is_string($scheme) ? $scheme : null);
		} else {
			return (new RegexUrlManager())->createUrl($route);
		}
	}
}