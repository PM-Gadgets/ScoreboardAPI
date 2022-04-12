<?php

namespace Cosmic5173\ScoreboardAPI\scoreboard\factory;

use JetBrains\PhpStorm\Pure;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

final class ScoreFactory
{

    private const OBJECTIVE_NAME = "objective";
    private const CRITERIA_NAME  = "dummy";

    public const MIN_LINES = 0;
    public const MAX_LINES = 15;

    /**
     * @throws ScoreFactoryException
     */
    public static function sendObjective(
        Player $player,
        string $displayName,
        int $slotOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING,
        string $displaySlot = SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR,
        string $criteriaName = self::CRITERIA_NAME
    ): void {
        if (!$player->isConnected()) throw new ScoreFactoryException("Player is not connected.");

        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = $displaySlot;
        $pk->objectiveName = self::OBJECTIVE_NAME;
        $pk->displayName = $displayName;
        $pk->criteriaName = $criteriaName;
        $pk->sortOrder = $slotOrder;

        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @throws ScoreFactoryException
     */
    public static function removeObjective(Player $player): void {
        if (!$player->isConnected()) throw new ScoreFactoryException("Player is not connected.");

        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = self::OBJECTIVE_NAME;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @throws ScoreFactoryException
     */
    public static function sendLines(Player $player, array $lines = []): void {
        if (!$player->isConnected()) throw new ScoreFactoryException("Player is not connected.");

        $entries = [];
        foreach ($lines as $index=>$line)
            $entries[] = self::createEntry($index, $line);

        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries = $entries;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @throws ScoreFactoryException
     */
    public static function removeLine(Player $player, int $line): void {
        if (!$player->isConnected()) throw new ScoreFactoryException("Player is not connected.");

        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_REMOVE;
        $pk->entries = [self::createEntry($line)];
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @throws ScoreFactoryException
     */
    public static function removeLines(Player $player, $lines = []): void {
        if (!$player->isConnected()) throw new ScoreFactoryException("Player is not connected.");

        $entries = [];
        foreach ($lines as $line)
            $entries[] = self::createEntry($line);

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_REMOVE;
        $pk->entries = $entries;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    #[Pure]
    private static function createEntry(int $index, ?string $text = null): ScorePacketEntry {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = self::OBJECTIVE_NAME;
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $text ?? $entry->customName = $text;
        $entry->score = $index;
        $entry->scoreboardId = $index;
        return $entry;
    }
}