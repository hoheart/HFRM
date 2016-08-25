<?php
namespace Framework\Http;

abstract class HttpMessage
{

    const VERSION = 'HTTP/1.1';

    /**
     * 内容类型
     *
     * @var string
     */
    const CONTENT_TYPE_URLENCODED = 'application/x-www-form-urlencoded;charset=utf-8';

    const TRANSFER_ENCODING_CHUNKED = 'chunked';

    /**
     * header
     */
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    const HEADER_CONTENT_TYPE = 'Content-Type';

    const HEADER_TRANSFER_ENCODING = 'Transfer-Encoding';

    /**
     *
     * @var string
     */
    protected $mVersion = self::VERSION;

    /**
     *
     * @var map $mHeader
     */
    protected $mHeader = array();

    /**
     *
     * @var map $mCookie
     */
    protected $mCookieMap = array();

    /**
     *
     * @var string $mBody
     */
    protected $mBody = '';

    public function __construct()
    {
        // nothing
    }

    public function setHeader($fieldName, $value)
    {
        if ('Cookie' == $fieldName) {
            $this->mCookieMap[$fieldName] = $value;
        } else {
            $this->mHeader[$fieldName] = $value;
        }
    }

    public function getHeader($fieldName)
    {
        if ('Cookie' == $fieldName) {
            return $this->mCookieMap[$fieldName];
        } else {
            return @$this->mHeader[$fieldName];
        }
    }

    public function setCookie($name, $value = "", $expires = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        $map = array(
            'value' => $value
        );
        
        if (0 != $expires) {
            $map['expires'] = $expires;
        }
        if ('' !== $path) {
            $map['path'] = $path;
        }
        if ('' !== $domain) {
            $map['domain'] = $domain;
        }
        if (true === $secure) {
            $map['secure'] = $secure;
        }
        if (true === $httponly) {
            $map['httponly'] = $httponly;
        }
        if (empty($map)) {
            $this->mCookieMap[$name] = $value;
        } else {
            $this->mCookieMap[$name] = $map;
        }
    }

    public function setContentType($type)
    {
        $this->mHeader[self::HEADER_CONTENT_TYPE] = $type;
    }

    public function getContentType()
    {
        return $this->mHeader[self::HEADER_CONTENT_TYPE];
    }

    public function getCookie($name)
    {
        return $this->mCookieMap[$name];
    }

    public function getAllCookie()
    {
        return $this->mCookieMap;
    }

    public function setBody($body)
    {
        $this->mBody = $body;
    }

    public function addBody($str)
    {
        $this->mBody .= $str;
    }

    public function getBody()
    {
        return $this->mBody;
    }
}
