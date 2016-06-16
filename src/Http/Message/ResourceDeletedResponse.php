<?php

namespace NilPortugues\Api\Xml\Http\Message;

class ResourceDeletedResponse extends AbstractResponse
{
    /**
     * @var int
     */
    protected $httpCode = 204;

    /**
     *
     */
    public function __construct()
    {
        $this->response = self::instance('', $this->httpCode, $this->headers);
    }

    /**
     */
    public function getBody()
    {
        return;
    }
}
