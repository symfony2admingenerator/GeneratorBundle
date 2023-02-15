<?php

namespace Admingenerator\GeneratorBundle\Exception;

use LogicException;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationException extends LogicException
{
    /**
     * @param ConstraintViolation[] $errors An array of ConstraintViolation instances.
     */
    public function __construct(protected array $errors = [])
    {
        parent::__construct();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Validate and set errors.
     *
     * @param ConstraintViolation[] $errors An array of ConstraintViolation instances.
     */
    public function setErrors(array $errors = []): void
    {
        foreach ($errors as $error) {
            if (!$error instanceof ConstraintViolation) {
                throw new \InvalidArgumentException(sprintf('The supplied error is of class "%s", while a "Symfony\Component\Validator\ConstraintViolation" was expected.', get_class($error)));
            }
        }

        $this->errors = $errors;
    }
}
