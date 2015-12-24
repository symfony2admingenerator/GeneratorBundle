<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Doctrine\Common\Util\Inflector;

/**
 * This class describes an action
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class Action
{
    protected $name;

    protected $type;

    protected $label;

    protected $icon;

    protected $class;

    protected $options = array();

    protected $submit;

    protected $route;

    protected $params = array();

    protected $confirmMessage;

    protected $csrfProtected = false;

    protected $forceIntermediate = false;

    protected $credentials = 'permitAll';

    public function __construct($name, $type = 'custom')
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function setProperty($option, $value)
    {
        $option = Inflector::classify($option);
        call_user_func_array(array($this, 'set'.$option), array($value));
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTwigName()
    {
        return strtolower(str_replace('-', '_', $this->name));
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        if (isset($this->label)) {
            return $this->label;
        }

        return $this->humanize($this->getName());
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param boolean $submit
     */
    public function setSubmit($submit)
    {
        $this->submit = (bool) $submit;
    }

    public function getSubmit()
    {
        return $this->submit;
    }

    private function humanize($text)
    {
        return ucfirst(str_replace('_', ' ', $text));
    }

    /**
     * @param string $confirmMessage
     */
    public function setConfirm($confirmMessage)
    {
        $this->confirmMessage = $confirmMessage;
    }

    public function getConfirm()
    {
        return $this->confirmMessage;
    }

    /**
     * @param boolean $csrfProtected
     */
    public function setCsrfProtected($csrfProtected)
    {
        $this->csrfProtected = $csrfProtected;
    }

    public function getCsrfProtected()
    {
        return $this->csrfProtected;
    }

    public function setCredentials($credentials = 'permitAll')
    {
        $this->credentials = $credentials;
    }

    public function setForceIntermediate($forceIntermediate)
    {
        $this->forceIntermediate = $forceIntermediate;
    }

    public function getForceIntermediate()
    {
        return $this->forceIntermediate;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
