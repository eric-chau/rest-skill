<?php

use Jarvis\Skill\Rest\Annotation\Pagination;
use Jarvis\Skill\Rest\Annotation\PaginationHandler;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class PaginationHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $anno;
    private $handler;
    private $req;

    public function setUp()
    {
        $this->anno = new Pagination();
        $this->req = new Request();
        $this->handler = new PaginationHandler($this->req);
    }

    public function testSupports()
    {
        $this->assertTrue($this->handler->supports($this->anno));
        $this->assertFalse($this->handler->supports(new \stdClass()));
    }

    public function testHandleWithoutRange()
    {
        $this->assertFalse($this->req->attributes->has('limit'));
        $this->assertFalse($this->req->attributes->has('offset'));
        $this->assertFalse($this->req->attributes->has('maxLimit'));

        $this->handler->handle($this->anno);

        $this->assertTrue($this->req->attributes->has('limit'));
        $this->assertTrue($this->req->attributes->has('offset'));
        $this->assertTrue($this->req->attributes->has('maxLimit'));

        $this->assertSame($this->anno->limit, $this->req->attributes->get('limit'));
        $this->assertSame($this->anno->offset, $this->req->attributes->get('offset'));
        $this->assertSame($this->anno->maxLimit, $this->req->attributes->get('maxLimit'));
    }

    public function testHandleWithRange()
    {
        $this->req->query->set('range', '10-54');

        $this->handler->handle($this->anno);

        $this->assertSame(45, $this->req->attributes->get('limit'));
        $this->assertSame(10, $this->req->attributes->get('offset'));
        $this->assertSame($this->anno->maxLimit, $this->req->attributes->get('maxLimit'));
    }

    /**
     * @expectedException        Jarvis\Skill\Rest\Exception\RestHttpException
     * @expectedExceptionMessage Range offset cannot be greater than limit (100-0).
     */
    public function testThrowExceptionOnHandleRangeWithOffsetGreaterThanLastItemIndex()
    {
        $this->req->query->set('range', '100-0');

        $this->handler->handle($this->anno);
    }

    /**
     * @expectedException        Jarvis\Skill\Rest\Exception\RestHttpException
     * @expectedExceptionMessage Range limit cannot exceed 100 (0-1000).
     */
    public function testHandleRangeWithLimitExceedMaxLimitThrowsException()
    {
        $this->req->query->set('range', '0-1000');

        $this->handler->handle($this->anno);
    }

    public function testHandleRangeWithAnotherMaxLimit()
    {
        $this->anno->maxLimit = 1001;
        $this->req->query->set('range', '0-1000');

        $this->handler->handle($this->anno);

        $this->assertSame(1001, $this->req->attributes->get('maxLimit'));
    }
}
