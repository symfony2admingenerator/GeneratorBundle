<?php

namespace Admingenerator\GeneratorBundle\SessionSerializer;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class SessionSerializedModel
{
    protected $class;

    protected $pk;

    public function __construct($class, $pk)
    {
        $this->class = $class;
        $this->pk = $pk;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPk()
    {
        return $this->pk;
    }
}