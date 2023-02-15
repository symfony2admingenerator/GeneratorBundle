<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Doctrine\Inflector\InflectorFactory;

/**
 * This class describes an action
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class Action
{
    protected ?string $label = null;

    protected string $icon = '';

    protected string $class = '';

    protected array $options = [];

    protected string $submit = '';

    protected string $route = '';

    protected array $params = [];

    protected string $confirmMessage = '';

    protected string $confirmModal = '';

    protected bool $csrfProtected = false;

    protected bool $forceIntermediate = false;

    protected mixed $credentials = 'AdmingenAllowed';

    public function __construct(protected readonly string $name, protected readonly string $type = 'custom')
    {
    }

    public function setProperty($option, $value): void
    {
        $option = InflectorFactory::create()->build()->classify($option);
        call_user_func_array(array($this, 'set'.$option), array($value));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTwigName(): string
    {
        return strtolower(str_replace('-', '_', $this->name));
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return $this->humanize($this->getName());
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setSubmit(bool $submit): void
    {
        $this->submit = $submit;
    }

    public function getSubmit(): bool
    {
        return $this->submit;
    }

    private function humanize(string $text): string
    {
        return ucfirst(str_replace('_', ' ', $text));
    }

    public function setConfirm(string $confirmMessage): void
    {
        $this->confirmMessage = $confirmMessage;
    }

    public function getConfirm(): string
    {
        return $this->confirmMessage;
    }

    public function setConfirmModal(string $confirmModal): void
    {
        $this->confirmModal = $confirmModal;
    }

    public function getConfirmModal(): string
    {
        return $this->confirmModal;
    }

    public function setCsrfProtected(bool $csrfProtected): void
    {
        $this->csrfProtected = $csrfProtected;
    }

    public function getCsrfProtected(): bool
    {
        return $this->csrfProtected;
    }

    public function setCredentials(mixed $credentials): void
    {
        $this->credentials = $credentials;
    }

    public function setForceIntermediate(bool $forceIntermediate): void
    {
        $this->forceIntermediate = $forceIntermediate;
    }

    public function getForceIntermediate(): bool
    {
        return $this->forceIntermediate;
    }

    public function getCredentials(): mixed
    {
        return $this->credentials;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
