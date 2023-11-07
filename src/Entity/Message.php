<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaEnvio = null;

    #[ORM\Column(length: 255)]
    private ?string $contenido = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chat $idChat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaEnvio(): ?\DateTimeInterface
    {
        return $this->fechaEnvio;
    }

    public function setFechaEnvio(\DateTimeInterface $fechaEnvio): static
    {
        $this->fechaEnvio = $fechaEnvio;

        return $this;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): static
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getIdChat(): ?Chat
    {
        return $this->idChat;
    }

    public function setIdChat(?Chat $idChat): static
    {
        $this->idChat = $idChat;

        return $this;
    }
}
