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
        $this->setHeader('RequestId', $this->mId);
        
        if (! empty($url)) {
            $this->setURI($url);
        }
        
        parent::__construct();
    }

    public function getId()
    {
        return $this->mId;
    }

    /**
     *
     * @see self::pack()
     * @param string $name
     *            键名
     * @param string $value
     *            键值
     */
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

    /**
     * 如果uri中含有查询参数，不会把查询参数解析到QueryParamsMap里。pack时，除了get请求外，其他请求，会原样pack此函数设置的uri。
     *
     * @param string $uri
     *            uri
     * @throws ParameterErrorException 参数错误
     */
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

    protected function packQueryMap()
    {
        $str = '';
        foreach ($this->mQueryParamMap as $key => $val) {
            if ('' !== $str) {
                $str .= '&';
            }
            
            $str .= $key . '=' . urlencode($val);
        }
        
        return $str;
    }

    protected function packUri()
    {
        $uri = $this->mUri;
        
        if (self::METHOD_GET == $this->mMethod) {
            $str = $this->packQueryMap();
            if (! empty($str)) {
                if (false !== strpos($this->mUri, '?')) {
                    // 如果已经有?，表示有query字符了
                    $uri = $this->mUri . '&' . $str;
                } else {
                    $uri = $this->mUri . '?' . $str;
                }
            }
        }
        
        return $uri;
    }

    /**
     * 会对set的key-value键值对进行pack（放入body），但只pack Content-Type为application/x-www-form-urlencoded;charset=utf-8的非get请求，其他情况，将丢弃。
     */
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
        $s .= self::HEADER_COOKIE . ': ' . $cookieStr . "\r\n";
        
        $body = $this->mBody;
        $str = $this->packQueryMap();
        if (! empty($str)) {
            if (! empty($body)) {
                $body .= '&' . $str;
            } else {
                $body = $str;
            }
        }
        $s .= self::HEADER_CONTENT_LENGTH . ': ' . strlen($body) . "\r\n";
        $s .= "\r\n";
        $s .= $body;
        
        return $s;
    }
}
