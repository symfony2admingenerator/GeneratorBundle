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
                throw new \InvalidArgumentException();
            }
        }

        $this->errors = $errors;
    }
}
