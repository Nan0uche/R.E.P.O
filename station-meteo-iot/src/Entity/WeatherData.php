<?php

namespace App\Entity;

use App\Repository\WeatherDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: WeatherDataRepository::class)]
class WeatherData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $temperature = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $humidity = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $pressure = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $windSpeed = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $windDirection = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $recordedAt = null;

    #[ORM\ManyToOne(inversedBy: 'weatherData')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getHumidity(): ?float
    {
        return $this->humidity;
    }

    public function setHumidity(float $humidity): static
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function getPressure(): ?float
    {
        return $this->pressure;
    }

    public function setPressure(float $pressure): static
    {
        $this->pressure = $pressure;

        return $this;
    }

    public function getWindSpeed(): ?float
    {
        return $this->windSpeed;
    }

    public function setWindSpeed(?float $windSpeed): static
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    public function getWindDirection(): ?string
    {
        return $this->windDirection;
    }

    public function setWindDirection(?string $windDirection): static
    {
        $this->windDirection = $windDirection;

        return $this;
    }

    public function getRecordedAt(): ?\DateTimeInterface
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(\DateTimeInterface $recordedAt): static
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
