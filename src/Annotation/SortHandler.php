<?php

namespace Jarvis\Skill\Rest\Annotation;

use Jarvis\Skill\Annotation\Handler\AbstractHandler;
use Jarvis\Skill\Rest\Exception\RestHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class SortHandler extends AbstractHandler
{
    private $req;

    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($annotation)
    {
        parent::handle($annotation);

        $defaultSort = (array) $annotation->sort;
        $defaultDesc = (array) $annotation->desc;

        $sort = $this->req->query->get('sort');
        if (is_string($sort)) {
            $sort = explode(',', $sort);
        } else {
            $sort = $defaultSort;
        }

        $sort = array_fill_keys($sort, 'asc');

        $desc = $this->req->query->get('desc');
        if (is_string($desc)) {
            $desc = explode(',', $desc);
        } else {
            $desc = $defaultDesc;
        }

        foreach ($desc as $field) {
            if (isset($sort[$field])) {
                $sort[$field] = 'desc';
            } else {
                throw new RestHttpException("Cannot order '$field' by desc cause it is missing from sort field list.");
            }
        }

        $this->req->attributes->add(['sort' => $sort]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation)
    {
        return $annotation instanceof Sort;
    }
}
