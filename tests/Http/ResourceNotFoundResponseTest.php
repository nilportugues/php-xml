<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/1/15
 * Time: 12:28 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Api\Xml\Http\Message\Xml;

use NilPortugues\Api\Xml\Http\Message\ResourceNotFoundResponse;

class ResourceNotFoundResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $xml = '';
        $response = new ResourceNotFoundResponse($xml);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['text/xml; charset=utf-8'], $response->getHeader('Content-type'));
    }
}
