<?php
namespace Framework\Http;

use Framework\IHttpResponse;
use Framework\HFC\Exception\ParameterErrorException;
use Framework\Exception\NotImplementedException;

/**
 * 目前支持http1.0，不支持chunked
 *
 * @author Hoheart
 */
class HttpResponse extends HttpMessage implements IHttpResponse
{

    /**
     *
     * @var int $mStatusCode
     */
    protected $mStatusCode = 200;

    /**
     *
     * @var string $mReasonPhrase
     */
    protected $mReasonPhrase = 'OK';

    /**
     *
     * @param string $str
     *            整个响应包
     */
    public function __construct($str = '')
    {
        if ('' !== $str) {
            self::parse($str, $this);
        }
    }

    /**
     *
     * @param string $str
     *            str
     * @throws ParameterErrorException
     * @return HttpResponse
     */
    public static function parse($str)
    {
        if (! is_string($str) || '' === $str) {
            throw new ParameterErrorException();
        }
        
        $resp = new HttpResponse();
        $pos = self::parseCommandLine($str, $resp);
        $bodyPos = self::parseHeaderMap($str, $pos, $resp);
        $resp->mBody = substr($str, $bodyPos + 4);
        
        return $resp;
    }

    protected static function parseCommandLine($str, $resp)
    {
        $pos = strpos($str, "\r\n");
        if (false === $pos) {
            throw new ParameterErrorException();
        }
        
        $cmdLine = substr($str, 0, $pos);
        list ($version, $code, $phrase) = explode(' ', $cmdLine);
        $resp->setVersion($version);
        $resp->setStatusCode($code);
        $resp->setReasonPhrase($phrase);
        
        return $pos + 2;
    }

    protected static function parseHeaderMap($str, $pos, $resp)
    {
        $bodyPos = strpos($str, "\r\n\r\n", $pos);
        if (false === $bodyPos) {
            $bodyPos = strlen($str);
        }
        
        $strLen = strlen($str);
        $endPos = 0;
        do {
            $endPos = strpos($str, "\r\n", $pos);
            if (false === $endPos) {
                $endPos = $strLen;
            }
            
            $line = substr($str, $pos, $endPos - $pos);
            $posSemicolon = strpos($line, ':');
            $key = substr($line, 0, $posSemicolon);
            $val = substr($line, $posSemicolon + 1);
            $val = trim($val);
            if (HttpMessage::HEADER_CONTENT_LENGTH == $key) {
                $val = (int) $val;
                $resp->setHeader(HttpMessage::HEADER_CONTENT_LENGTH, $val);
            } elseif ('Set-Cookie' === $key) {
                list ($cookieName, $cookie) = self::parseCookie($val);
                $resp->setCookie($cookieName, $cookie['value'], $cookie['expires'], $cookie['path'], $cookie['domain'], @$cookie['secure'], @$cookie['httponly']);
            } else {
                $resp->mHeader[$key] = $val;
            }
            
            $pos = $endPos + 2;
        } while ($endPos < $bodyPos);
        
        return $bodyPos;
    }

    protected static function parseCookie($str)
    {
        $key = '';
        $cookie = array();
        $arr = explode(';', $str);
        foreach ($arr as $one) {
            $one = trim($one);
            $pos = strpos($one, '=');
            if (false === $pos) {
                if ('secure' === $one) {
                    $cookie['secure'] = true;
                } elseif ('httponly' === $one) {
                    $cookie['httponly'] = true;
                }
            } else {
                $name = substr($one, 0, $pos);
                $val = substr($one, $pos + 1);
                $val = trim($val);
                if ('' === $key) {
                    $key = $name;
                    $cookie['value'] = urldecode($val);
                } else {
                    if ('expires' === $name) {
                        $val = @strtotime($val);
                        
                        $cookie['expires'] = $val;
                    } else {
                        $cookie[$name] = $val;
                    }
                }
            }
        }
        
        return array(
            $key,
            $cookie
        );
    }

    public function setStatusCode($code)
    {
        $this->mStatusCode = (int) $code;
    }

    public function getStatusCode()
    {
        return $this->mStatusCode;
    }

    public function sestReasonPhrase($phrase)
    {
        $this->mReasonPhrase = $phrase;
    }

    public function getReasonPhrase()
    {
        return $this->mReasonPhrase;
    }

    public function setVersion($version)
    {
        list ($name, $num) = explode('/', $version);
        if ('HTTP' !== $name) {
            throw new NotImplementedException();
        }
        if ($num > 1.1) {
            throw new NotImplementedException();
        }
        
        $this->mVersion = $version;
    }

    public function getVersion()
    {
        return $this->mVersion;
    }

    public function setReasonPhrase($reason)
    {
        $this->mReasonPhrase = $reason;
    }

    protected function packOneCookie($key, $cookie)
    {
        $s = '';
        
        if (is_string($cookie)) {
            $s = "Set-Cookie: $key=" . urlencode($cookie) . "\r\n";
        } else {
            $s = "Set-Cookie: $key=" . urlencode($cookie['value']) . "; ";
            if (0 != $cookie['expire']) {
                $s .= date('D, d-M-Y H:i:s e', $cookie['expire']);
            }
            if ('' !== $cookie['path']) {
                $s .= $cookie['path'];
            }
            if ('' !== $cookie['domain']) {
                $s .= $cookie['domain'];
            }
            if (true === $cookie['secure']) {
                $s .= 'secure';
            }
            if (true === $cookie['httponly']) {
                $s .= 'httponly';
            }
            
            $s .= "\r\n";
        }
    }
}
