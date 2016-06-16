<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/20/15
 * Time: 9:04 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Xml;

use DateTime;
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Api\Xml\XmlSerializer;
use NilPortugues\Api\Xml\XmlTransformer;
use NilPortugues\Tests\Api\Xml\Dummy\ComplexObject\Comment;
use NilPortugues\Tests\Api\Xml\Dummy\ComplexObject\Post;
use NilPortugues\Tests\Api\Xml\Dummy\ComplexObject\User;
use NilPortugues\Tests\Api\Xml\Dummy\ComplexObject\ValueObject\CommentId;
use NilPortugues\Tests\Api\Xml\Dummy\ComplexObject\ValueObject\PostId;
use NilPortugues\Tests\Api\Xml\Dummy\ComplexObject\ValueObject\UserId;

class XmlSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Post
     */
    private function getPostObject()
    {
        $post = new Post(
            new PostId(9),
            'Hello World',
            'Your first post',
            new User(new UserId(1), 'Post Author'),
            [
                new Comment(
                    new CommentId(1000),
                    'Have no fear, sers, your king is safe.',
                    new User(new UserId(2), 'Barristan Selmy'),
                    [
                        'created_at' => (new DateTime('2015-07-18 12:13', new \DateTimeZone('Europe/Madrid')))->format('c'),
                        'accepted_at' => (new DateTime('2015-07-19 00:00', new \DateTimeZone('Europe/Madrid')))->format('c'),
                    ]
                ),
            ]
        );

        return $post;
    }

    /**
     *
     */
    public function testItWillRenamePropertiesAndHideFromClass()
    {
        $mappings = $this->mappings();

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <postId><![CDATA[9]]></postId>
  <headline><![CDATA[Hello World]]></headline>
  <body><![CDATA[Your first post]]></body>
  <author>
    <userId><![CDATA[1]]></userId>
    <name><![CDATA[Post Author]]></name>
  </author>
  <comments>
    <resource>
      <commentId><![CDATA[1000]]></commentId>
      <dates>
        <created_at><![CDATA[2015-07-18T12:13:00+02:00]]></created_at>
        <accepted_at><![CDATA[2015-07-19T00:00:00+02:00]]></accepted_at>
      </dates>
      <comment><![CDATA[Have no fear, sers, your king is safe.]]></comment>
      <user>
        <userId><![CDATA[2]]></userId>
        <name><![CDATA[Barristan Selmy]]></name>
      </user>
    </resource>
  </comments>
  <links>
    <link rel="self" href="http://example.com/posts/9"/>
    <link rel="comments" href="http://example.com/posts/9/comments"/>
  </links>
</data>
XML;

        $this->assertEquals(
            $expected,
            (new XmlSerializer(new Mapper($mappings)))->serialize($this->getPostObject())
        );
    }

    public function testItCanSerializeArrays()
    {
        $mappings = $this->mappings();

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <resource>
    <postId><![CDATA[9]]></postId>
    <headline><![CDATA[Hello World]]></headline>
    <body><![CDATA[Your first post]]></body>
    <author>
      <userId><![CDATA[1]]></userId>
      <name><![CDATA[Post Author]]></name>
    </author>
    <comments>
      <resource>
        <commentId><![CDATA[1000]]></commentId>
        <dates>
          <created_at><![CDATA[2015-07-18T12:13:00+02:00]]></created_at>
          <accepted_at><![CDATA[2015-07-19T00:00:00+02:00]]></accepted_at>
        </dates>
        <comment><![CDATA[Have no fear, sers, your king is safe.]]></comment>
        <user>
          <userId><![CDATA[2]]></userId>
          <name><![CDATA[Barristan Selmy]]></name>
        </user>
      </resource>
    </comments>
    <links>
      <link rel="self" href="http://example.com/posts/9"/>
      <link rel="comments" href="http://example.com/posts/9/comments"/>
    </links>
  </resource>
  <resource>
    <postId><![CDATA[9]]></postId>
    <headline><![CDATA[Hello World]]></headline>
    <body><![CDATA[Your first post]]></body>
    <author>
      <userId><![CDATA[1]]></userId>
      <name><![CDATA[Post Author]]></name>
    </author>
    <comments>
      <resource>
        <commentId><![CDATA[1000]]></commentId>
        <dates>
          <created_at><![CDATA[2015-07-18T12:13:00+02:00]]></created_at>
          <accepted_at><![CDATA[2015-07-19T00:00:00+02:00]]></accepted_at>
        </dates>
        <comment><![CDATA[Have no fear, sers, your king is safe.]]></comment>
        <user>
          <userId><![CDATA[2]]></userId>
          <name><![CDATA[Barristan Selmy]]></name>
        </user>
      </resource>
    </comments>
    <links>
      <link rel="self" href="http://example.com/posts/9"/>
      <link rel="comments" href="http://example.com/posts/9/comments"/>
    </links>
  </resource>
</data>
XML;
        $serializer = (new XmlSerializer(new Mapper($mappings)));

        $this->assertEquals(
            $expected,
            $serializer->serialize([$this->getPostObject(), $this->getPostObject()])
        );
    }

    public function testGetTransformer()
    {
        $serializer = (new XmlSerializer(new Mapper([])));

        $this->assertInstanceOf(XmlTransformer::class, $serializer->getTransformer());
    }

    /**
     * @return array
     */
    protected function mappings()
    {
        return [
            [
                'class' => Post::class,
                'alias' => 'Message',
                'aliased_properties' => [
                    'author' => 'author',
                    'title' => 'headline',
                    'content' => 'body',
                ],
                'hide_properties' => [
                ],
                'id_properties' => [
                    'postId',
                ],
                'urls' => [
                    // Mandatory
                    'self' => 'http://example.com/posts/{postId}',
                    // Optional
                    'comments' => 'http://example.com/posts/{postId}/comments',
                ],
            ],
            [
                'class' => User::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'userId',
                ],
                'urls' => [
                    'self' => 'http://example.com/users/{userId}',
                    'friends' => 'http://example.com/users/{userId}/friends',
                    'comments' => 'http://example.com/users/{userId}/comments',
                ],
            ],
            [
                'class' => Comment::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'commentId',
                ],
                'urls' => [
                    'self' => 'http://example.com/comments/{commentId}',
                ],
            ],
            [
                'class' => CommentId::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'commentId',
                ],
                'urls' => [
                    'self' => 'http://example.com/comments/{commentId}',
                ],
            ],
        ];
    }
}
