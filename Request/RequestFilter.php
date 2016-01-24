<?php

namespace Framework\Request;

class RequestFilter {

	/**
	 * 去除sql注入攻击。来自360的代码
	 * 只要调用该函数，就会自动过滤GET、POST、COOKIE中的参数。
	 */
	static public function RemoveSQLInjection () {
		$getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
		$postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
		$cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

		function StopAttack ($StrFiltKey, $StrFiltValue, $ArrFiltReq) {
			if (is_array($StrFiltValue)) {
				$StrFiltValue = @implode($StrFiltValue);
			}
			if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltValue) == 1) {
				print "Illegal operation!";
				exit();
			}
		}
		
		foreach ($_GET as $key => $value) {
			StopAttack($key, $value, $getfilter);
		}
		foreach ($_POST as $key => $value) {
			StopAttack($key, $value, $postfilter);
		}
		foreach ($_COOKIE as $key => $value) {
			StopAttack($key, $value, $cookiefilter);
		}
	}

	/**
	 * @去除XSS（跨站脚本攻击）的函数
	 * @par $val 字符串参数，可能包含恶意的脚本代码如<script language="javascript">alert("hello
	 * world");</script>
	 *
	 * @return 处理后的字符串
	 * @author Rocky
	 *        
	 */
	static public function RemoveXSS ($val) {
		$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i ++) {
			
			$val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
			$val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
		}
		
		$ra1 = Array(
			'javascript',
			'vbscript',
			'expression',
			'applet',
			'meta',
			'xml',
			'blink',
			'link',
			'style',
			'script',
			'embed',
			'object',
			'iframe',
			'frame',
			'frameset',
			'ilayer',
			'layer',
			'bgsound',
			'title',
			'base'
		);
		$ra2 = Array(
			'onabort',
			'onactivate',
			'onafterprint',
			'onafterupdate',
			'onbeforeactivate',
			'onbeforecopy',
			'onbeforecut',
			'onbeforedeactivate',
			'onbeforeeditfocus',
			'onbeforepaste',
			'onbeforeprint',
			'onbeforeunload',
			'onbeforeupdate',
			'onblur',
			'onbounce',
			'oncellchange',
			'onchange',
			'onclick',
			'oncontextmenu',
			'oncontrolselect',
			'oncopy',
			'oncut',
			'ondataavailable',
			'ondatasetchanged',
			'ondatasetcomplete',
			'ondblclick',
			'ondeactivate',
			'ondrag',
			'ondragend',
			'ondragenter',
			'ondragleave',
			'ondragover',
			'ondragstart',
			'ondrop',
			'onerror',
			'onerrorupdate',
			'onfilterchange',
			'onfinish',
			'onfocus',
			'onfocusin',
			'onfocusout',
			'onhelp',
			'onkeydown',
			'onkeypress',
			'onkeyup',
			'onlayoutcomplete',
			'onload',
			'onlosecapture',
			'onmousedown',
			'onmouseenter',
			'onmouseleave',
			'onmousemove',
			'onmouseout',
			'onmouseover',
			'onmouseup',
			'onmousewheel',
			'onmove',
			'onmoveend',
			'onmovestart',
			'onpaste',
			'onpropertychange',
			'onreadystatechange',
			'onreset',
			'onresize',
			'onresizeend',
			'onresizestart',
			'onrowenter',
			'onrowexit',
			'onrowsdelete',
			'onrowsinserted',
			'onscroll',
			'onselect',
			'onselectionchange',
			'onselectstart',
			'onstart',
			'onstop',
			'onsubmit',
			'onunload'
		);
		$ra = array_merge($ra1, $ra2);
		// 不断替换 只要上次替换的值
		$found = true;
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i ++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j ++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				// 加入<> 标签
				$replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
				// 过滤 hex 标签
				$val = preg_replace($pattern, $replacement, $val);
				// 没有替换需要操作，退出循环
				if ($val_before == $val) {
					$found = false;
				}
			}
		}
		return $val;
	}
}