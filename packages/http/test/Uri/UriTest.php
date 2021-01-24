<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Uri;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Uri;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of Uri
 *
 * @since 2.1
 */
class UriTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?Uri $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function setUp(): void
    {
        $this->instance = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');
    }

    /**
     * testConstruct
     *
     * @return  void
     */
    public function testConstruct()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

        self::assertEquals('https', $uri->getScheme());
        self::assertEquals('user:pass', $uri->getUserInfo());
        self::assertEquals('local.example.com', $uri->getHost());
        self::assertEquals(3001, $uri->getPort());
        self::assertEquals('user:pass@local.example.com:3001', $uri->getAuthority());
        self::assertEquals('/foo', $uri->getPath());
        self::assertEquals('bar=baz', $uri->getQuery());
        self::assertEquals('quz', $uri->getFragment());
    }

    /**
     * testWithScheme
     *
     * @return  void
     */
    public function testWithScheme()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('http');
        self::assertNotSame($uri, $new);
        self::assertEquals('http', $new->getScheme());
        self::assertEquals('http://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);

        $new = $uri->withScheme('https://');
        self::assertEquals('https', $new->getScheme());
    }

    /**
     * testWithUserInfo
     *
     * @return  void
     */
    public function testWithUserInfo()
    {
        // User
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('flower');
        self::assertNotSame($uri, $new);
        self::assertEquals('flower', $new->getUserInfo());
        self::assertEquals('https://flower@local.example.com:3001/foo?bar=baz#quz', (string) $new);

        // User & Password
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('flower', 'sakura');
        self::assertNotSame($uri, $new);
        self::assertEquals('flower:sakura', $new->getUserInfo());
        self::assertEquals('https://flower:sakura@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * testWithHost
     *
     * @return  void
     */
    public function testWithHost()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('windwalker.io');
        self::assertNotSame($uri, $new);
        self::assertEquals('windwalker.io', $new->getHost());
        self::assertEquals('https://user:pass@windwalker.io:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * testWithPort
     *
     * @return  void
     */
    public function testWithPort()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPort(3000);
        self::assertNotSame($uri, $new);
        self::assertEquals(3000, $new->getPort());
        self::assertEquals(
            sprintf('https://user:pass@local.example.com:%d/foo?bar=baz#quz', 3000),
            (string) $new
        );
    }

    /**
     * testWithPath
     *
     * @return  void
     */
    public function testWithPath()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPath('/bar/baz');
        self::assertNotSame($uri, $new);
        self::assertEquals('/bar/baz', $new->getPath());
        self::assertEquals('https://user:pass@local.example.com:3001/bar/baz?bar=baz#quz', (string) $new);

        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        self::assertEquals('foo/bar', $new->getPath());

        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        self::assertEquals('http://example.com/foo/bar', $new->__toString());

        // Encoded
        $uri      = $uri->withPath('/foo^bar');
        $expected = '/foo%5Ebar';
        self::assertEquals($expected, $uri->getPath());

        // Not double encoded
        $uri      = $uri->withPath('/foo%5Ebar');
        $expected = '/foo%5Ebar';
        self::assertEquals($expected, $uri->getPath());
    }

    /**
     * testWithQuery
     *
     * @return  void
     */
    public function testWithQuery()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withQuery('baz=bat');
        self::assertNotSame($uri, $new);
        self::assertEquals('baz=bat', $new->getQuery());
        self::assertEquals('https://user:pass@local.example.com:3001/foo?baz=bat#quz', (string) $new);

        // Strip query symbol
        $uri = new Uri('http://example.com');
        $new = $uri->withQuery('?foo=bar');
        self::assertEquals('foo=bar', $new->getQuery());
    }

    /**
     * testWithFragment
     *
     * @return  void
     */
    public function testWithFragment()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withFragment('qat');
        self::assertNotSame($uri, $new);
        self::assertEquals('qat', $new->getFragment());
        self::assertEquals('https://user:pass@local.example.com:3001/foo?bar=baz#qat', (string) $new);

        $uri = new Uri('http://example.com');
        $new = $uri->withFragment('#/foo/bar');
        self::assertEquals('/foo/bar', $new->getFragment());
    }

    /**
     * authorityProvider
     *
     * @return  array
     */
    public function authorityProvider(): array
    {
        return [
            'host-only' => ['http://foo.com/bar', 'foo.com'],
            'host-port' => ['http://foo.com:3000/bar', 'foo.com:3000'],
            'user-host' => ['http://me@foo.com/bar', 'me@foo.com'],
            'user-host-port' => ['http://me@foo.com:3000/bar', 'me@foo.com:3000'],
        ];
    }

    /**
     * testAuthority
     *
     * @dataProvider authorityProvider
     *
     * @param  string  $url
     * @param  string  $expected
     */
    public function testAuthority($url, $expected)
    {
        $uri = new Uri($url);
        self::assertEquals($expected, $uri->getAuthority());
    }

    public function queryStringsForEncoding(): array
    {
        return [
            'key-only' => ['k^ey', 'k%5Eey'],
            'key-value' => ['k^ey=valu`', 'k%5Eey=valu%60'],
            'array-key-only' => ['key[]', 'key%5B%5D'],
            'array-key-value' => ['key[]=valu`', 'key%5B%5D=valu%60'],
            'complex' => ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @dataProvider queryStringsForEncoding
     *
     * @param  string  $query
     * @param  string  $expected
     */
    public function testQueryEncoded($query, $expected)
    {
        $uri = new Uri();
        $uri = $uri->withQuery($query);
        self::assertEquals($expected, $uri->getQuery());

        // No double encoded
        $uri = $uri->withQuery($expected);
        self::assertEquals($expected, $uri->getQuery());
    }

    /**
     * testFragmentEncoded
     *
     * @return  void
     */
    public function testFragmentEncoded()
    {
        $uri      = new Uri();
        $uri      = $uri->withFragment('/p^th?key^=`bar#b@z');
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        self::assertEquals($expected, $uri->getFragment());

        // No double encoded
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        $uri      = $uri->withFragment($expected);
        self::assertEquals($expected, $uri->getFragment());
    }

    /**
     * seedInvalidArguments
     *
     * @return  array
     */
    public function seedInvalidArguments(): array
    {
        $methods = [
            'withScheme',
            'withUserInfo',
            'withHost',
            'withPath',
            'withQuery',
            'withFragment',
        ];

        $values = [
            'null' => null,
            'true' => true,
            'false' => false,
            'zero' => 0,
            'int' => 1,
            'zero-float' => 0.0,
            'float' => 1.1,
            'array' => ['value'],
            'object' => (object) ['value' => 'value'],
        ];

        $combinations = [];

        foreach ($methods as $method) {
            foreach ($values as $type => $value) {
                $key = sprintf('%s-%s', $method, $type);

                $combinations[$key] = [$method, $value];
            }
        }

        return $combinations;
    }

    /**
     * testPassingInvalidValueToWithMethodRaisesException
     *
     * @param $method
     * @param $value
     *
     * @return  void
     *
     * @dataProvider seedInvalidArguments
     */
    public function testInvalidArguments($method, $value)
    {
        self::assertExpectedException(
            function () use ($method, $value) {
                $uri = new Uri('https://example.com/');
                $uri->$method($value);
            },
            InvalidArgumentException::class
        );
    }

    public function testToString()
    {
        self::assertThat(
            $this->instance->toString(),
            self::equalTo('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment')
        );

        $this->instance = $this->instance->withQuery('somevar=somevalue')
            ->withVar('somevar2', 'somevalue2')
            ->withScheme('ftp')
            ->withUser('root')
            ->withPassword('secret')
            ->withHost('www.example.org')
            ->withPort('8888')
            ->withFragment('someFragment')
            ->withPath('/this/is/a/path/to/a/file');

        self::assertThat(
            $this->instance->toString(),
            self::equalTo(
                'ftp://root:secret@www.example.org:8888/this/is/a/path/to/a/file?somevar=somevalue&somevar2=somevalue2#someFragment'
            )
        );
    }

    public function testToStringParts()
    {
        self::assertEquals(
            'http://www.example.com',
            $this->instance->toString(Uri::SCHEME | Uri::HOST)
        );
        self::assertEquals(
            'someuser:somepass@www.example.com/path/file.html?var=value',
            $this->instance->toString(
                Uri::HOST
                | Uri::PATH
                | Uri::USER_INFO
                | Uri::QUERY
            )
        );
        self::assertEquals(
            'http://someuser:somepass@www.example.com:80',
            $this->instance->toString(
                Uri::FULL_HOST
            )
        );
        self::assertEquals(
            '/path/file.html?var=value#fragment',
            $this->instance->toString(
                Uri::URI | Uri::FRAGMENT
            )
        );
    }

    public function testSetVar()
    {
        $this->instance = $this->instance->withVar('somevariable', 'somevalue');

        self::assertThat(
            $this->instance->getVar('somevariable'),
            self::equalTo('somevalue')
        );
    }

    public function testHasVar()
    {
        self::assertThat(
            $this->instance->hasVar('somevariable'),
            self::equalTo(false)
        );

        self::assertThat(
            $this->instance->hasVar('var'),
            self::equalTo(true)
        );
    }

    public function testGetVar()
    {
        self::assertThat(
            $this->instance->getVar('var'),
            self::equalTo('value')
        );

        self::assertThat(
            $this->instance->getVar('var2'),
            self::equalTo('')
        );

        self::assertThat(
            $this->instance->getVar('var2', 'default'),
            self::equalTo('default')
        );
    }

    public function testWithoutVar()
    {
        self::assertThat(
            $this->instance->getVar('var'),
            self::equalTo('value')
        );

        $this->instance = $this->instance->withoutVar('var');

        self::assertThat(
            $this->instance->getVar('var'),
            self::equalTo('')
        );
    }

    /**
     * Test the getQuery method.
     *
     * @return  void
     *
     * @since   2.0
     * @covers  Uri::getQuery
     */
    public function testGetQuery()
    {
        self::assertThat(
            $this->instance->getQuery(),
            self::equalTo('var=value')
        );

        self::assertThat(
            $this->instance->getQueryValues(),
            self::equalTo(['var' => 'value'])
        );
    }

    public function testGetScheme()
    {
        self::assertThat(
            $this->instance->getScheme(),
            self::equalTo('http')
        );
    }

    public function testGetUser()
    {
        self::assertThat(
            $this->instance->getUser(),
            self::equalTo('someuser')
        );
    }

    public function testWithUser()
    {
        $this->instance = $this->instance->withUser('root');

        self::assertThat(
            $this->instance->getUser(),
            self::equalTo('root')
        );
    }

    public function testGetPass()
    {
        self::assertThat(
            $this->instance->getPassword(),
            self::equalTo('somepass')
        );
    }

    public function testWithPassword()
    {
        $this->instance = $this->instance->withPassword('secret');

        self::assertThat(
            $this->instance->getPassword(),
            self::equalTo('secret')
        );
    }

    public function testGetHost()
    {
        self::assertThat(
            $this->instance->getHost(),
            self::equalTo('www.example.com')
        );
    }

    public function testGetPort()
    {
        self::assertThat(
            $this->instance->getPort(),
            self::equalTo('80')
        );
    }

    public function testGetPath()
    {
        self::assertThat(
            $this->instance->getPath(),
            self::equalTo('/path/file.html')
        );
    }

    /**
     * Test the getFragment method.
     *
     * @return  void
     *
     * @since   2.0
     * @covers  Uri::getFragment
     */
    public function testGetFragment()
    {
        self::assertThat(
            $this->instance->getFragment(),
            self::equalTo('fragment')
        );
    }

    public function testIsSSL()
    {
        $object = new Uri('https://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');

        self::assertThat(
            $object->isSSL(),
            self::equalTo(true)
        );

        $object = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');

        self::assertThat(
            $object->isSSL(),
            self::equalTo(false)
        );
    }
}
