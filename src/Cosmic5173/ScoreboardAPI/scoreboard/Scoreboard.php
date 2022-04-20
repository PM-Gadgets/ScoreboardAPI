<?php

namespace Cosmic5173\ScoreboardAPI\scoreboard;

use Cosmic5173\ScoreboardAPI\scoreboard\factory\ScoreFactory;
use Cosmic5173\ScoreboardAPI\scoreboard\factory\ScoreFactoryException;
use Cosmic5173\ScoreboardAPI\tag\ScoreTag;
use Cosmic5173\ScoreboardAPI\tag\ScoreTagResolveEvent;
use Cosmic5173\ScoreboardAPI\utils\Utils;
use pocketmine\player\Player;

class Scoreboard {

    private string $title;
    /** @var string[] */
    private array $lines;
    /** @var Player[] */
    private array $viewers = [];

    public function __construct(string $title, array $lines = []) {
        $this->title = $title;
        $this->lines = $lines;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Scoreboard
     */
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    /**
     * @throws ScoreFactoryException
     */
    public function setLine(int $index, string $text): self {
        if ($index < 0 || $index > 15) {
            throw new \InvalidArgumentException("Line index $index is out of range");
        } else {
            $this->lines[$index] = $text;
            $this->sendLine($index);
        }
        return $this;
    }

    /**
     * @throws ScoreFactoryException
     */
    private function sendLine(int $index): self {
        $line = $this->lines[$index] ?? null;

        foreach ($this->viewers as $player) {
            if (isset($line)) {
                $tags = Utils::resolveTags($line);
                foreach ($tags as $tag) {
                    $ev = new ScoreTagResolveEvent(new ScoreTag($tag, ""), $player); $ev->call();
                    $line = str_replace($tag, $ev->getTag()->getValue(), $line);
                }

                ScoreFactory::sendLines($player, [$index => $line]);
            } else {
                $this->removeLine($index);
            }
        }

        return $this;
    }

    /**
     * @throws ScoreFactoryException
     */
    public function sendLines(Player $player): self {
        $lines = [];
        foreach ($this->lines as $index=>$line) {
            $tags = Utils::resolveTags($line);
            foreach ($tags as $tag) {
                $ev = new ScoreTagResolveEvent(new ScoreTag($tag, ""), $player); $ev->call();
                $line[$index] = str_replace($tag, $ev->getTag()->getValue(), $line);
            }
        }
        ScoreFactory::sendLines($player, $lines);
        return $this;
    }

    public function getLine(int $index): string {
        if (isset($this->lines[$index])) {
            return $this->lines[$index];
        } else {
            throw new \InvalidArgumentException("Line index $index is out of range");
        }
    }

    /**
     * @throws ScoreFactoryException
     */
    public function removeLine(int $index): self {
        if (isset($this->lines[$index])) {
            unset($this->lines[$index]);
            foreach ($this->viewers as $player)
                ScoreFactory::removeLine($player, $index);
        } else {
            throw new \InvalidArgumentException("Line $index does not exist");
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getViewers(): array {
        return $this->viewers;
    }

    /**
     * @param array $viewers
     * @return Scoreboard
     */
    public function setViewers(array $viewers): self {
        $this->viewers = $viewers;
        return $this;
    }

    /**
     * @throws ScoreFactoryException
     */
    public function sendToPlayer(Player $player): self {
        $this->viewers[$player->getXuid()] = $player;

        ScoreFactory::sendObjective($player, $this->title);
        $this->sendLines($player);
        return $this;
    }

    /**
     * @throws ScoreFactoryException
     */
    public function removeFromPlayer(Player $player): self {
        unset($this->viewers[$player->getXuid()]);
        ScoreFactory::removeObjective($player);
        return $this;
    }

    /**
     * @throws factory\ScoreFactoryException
     */
    public function update(): self {
        foreach ($this->getViewers() as $viewer) {
            $this->sendLines($viewer);
        }
        return $this;
    }

    /**
     * @throws factory\ScoreFactoryException
     */
    public function updateForPlayer(Player $player): Scoreboard {
        $this->sendLines($player);
        return $this;
    }
}