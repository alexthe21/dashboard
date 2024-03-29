<?php
/**
 * xenFramework (http://xenframework.com/)
 *
 * @link        http://github.com/xenframework for the canonical source repository
 * @copyright   Copyright (c) xenFramework. (http://xenframework.com)
 * @license     Affero GNU Public License - http://en.wikipedia.org/wiki/Affero_General_Public_License
 */

namespace xen\application;


class Response 
{
    private $_headers;
    private $_content;

    public function __construct()
    {
    }

    public function send()
    {
        return $this->_content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        header($this->_headers);
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->_statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

}