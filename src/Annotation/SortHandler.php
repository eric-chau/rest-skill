<?php

declare(strict_types=1);

namespace Jarvis\Skill\Rest\Annotation;

use Jarvis\Skill\Rest\Exception\RestHttpException;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class SortHandler extends AbstractRestHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($annotation)
    {
        parent::handle($annotation);

        $sanitize = true === $annotation->sanitize;
        $accepted = false === $annotation->accepted ? false : (array) $annotation->accepted;
        $defaultSort = (array) $annotation->sort;
        $defaultDesc = (array) $annotation->desc;

        $sort = $this->request->query->get('sort');
        if (is_string($sort)) {
            $sort = explode(',', $sort);
        } else {
            $sort = $defaultSort;
        }

        if (false !== $accepted) {
            foreach ($sort as $name) {
                if (!in_array($name, $accepted)) {
                    throw new RestHttpException(sprintf(
                        "You are not allowed to sort by \"$name\". Available: %s.",
                        0 === count($accepted) ? 'none' : implode(', ', $accepted)
                    ));
                }
            }
        }

        $sort = $sanitize ? array_map([$this, 'sanitizeFieldName'], $sort) : $sort;
        $sort = array_fill_keys($sort, 'asc');

        $desc = $this->request->query->get('desc');
        if (is_string($desc)) {
            $desc = explode(',', $desc);
        } else {
            $desc = $defaultDesc;
        }

        $desc = $sanitize ? array_map([$this, 'sanitizeFieldName'], $desc) : $desc;
        foreach ($desc as $field) {
            if (isset($sort[$field])) {
                $sort[$field] = 'desc';
            } else {
                throw new RestHttpException("Cannot order by '$field' desc cause it is missing from sort list.");
            }
        }

        $this->request->attributes->add(['sort' => $sort]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Sort;
    }
}
