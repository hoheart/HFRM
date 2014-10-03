<?php 

namespace hhp;

class Redirection{
	
	/**
	 * 请求
	 * @var IRequest
	 */
	protected $mRequest = null;
	
	protected $mRedirectRule = null;
	
	public function __construct( IRequest $request , array $redirectRule ){
		$this->mRequest = $request;
		$this->mRedirectRule = $redirectRule;
	}
	
	public function getModuleAlias(){
		$path = $this->mRequest->getResource();
		
	}
}

?>