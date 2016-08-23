<?php
namespace Framework\Http;

use Framework\IHttpRequest;
use Framework\HFC\Exception\ParameterErrorException;

class HttpRequest extends HttpMessage implements IHttpRequest
{

    const HEADER_HOST = 'Host';

    /**
     *
     * @var string $mMethod
     */
    protected $mMethod = 'GET';

    /**
     *
     * @var string $mUri
     */
    protected $mUri = '/';

    /**
     * 通过?号传递的参数
     *
     * @var map $mQueryParamMap
     */
    protected $mQueryParamMap = array();

    protected $mId = '';

    public function __construct($url = '')
    {
        $this->mId = uuid_create();
        
        if (! empty($url)) {
            $this->setURI($url);
        }
        
        parent::__construct();
    }

    public function getId()
    {
        return $this->mId;
    }

    public function set($name, $value)
    {
        $this->mQueryParamMap[$name] = $value;
    }

    public function get($name)
    {
        return $this->mQueryParamMap[$name];
    }

    public function setMethod($method)
    {
        $this->mMethod = $method;
    }

    public function getMethod()
    {
        return $this->mMethod;
    }

    public function setURI($uri)
    {
        $urlArr = parse_url($uri);
        if (false === $urlArr) {
            throw new ParameterErrorException();
        }
        
        $host = $urlArr['host'];
        if (! empty($host)) {
            if (! empty($urlArr['port'])) {
                $host .= ':' . $urlArr['port'];
            }
            $this->setHeader('Host', $host);
        }
        
        $query = '';
        if (! empty($urlArr['query'])) {
            $query = '?' . $urlArr['query'];
        }
        $this->mUri = $urlArr['path'] . $query;
    }

    public function getURI()
    {
        return $this->mUri;
    }

    public function setRequestURI($uri)
    {
        $this->setURI($uri);
    }

    public function getRequestURI()
    {
        return $this->getURI();
    }

    public function isAjaxRequest()
    {
        return strtolower($this->getHeader('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
    }

    public function getClientIP()
    {
        $IPaddress = $this->getHeader('HTTP_X_FORWARDED_FOR');
        if (! empty($IPaddress)) {
            return $IPaddress;
        }
        
        $IPaddress = $this->getHeader('HTTP_CLIENT_IP');
        if (! empty($IPaddress)) {
            return $IPaddress;
        }
        
        $IPaddress = $this->getHeader('REMOTE_ADDR');
        if (! empty($IPaddress)) {
            return $IPaddress;
        }
    }

    public function getAllParams()
    {
        return $this->mQueryParamMap;
    }

    public function pack()
    {
        $s = $this->mMethod . ' ' . $this->mUri . ' ' . $this->mVersion . "\r\n";
        
        foreach ($this->mHeader as $key => $val) {
            $s .= "$key: $val\r\n";
        }
        
        $s .= 'Content-Length: ' . strlen($this->mBody) . "\r\n";
        
        foreach ($this->mCookieMap as $key => $val) {
            $s .= $this->packOneCookie($key, $val);
        }
        
        $s .= "\r\n";
        
        $s .= $this->mBody;
        
        return $s;
    }
}