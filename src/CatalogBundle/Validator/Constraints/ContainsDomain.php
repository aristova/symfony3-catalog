<?php

namespace CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsDomain extends Constraint
{
    public $message = 'The URL "{{ url }}" must match the pattern: "https://market.yandex.ru/product/product_id".';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
