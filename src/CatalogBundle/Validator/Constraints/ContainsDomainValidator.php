<?php
namespace CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsDomainValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/[https:\/\/yandex.ru\/product\/][0-9]+(|\/)$/', $value, $matches)) {
            $this->context->buildViolation($constraint->message)
              ->setParameter('{{ url }}', $value)
              ->addViolation();
        }
    }
}
