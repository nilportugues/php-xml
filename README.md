# XML Transformer

[![Build Status](https://travis-ci.org/nilportugues/php-xml.svg)]
(https://travis-ci.org/nilportugues/php-xml) 
[![Coverage Status](https://coveralls.io/repos/nilportugues/xml-transformer/badge.svg?branch=master&service=github)]
(https://coveralls.io/github/nilportugues/xml-transformer?branch=master) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/xml-transformer/badges/quality-score.png?b=master)]
(https://scrutinizer-ci.com/g/nilportugues/xml-transformer/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/76f021fa-6559-4faf-a010-5dfb95cd70e2/mini.png)]
(https://insight.sensiolabs.com/projects/76f021fa-6559-4faf-a010-5dfb95cd70e2) [![Latest Stable Version](https://poser.pugx.org/nilportugues/xml/v/stable)]
(https://packagist.org/packages/nilportugues/xml) 
[![Total Downloads](https://poser.pugx.org/nilportugues/xml/downloads)]
(https://packagist.org/packages/nilportugues/xml) 
[![License](https://poser.pugx.org/nilportugues/xml/license)]
(https://packagist.org/packages/nilportugues/xml) 
[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://paypal.me/nilportugues)

## Installation

Use [Composer](https://getcomposer.org) to install the package:

```xml
$ composer require nilportugues/xml
```


## Usage
Given a PHP Object, and a series of mappings, the **XML Transformer** will represent the given data as a XML object.

For instance, given the following piece of code, defining a Blog Post and some comments:

```php
$post = new Post(
  new PostId(9),
  'Hello World',
  'Your first post',
  new User(
      new UserId(1),
      'Post Author'
  ),
  [
      new Comment(
          new CommentId(1000),
          'Have no fear, sers, your king is safe.',
          new User(new UserId(2), 'Barristan Selmy'),
          [
              'created_at' => (new DateTime('2015/07/18 12:13:00'))->format('c'),
              'accepted_at' => (new DateTime('2015/07/19 00:00:00'))->format('c'),
          ]
      ),
  ]
);
```

And a Mapping array for all the involved classes:

```php
use NilPortugues\Api\Mapping\Mapper;

$mappings = [
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
            'self' => 'http://example.com/posts/{postId}',
            'comments' => 'http://example.com/posts/{postId}/comments'
        ],
    ],
    [
        'class' => PostId::class,
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'postId',
        ],
        'urls' => [
            'self' => 'http://example.com/posts/{postId}',
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
        'class' => UserId::class,
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

$mapper = new Mapper($mappings);
```

Calling the transformer will output a **valid XML response** using the correct formatting:

```php
use NilPortugues\Api\Xml\XmlSerializer;
use NilPortugues\Api\Xml\Http\Message\Response;

$serializer = new XmlSerializer($mapper);
$output = $serializer->serialize($post);

//PSR7 Response with headers and content.
$response = new Response($output);

header(
    sprintf(
        'HTTP/%s %s %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    )
);
foreach($response->getHeaders() as $header => $values) {
    header(sprintf("%s:%s\n", $header, implode(', ', $values)));
}

echo $response->getBody();
```

**Output:**


```
HTTP/1.1 200 OK
Cache-Control: private, max-age=0, must-revalidate
Content-type: text/xml; charset=utf-8
```

```xml
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
```

## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via [Pull Request](https://github.com/nilportugues/xml-transformer/pulls).



## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/xml-transformer/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/xml-transformer).



## Support

Get in touch with me using one of the following means:

 - Emailing me at <contact@nilportugues.com>
 - Opening an [Issue](https://github.com/nilportugues/xml-transformer/issues/new)
 - Using Gitter: [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/nilportugues/xml-transformer?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)



## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/xml-transformer/graphs/contributors)


## License
The code base is licensed under the [MIT license](LICENSE).
