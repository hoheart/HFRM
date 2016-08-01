<?php
namespace Framework\Swoole;

use Framework\IHttpRequest;
use Framework\Exception\NotImplementedException;

class HttpRequest implements IHttpRequest
{

    /**
     *
     * @var string
     */
    const COOKIE_NAME_REQUESTID = 'RequestId';

    /**
     *
     * @var swoole_http_request
     */
    protected $mRequest = null;

    /**
     *
     * @var array $mAllParam
     */
    protected $mAllParam = array();

    public function __construct($req)
    {
        $this->mRequest = $req;
    }

    public function getId()
    {
        $id = $this->getCookie(self::COOKIE_NAME_REQUESTID);
        if (empty($id)) {
            $id = uuid_create();
            
            $this->mRequest->cookie[self::COOKIE_NAME_REQUESTID] = $id;
        }
        
        return $id;
    }

    public function get($name)
    {
        $post = $this->mRequest->post;
        $get = $this->mRequest->get;
        $cookie = $this->mRequest->cookie;
        $files = $this->files;
        if (is_array($post) && array_key_exists($name, $post)) {
            return $post[$name];
        } elseif (is_array($get) && array_key_exists($name, $get)) {
            return $get[$name];
        } elseif (is_array($cookie) && array_key_exists($name, $cookie)) {
            return $cookie[$name];
        } elseif (is_array($files) && array_key_exists($name, $files)) {
            return $files[$name];
        }
        
        return null;
    }

    public function getMethod()
    {
        return $this->mRequest->server['request_method'];
    }

    public function getURI()
    {
        return $this->getRequestURI();
    }

    public function getRequestURI()
    {
        $uri = urldecode($this->mRequest->server['request_uri']);
        return $uri;
    }

    public function isAjaxRequest()
    {
        return ! empty($this->mRequest->header['http_x_requested_with']) && strtolower($this->mRequest->header['http_x_requested_with']) == 'xmlhttprequest';
    }

    public function getHeader($fieldName)
    {
        $fieldName = strtolower($fieldName);
        return $this->mRequest->header[$fieldName];
    }

    public function getClientIP()
    {
        $IPaddress = '';
        
        if (isset($this->mRequest->header["http_x_forwarded_for"])) {
            $IPaddress = $this->mRequest->header["http_x_forwarded_for"];
        } elseif (isset($this->mRequest->header["http_client_ip"])) {
            $IPaddress = $this->mRequest->header["http_client_ip"];
        } elseif (isset($this->mRequest->server['remote_addr'])) {
            $IPaddress = $this->mRequest->server["remote_addr"];
        }
        
        return $IPaddress;
    }

    public function getAllParams()
    {
        if (empty($this->mAllParam)) {
            if (is_array($this->mRequest->get)) {
                $this->mAllParam = array_merge($this->mAllParam, $this->mRequest->get);
            }
            if (is_array($this->mRequest->post)) {
                $this->mAllParam = array_merge($this->mAllParam, $this->mRequest->post);
            }
            if (is_array($this->mRequest->cookie)) {
                $this->mAllParam = array_merge($this->mAllParam, $this->mRequest->cookie);
            }
        }
        
        return $this->mAllParam;
    }

    public function getCookie($name)
    {
        $val = '';
        if (is_array($this->mRequest->cookie)) {
            $val = $this->mRequest->cookie[$name];
        }
        
        return $val;
    }

    public function getAllCookie()
    {
        $cookieArr = array();
        if (is_array($this->mRequest->cookie)) {
            $cookieArr = $this->mRequest->cookie;
        }
        
        return $cookieArr;
    }

    public function pack()
    {
        throw new NotImplementedException();
    }
}
