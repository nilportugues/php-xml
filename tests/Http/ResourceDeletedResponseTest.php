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

use NilPortugues\Api\Xml\Http\Message\ResourceDeletedResponse;

class ResourceDeletedResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $response = new ResourceDeletedResponse();

        $this->assertSame(null, $response->getBody());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(['text/xml; charset=utf-8'], $response->getHeader('Content-type'));
    }
}
