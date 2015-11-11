<?php

use Jarvis\Skill\Rest\Annotation\Sort;
use Jarvis\Skill\Rest\Annotation\SortHandler;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class SortHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $anno;
    private $handler;
    private $req;

    public function setUp()
    {
        $this->anno = new Sort();
        $this->req = new Request();
        $this->handler = new SortHandler($this->req);
    }

    public function testSupports()
    {
        $this->assertTrue($this->handler->supports($this->anno));
        $this->assertFalse($this->handler->supports(new \stdClass()));
    }

    public function testHandleWithoutSort()
    {
        $this->assertFalse($this->req->attributes->has('sort'));

        $this->handler->handle($this->anno);

        $this->assertTrue($this->req->attributes->has('sort'));

        $this->assertSame([], $this->req->attributes->get('sort'));
    }

    public function testHandleWithDefaultscSort()
    {
        $this->anno->sort = ['foo', 'bar'];
        $this->handler->handle($this->anno);

        $this->assertTrue($this->req->attributes->has('sort'));

        $this->assertSame(array_fill_keys($this->anno->sort, 'asc'), $this->req->attributes->get('sort'));
    }

    public function testHandleWithDefaultAscAndDescSort()
    {
        $this->anno->sort = ['foo', 'bar'];
        $this->anno->desc = 'foo';
        $this->handler->handle($this->anno);

        $this->assertTrue($this->req->attributes->has('sort'));

        $this->assertSame([
            'foo' => 'desc',
            'bar' => 'asc',
        ], $this->req->attributes->get('sort'));
    }

    public function testHandleWithProvidedAscAndDescSort()
    {
        $this->req->query->set('sort', 'foo,bar,jarvis');
        $this->req->query->set('desc', 'jarvis');

        $this->handler->handle($this->anno);

        $this->assertTrue($this->req->attributes->has('sort'));

        $this->assertSame([
            'foo'    => 'asc',
            'bar'    => 'asc',
            'jarvis' => 'desc',
        ], $this->req->attributes->get('sort'));
    }

    /**
     * @expectedException        Jarvis\Skill\Rest\Exception\RestHttpException
     * @expectedExceptionMessage Cannot order by 'foo' desc cause it is missing from sort list.
     */
    public function testProvideUnknownDescFieldThrowException()
    {
        $this->anno->desc = ['foo'];

        $this->handler->handle($this->anno);
    }

    public function testSortSanitizeOption()
    {
        $this->anno->sort = ['foo_bar'];

        $this->handler->handle($this->anno);

        $this->assertSame(['fooBar' => 'asc'], $this->req->attributes->get('sort'));

        $this->anno->sanitize = false;

        $this->handler->handle($this->anno);

        $this->assertSame(['foo_bar' => 'asc'], $this->req->attributes->get('sort'));
    }
}
