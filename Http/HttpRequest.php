<?php
namespace Framework\Http;

use Framework\IHttpRequest;
use Framework\HFC\Exception\ParameterErrorException;

class HttpRequest extends HttpMessage implements IHttpRequest
{

    /**
     * header
     *
     * @var string
     */
    const HEADER_HOST = 'Host';

    const HEADER_COOKIE = 'Cookie';

    /**
     * method
     *
     * @var string
     */
    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    /**
     *
     * @var string $mMethod
     */
    protected $mMethod = self::METHOD_GET;

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
        if (self::METHOD_POST == $method) {
            $this->setHeader(self::HEADER_CONTENT_TYPE, self::CONTENT_TYPE_URLENCODED);
        }
        
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
            $this->setHeader(self::HEADER_HOST, $host);
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

    protected function packUri()
    {
        $str = '';
        foreach ($this->mQueryParamMap as $key => $val) {
            if ('' !== $str) {
                $str .= '&';
            }
            
            $str .= $key . '=' . urlencode($val);
        }
        
        $uri = '';
        if (self::METHOD_GET == $this->mMethod) {
            if (false !== strpos($this->mUri, '?')) {
                // 如果已经有?，表示有query字符了
                $uri = $this->mUri . '&' . $str;
            } else {
                $uri = $this->mUri . '?' . $str;
            }
        }
        
        return $uri;
    }

    public function pack()
    {
        $s = $this->mMethod . ' ' . $this->packUri() . ' ' . $this->mVersion . "\r\n";
        
        foreach ($this->mHeader as $key => $val) {
            $s .= "$key: $val\r\n";
        }
        
        $cookieStr = '';
        foreach ($this->mCookieMap as $key => $val) {
            if ('' !== $cookieStr) {
                $cookieStr .= '; ';
            }
            
            $cookieStr .= $key . '=';
            if (is_string($val)) {
                $cookieStr .= urlencode($val);
            } else {
                $cookieStr .= urlencode($val['value']);
            }
        }
        $s .= self::HEADER_COOKIE . $cookieStr . "\r\n";
        
        $s .= self::HEADER_CONTENT_LENGTH . ' ' . strlen($this->mBody) . "\r\n";
        
        $s .= "\r\n";
        
        $s .= $this->mBody;
        
        return $s;
    }
}
