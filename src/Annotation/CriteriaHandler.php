<?php

namespace Jarvis\Skill\Rest\Annotation;

use Jarvis\Skill\Rest\Criteria\AndArray;
use Jarvis\Skill\Rest\Criteria\OrArray;
use Jarvis\Skill\Rest\Exception\RestHttpException;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class CriteriaHandler extends AbstractRestHandler
{
    const AND_SEPARATOR = ';';
    const OR_SEPARATOR = ',';
    const RESERVED_WORDS = [
        'desc',
        'fields',
        'range',
        'sort',
    ];

    /**
     * {@inheritdoc}
     */
    public function handle($annotation)
    {
        parent::handle($annotation);

        $sanitize = true === $annotation->sanitize;
        $accepted = false === $annotation->accepted ? false : (array) $annotation->accepted;

        $criteria = [];
        foreach ($this->req->query->all() as $key => $value) {
            if (in_array($key, self::RESERVED_WORDS)) {
                continue;
            }

            if (false !== $accepted && !in_array($key, $accepted)) {
                throw new RestHttpException(sprintf(
                    "You are not allowed to filter by \"$key\". Available: %s.",
                    0 === count($accepted) ? 'none' : implode(', ', $accepted)
                ));
            }

            if (false !== strpos($value, ',')) {
                $value = new OrArray(explode(',', $value));
            } elseif (false !== strpos($value, ';')) {
                $value = new AndArray(explode(';', $value));
            }

            $criteria[$sanitize ? $this->sanitizeFieldName($key) : $key] = $value;
        }

        $this->req->attributes->add(['criteria' => $criteria]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation)
    {
        return $annotation instanceof Criteria;
    }
}
