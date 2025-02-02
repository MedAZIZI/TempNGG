<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['easy', 'medium', 'hard'])]
    private ?string $difficulty = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $playedAt = null;

    /**
     * @var Collection<int, Player>
     */
    //to add, cascade: ['persist', 'remove']
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'game', cascade: ['persist', 'remove'])]
    private Collection $players;

    // Transient properties for form handling
    private ?int $numberOfPlayers = null;

    #[Assert\Count(min: 2, minMessage: 'You must have at least 2 players')]
    private array $playerNames = [];


    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->playedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getPlayedAt(): ?\DateTimeInterface
    {
        return $this->playedAt;
    }

    public function setPlayedAt(\DateTimeInterface $playedAt): static
    {
        $this->playedAt = $playedAt;

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setGame($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getGame() === $this) {
                $player->setGame(null);
            }
        }

        return $this;
    }
    // Getter and Setter for numberOfPlayers (transient)
    public function getNumberOfPlayers(): ?int
    {
        return $this->numberOfPlayers;
    }

    public function setNumberOfPlayers(?int $numberOfPlayers): static
    {
        $this->numberOfPlayers = $numberOfPlayers;
        return $this;
    }

    // Getter and Setter for playerNames (transient)
    public function getPlayerNames(): array
    {
        return $this->playerNames;
    }

    public function setPlayerNames(array $playerNames): static
    {
        $this->playerNames = $playerNames;
        return $this;
    }

}
