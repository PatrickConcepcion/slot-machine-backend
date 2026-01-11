<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class SlotMachineConfig
{
    public static function getPaylines(): array
    {
        return Config::get('gameplay.paylines');
    }

    public static function getReels(): array
    {
        return Config::get('gameplay.reels');
    }

    public static function getMultiplier(string $symbol, int $count): float
    {
        return config("gameplay.paytable.$symbol.$count", 0);
    }

    public static function getWildMultiplier(string $symbol): float
    {
        return config("gameplay.wild_paytable.$symbol", 0);
    }
}