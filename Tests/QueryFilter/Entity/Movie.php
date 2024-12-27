<?php
namespace Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Table(name: 'movies')]
class Movie
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    protected ?string $desc = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $is_published = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    protected ?Producer $producer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDesc(string $desc): void
    {
        $this->desc = $desc;
    }

    public function getDesc(): ?string
    {
        return $this->desc;
    }

    public function setIsPublished(?bool $isPublished): void
    {
        $this->is_published = $isPublished;
    }

    public function getIsPublished(): ?bool
    {
        return $this->is_published;
    }

    public function setProducer(?Producer $producer): self
    {
        $this->producer = $producer;

        return $this;
    }

    public function getProducer(): ?Producer
    {
        return $this->producer;
    }
}
