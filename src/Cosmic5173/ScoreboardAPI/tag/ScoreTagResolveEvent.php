<?php

namespace Cosmic5173\ScoreboardAPI\tag;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class ScoreTagResolveEvent extends PlayerEvent {

    protected ScoreTag $tag;

    public function __construct(ScoreTag $tag, Player $player) {
        $this->player = $player;
        $this->tag = $tag;
    }

    /**
     * @return ScoreTag
     */
    public function getTag(): ScoreTag {
        return $this->tag;
    }

    /**
     * @param ScoreTag $tag
     */
    public function setTag(ScoreTag $tag): void {
        $this->tag = $tag;
    }
}