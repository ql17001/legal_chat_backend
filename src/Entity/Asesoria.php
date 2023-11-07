<?php

namespace App\Entity;

use App\Repository\AsesoriaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AsesoriaRepository::class)]
class Asesoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 1)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne(inversedBy: 'asesorias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $idCliente = null;

    #[ORM\ManyToOne(inversedBy: 'asesorias')]
    private ?Usuario $idAsesor = null;

    #[ORM\OneToOne(mappedBy: 'idAsesoria', cascade: ['persist', 'remove'])]
    private ?Chat $chat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getIdCliente(): ?Usuario
    {
        return $this->idCliente;
    }

    public function setIdCliente(?Usuario $idCliente): static
    {
        $this->idCliente = $idCliente;

        return $this;
    }

    public function getIdAsesor(): ?Usuario
    {
        return $this->idAsesor;
    }

    public function setIdAsesor(?Usuario $idAsesor): static
    {
        $this->idAsesor = $idAsesor;

        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(Chat $chat): static
    {
        // set the owning side of the relation if necessary
        if ($chat->getIdAsesoria() !== $this) {
            $chat->setIdAsesoria($this);
        }

        $this->chat = $chat;

        return $this;
    }
}
