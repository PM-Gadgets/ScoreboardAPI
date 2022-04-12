<?php

namespace Cosmic5173\ScoreboardAPI\utils;

class Utils {

    private static string $REGEX = "";

    /**
     * Massive shout-out to Cortex/Marshall for this bit of code
     * used from HRKChat
     */
    private static function REGEX(): string{
        if(self::$REGEX === ""){
            self::$REGEX = "/(?:" . preg_quote("{") . ")((?:[A-Za-z0-9_\-]{2,})(?:\.[A-Za-z0-9_\-]+)+)(?:" . preg_quote("}") . ")/";
        }

        return self::$REGEX;
    }

    /**
     * Massive shout-out to Cortex/Marshall for this bit of code
     * used from HRKChat
     */
    public static function resolveTags(string $line): array{
        $tags = [];

        if(preg_match_all(self::REGEX(), $line, $matches)){
            $tags = $matches[1];
        }

        return $tags;
    }
}