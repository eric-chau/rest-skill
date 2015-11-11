<?php

use Jarvis\Skill\Rest\Annotation\Criteria;
use Jarvis\Skill\Rest\Annotation\CriteriaHandler;
use Jarvis\Skill\Rest\Criteria\AndArray;
use Jarvis\Skill\Rest\Criteria\OrArray;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class CriteriaHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $anno;
    private $handler;
    private $req;

    public function setUp()
    {
        $this->anno = new Criteria();
        $this->req = new Request();
        $this->handler = new CriteriaHandler($this->req);
    }

    public function testSupports()
    {
        $this->assertTrue($this->handler->supports($this->anno));
        $this->assertFalse($this->handler->supports(new \stdClass()));
    }

    public function testHandleWithoutCriteria()
    {
        $this->assertCount(0, $this->req->query->all());

        $this->handler->handle($this->anno);

        $this->assertSame([], $this->req->attributes->get('criteria'));
    }

    /**
     * @expectedException        Jarvis\Skill\Rest\Exception\RestHttpException
     * @expectedExceptionMessage You are not allowed to filter by "category_id". Available: id.
     */
    public function testHandleNotAcceptedCriteriaThrowsException()
    {
        $this->anno->accepted = ['id'];
        $this->req->query->set('category_id', 123);

        $this->handler->handle($this->anno);
    }

    public function testHandleAcceptedCriteria()
    {
        $criteria = ['id' => 123];

        $this->anno->accepted = array_keys($criteria);
        $this->anno->sanitize = false;
        $this->req->query->add($criteria);

        $this->handler->handle($this->anno);

        $this->assertSame($criteria, $this->req->attributes->get('criteria'));
    }

    public function testHandleWithReservedWords()
    {
        $this->req->query->add(['desc' => 'id', 'sort' => 'id']);

        $this->handler->handle($this->anno);

        $this->assertSame([], $this->req->attributes->get('criteria'));
    }

    public function testHandleWithSanitizeOption()
    {
        $criteria = ['category_id' => 1];

        $this->anno->accepted = array_keys($criteria);
        $this->anno->sanitize = true;
        $this->req->query->add($criteria);

        $this->handler->handle($this->anno);

        $this->assertSame(['categoryId' => 1], $this->req->attributes->get('criteria'));
    }

    public function testHandleConvertToAndArrayAndOrArray()
    {
        $criteria = [
            'category_id' => '1,2,3',
            'keyword_id'  => '123;456;789',
        ];

        $this->anno->accepted = array_keys($criteria);
        $this->req->query->add($criteria);

        $this->handler->handle($this->anno);

        $result = $this->req->attributes->get('criteria');
        $this->assertInstanceOf(OrArray::class, $result['categoryId']);
        $this->assertSame(explode(',', $criteria['category_id']), $result['categoryId']->getArrayCopy());
        $this->assertInstanceOf(AndArray::class, $result['keywordId']);
        $this->assertSame(explode(';', $criteria['keyword_id']), $result['keywordId']->getArrayCopy());
    }
}
