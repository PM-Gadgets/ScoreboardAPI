<?php

namespace Cosmic5173\ScoreboardAPI\tag;

use pocketmine\utils\SingletonTrait;

final class TagManager {

    use SingletonTrait;

    /** @var callable[] */
    private array $tags = [];

    public function isRegistered(string $tag): bool {
        return isset($this->tags[$tag]);
    }

    public function registerTag(string $tag, callable $callback): void {
        if(!$this->isRegistered($tag)) {
            $this->tags[$tag] = $callback;
        } else {
            throw new \InvalidArgumentException("Tag $tag is already registered");
        }
    }

    public function unregisterTag(string $tag): void {
        if($this->isRegistered($tag)) {
            unset($this->tags[$tag]);
        } else {
            throw new \InvalidArgumentException("Tag $tag is not registered");
        }
    }

    public function registerAddon(TagAddon $addon): void {
        foreach($addon->getProcessedTags() as $tag=>$callable) {
            $this->registerTag($tag, $callable);
        }
    }

    public function unregisterAddon(TagAddon $addon): void {
        foreach($addon->getProcessedTags() as $tag=>$callable) {
            $this->unregisterTag($tag);
        }
    }

    public function getTag(string $tag): callable {
        if($this->isRegistered($tag)) {
            return $this->tags[$tag];
        } else {
            throw new \InvalidArgumentException("Tag $tag is not registered");
        }
    }
}