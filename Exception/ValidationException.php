<?php

namespace Admingenerator\GeneratorBundle\Exception;

use Symfony\Component\Validator\ConstraintViolation;

class ValidationException extends \LogicException
{
    /**
     * @var ConstraintViolation[] An array of ConstraintViolation instances.
     */
    protected $errors;

    /**
     * @param ConstraintViolation[] $errors An array of ConstraintViolation instances.
     */
    public function __construct($errors = array())
    {
        $this->setErrors($errors);

        parent::__construct();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validate and set errors.
     *
     * @param ConstraintViolation[] $errors An array of ConstraintViolation instances.
     */
    public function setErrors($errors = array())
    {
        foreach ($errors as $error) {
            if (!$error instanceof ConstraintViolation) {
                throw new \InvalidArgumentException(sprintf('The supplied error is of class "%s", while a "Symfony\Component\Validator\ConstraintViolation" was expected.', get_class($error)));
            }
        }

        $this->errors = $errors;
    }
}
