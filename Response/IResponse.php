<?php

namespace Framework\Response;

use Framework\Output\IOutputStream;

/**
 * 因为内存限制，不能在该对象里存储太多内容，所以一旦达到大小限制，要及时输出。
 *
 * @author Hoheart
 *        
 */
interface IResponse {

	public function setOutputStream (IOutputStream $stream);

	public function addContent ($content);

	public function getContent ();
}