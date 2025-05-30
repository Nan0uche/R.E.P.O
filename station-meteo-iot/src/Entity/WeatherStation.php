<?php

namespace App\Entity;

use App\Repository\WeatherStationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WeatherStationRepository::class)]
class WeatherStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 17, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/',
        message: 'L\'adresse MAC doit être au format XX:XX:XX:XX:XX:XX'
    )]
    private ?string $macAddress = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    /**
     * @var Collection<int, WeatherData>
     */
    #[ORM\OneToMany(targetEntity: WeatherData::class, mappedBy: 'station', orphanRemoval: true)]
    private Collection $weatherData;

    public function __construct()
    {
        $this->weatherData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }

    public function setMacAddress(string $macAddress): static
    {
        $this->macAddress = strtoupper($macAddress);
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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

    /**
     * @return Collection<int, WeatherData>
     */
    public function getWeatherData(): Collection
    {
        return $this->weatherData;
    }

    public function addWeatherData(WeatherData $weatherData): static
    {
        if (!$this->weatherData->contains($weatherData)) {
            $this->weatherData->add($weatherData);
            $weatherData->setStation($this);
        }

        return $this;
    }

    public function removeWeatherData(WeatherData $weatherData): static
    {
        if ($this->weatherData->removeElement($weatherData)) {
            // set the owning side to null (unless already changed)
            if ($weatherData->getStation() === $this) {
                $weatherData->setStation(null);
            }
        }

        return $this;
    }
}
