<?php

namespace NilPortugues\Api\Xml\Http\Message;

abstract class AbstractResponse extends \NilPortugues\Api\Http\Message\AbstractResponse
{
    /**
     * @var array
     */
    protected $headers = [
        'Content-type' => 'text/xml; charset=utf-8',
        'Cache-Control' => 'private, max-age=0, must-revalidate',
    ];
}
