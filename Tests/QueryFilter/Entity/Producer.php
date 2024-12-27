<?php
namespace Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'producer')]
class Producer
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $name = null;

    #[ORM\Column]
    protected bool $published = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPublished(mixed $published): self
    {
        $this->published = !!$published;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }
}
