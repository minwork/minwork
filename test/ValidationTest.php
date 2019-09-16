<?php

namespace Test;

use Minwork\Helper\Validation;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function validUrlsProvider(): array
    {
        return [
            ['http://foo.com/blah_blah'],
            ['http://foo.com/blah_blah_(wikipedia)_(again)'],
            ['http://userid:password@example.com:8080/'],
            ['http://foo.com/blah_(wikipedia)_blah#cite-1'],
            ['http://-.~_!$&\'()*+,;=:%40:80%2f::::::@example.com'],
            ['http://223.255.255.254'],
            ['http://a.b-c.de'],
            ['http://code.google.com/events/#&product=browser'],
        ];
    }

    public function invalidUrlsProvider(): array
    {
        return [
            ['http://'],
            ['http://??/	'],
            ['http://foo.bar/foo(bar)baz quux'],
            ['http://a.b-.co'],
            ['http://3628126748'],
            ['http://.www.foo.bar./']
        ];
    }

    /**
     * @dataProvider validUrlsProvider
     * @param string $url
     */
    public function testValidationValidUrls(string $url)
    {
        $this->assertTrue(Validation::isUrl($url));
    }

    /**
     * @dataProvider invalidUrlsProvider
     * @param string $url
     */
    public function testValidationInvalidUrls(string $url)
    {
        $this->assertFalse(Validation::isUrl($url));
    }
}