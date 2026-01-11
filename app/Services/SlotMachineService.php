<?php

namespace App\Services;

class SlotMachineService
{
    private array $paylines;
    private array $reels;
    private array $paytable;
    private array $wildPaytable;

    public function __construct()
    {
        $this->paylines = SlotMachineConfig::getPaylines();
        $this->reels = SlotMachineConfig::getReels();
        $this->paytable = config('gameplay.paytable');
        $this->wildPaytable = config('gameplay.wild_paytable');
    }

    public function spin(float $totalBet): array
    {
        // Generate reel results
        $spinResult = $this->generateReelResults();

        // Calculate paylines
        $paylineResults = $this->calculatePaylines($spinResult['resultObjects'], $totalBet);

        return [
            'result' => $spinResult['result'],
            'allPaylines' => $paylineResults['allPaylines'], // may be removed in the final product
            'mult' => $paylineResults['grandMult'],
            'wildMult' => $paylineResults['grandWildMult'],
            'total' => $paylineResults['grandTotal'],
        ];
    }

    private function generateReelResults(): array
    {
        $resultObjects = [];
        $result = [];

        foreach ($this->reels as $reel) {
            $index = rand(0, 12);
            $prevIndex = ($index - 1 + 13) % 13;
            $nextIndex = ($index + 1) % 13;

            $resultObjects[] = [
                $reel[$prevIndex],
                $reel[$index],
                $reel[$nextIndex]
            ];

            // Store names for frontend's easier display. We may send the type back too but my main
            // purpose of using type is to determine whether it is a wildcard or not.
            $result[] = [
                $reel[$prevIndex]['name'],
                $reel[$index]['name'],
                $reel[$nextIndex]['name']
            ];
        }

        return [
            'resultObjects' => $resultObjects,
            'result' => $result
        ];
    }

    private function calculatePaylines(array $resultObjects, float $totalBet): array
    {
        // Final results/calculations
        $allPaylines = [];
        $grandMult = 0;
        $grandWildMult = 0;
        $grandTotal = 0;
        $betPerLine = $totalBet / 20;

        foreach ($this->paylines as $payline) {
            $paylineResult = $this->checkPayline($payline, $resultObjects, $betPerLine);

            $allPaylines[] = $paylineResult;

            // Ignore losses
            if ($paylineResult['total'] > 0) {
                $grandMult += $paylineResult['mult'];
                $grandWildMult += $paylineResult['wildMult'];
                $grandTotal += $paylineResult['total'];
            }
        }

        // This response is structured with debugging in mind. I also created a frontend for testing to make it 
        // easier for the examination reviewers. The $allPaylines is not necessarily needed and the response
        // can be refactored to only throw the final output. However, in staging, it may be useful as well as
        // the frontend might implement other things such as frontend line representations of the paylines.
        return [
            'allPaylines' => $allPaylines, // May be removed for final production
            'grandMult' => $grandMult,
            'grandWildMult' => $grandWildMult,
            'grandTotal' => $grandTotal
        ];
    }

    public function checkPayline(array $payline, array $resultObjects, float $betPerLine): array
    {
        $count = 0;
        $baseSymbol = null;
        $mult = 0;
        $wildMult = 0;
        $total = 0;

        $paylineCheck = [];

        // Collect all symbols for this payline. This can be integrated inside a single loop along with the match
        // logic but for the sake of developer experience, debugging and cleaner and more understandable code, I
        // decided to separate them. Due to the game's simplicity, performance impact isn't even going to be
        // noticeable.
        foreach ($payline as $index => $row) {
            $paylineCheck[] = $resultObjects[$index][$row - 1];
        }

        // Match calculation and base symbol determination logic
        foreach ($paylineCheck as $index => $symbolObj) {
            // Do not set wildcard as a teh base symbol, but count it
            if ($symbolObj['type'] === 'wild') {
                $count += 1;
                continue;
            }

            // If the base symbol is still not determined, set it
            if ($baseSymbol === null) {
                $baseSymbol = $symbolObj['name'];
                $count += 1;
                continue;
            }

            if ($symbolObj['name'] === $baseSymbol) {
                $count += 1;
            } else {
                // Stop counting/looping as the consecutive matching has been broken.
                break;
            }
        }

        // Determine the winning and calculate the multipliers
        if ($count > 2 && $baseSymbol !== null) {
            $mult = $this->paytable[$baseSymbol][$count] ?? 1;

            
            $wildMult = 0;

            // Calculate the wild multiplier. Use $count as basis instead of the payline to ensure looping only
            // iterates based on how many count was determined.
            for ($i = 0; $i < $count; $i++) {
                if ($paylineCheck[$i]['type'] === 'wild') {
                    $wildValue = $this->wildPaytable[$paylineCheck[$i]['name']] ?? 0;
                    $wildMult += $wildValue;
                }
            }

            // If no wilds, wildMult should be 1. This is initially set to zero to not interfere with the 
            // summation of all the wild multiplier results.
            if ($wildMult == 0) {
                $wildMult = 1;
            }

            $total = $betPerLine * $mult * $wildMult;
        }

        // This response is crafted with possible frontend debugging in mind.
        return [
            'paylineCheck' => array_map(fn($s) => $s['name'], $paylineCheck), // If you want the full object with the type, return $paylineCheck without the array map
            'count' => $count, // Frontend checking/debugging. This may be removed for final production
            'betPerLine' => $betPerLine,
            'mult' => $mult,
            'wildMult' => $wildMult,
            'total' => $total
        ];
    }
}
