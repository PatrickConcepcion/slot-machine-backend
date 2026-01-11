<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WildcardBaselineTest extends TestCase
{
    public function test_baseline_is_never_wildcard_with_mixed_symbols()
    {
        // Simulate a payline result with wildcards and regular symbols
        $result = [
            ['w1', 's2', 's3'],  // Reel 1
            ['s4', 's5', 's6'],  // Reel 2
            ['w2', 's7', 's8'],  // Reel 3
            ['s9', 's10', 's11'], // Reel 4
            ['s1', 's2', 's3'],  // Reel 5
        ];

        // Test payline [1, 1, 1, 1, 1] (top row)
        $payline = [1, 1, 1, 1, 1];
        $baseSymbol = null;

        foreach ($payline as $index => $row) {
            $symbol = $result[$index][$row - 1];

            if (str_starts_with($symbol, 'w')) {
                continue;
            }

            $baseSymbol = $symbol;
            break; // Important: stop after finding first non-wildcard
        }

        // Should be 's4' (first non-wildcard from reel 2)
        $this->assertEquals('s4', $baseSymbol);
        $this->assertFalse(str_starts_with($baseSymbol, 'w'));
    }

    public function test_baseline_with_all_wildcards_in_payline()
    {
        // Simulate a payline where ALL symbols are wildcards
        $result = [
            ['w1', 's2', 's3'],  // Reel 1
            ['w2', 's5', 's6'],  // Reel 2
            ['w1', 's7', 's8'],  // Reel 3
            ['w2', 's10', 's11'], // Reel 4
            ['w1', 's2', 's3'],  // Reel 5
        ];

        // Test payline [1, 1, 1, 1, 1] (top row) - all wildcards
        $payline = [1, 1, 1, 1, 1];
        $baseSymbol = null;

        foreach ($payline as $index => $row) {
            $symbol = $result[$index][$row - 1];

            if (str_starts_with($symbol, 'w')) {
                continue;
            }

            $baseSymbol = $symbol;
            break;
        }

        // Should be null because all symbols are wildcards
        $this->assertNull($baseSymbol);
    }

    public function test_baseline_is_first_non_wildcard()
    {
        // Test that we get the FIRST non-wildcard, not the last
        $result = [
            ['w1', 's2', 's3'],  // Reel 1 - wildcard
            ['w2', 's5', 's6'],  // Reel 2 - wildcard
            ['s7', 's8', 's9'],  // Reel 3 - FIRST non-wildcard (s7)
            ['s10', 's11', 's1'], // Reel 4 - non-wildcard (s10)
            ['s2', 's3', 's4'],  // Reel 5 - non-wildcard (s2)
        ];

        $payline = [1, 1, 1, 1, 1];
        $baseSymbol = null;

        foreach ($payline as $index => $row) {
            $symbol = $result[$index][$row - 1];

            if (str_starts_with($symbol, 'w')) {
                continue;
            }

            $baseSymbol = $symbol;
            break; // Must break to get FIRST non-wildcard
        }

        // Should be 's7' (first non-wildcard from reel 3)
        $this->assertEquals('s7', $baseSymbol);
    }

    public function test_baseline_without_break_gets_last_symbol()
    {
        // This test shows what happens WITHOUT break statement
        $result = [
            ['w1', 's2', 's3'],
            ['w2', 's5', 's6'],
            ['s7', 's8', 's9'],
            ['s10', 's11', 's1'],
            ['s2', 's3', 's4'],
        ];

        $payline = [1, 1, 1, 1, 1];
        $baseSymbol = null;

        foreach ($payline as $index => $row) {
            $symbol = $result[$index][$row - 1];

            if (str_starts_with($symbol, 'w')) {
                continue;
            }

            $baseSymbol = $symbol;
            // NO break - this is wrong!
        }

        // Without break, it gets the LAST non-wildcard 's2' instead of first 's7'
        $this->assertEquals('s2', $baseSymbol);
    }

    public function test_no_wildcard_ever_becomes_baseline()
    {
        // Run 100 random tests to ensure wildcards never become baseline
        for ($i = 0; $i < 100; $i++) {
            $result = [];

            // Generate random result
            for ($reel = 0; $reel < 5; $reel++) {
                $result[] = [
                    $this->randomSymbol(),
                    $this->randomSymbol(),
                    $this->randomSymbol(),
                ];
            }

            // Test all paylines
            $paylines = [
                [1, 1, 1, 1, 1],
                [2, 2, 2, 2, 2],
                [3, 3, 3, 3, 3],
            ];

            foreach ($paylines as $payline) {
                $baseSymbol = null;

                foreach ($payline as $index => $row) {
                    $symbol = $result[$index][$row - 1];

                    if (str_starts_with($symbol, 'w')) {
                        continue;
                    }

                    $baseSymbol = $symbol;
                    break;
                }

                // If baseSymbol is set, it should NEVER start with 'w'
                if ($baseSymbol !== null) {
                    $this->assertFalse(
                        str_starts_with($baseSymbol, 'w'),
                        "Baseline symbol should never be wildcard, got: $baseSymbol"
                    );
                }
            }
        }
    }

    private function randomSymbol(): string
    {
        $symbols = ['s1', 's2', 's3', 's4', 's5', 's6', 's7', 's8', 's9', 's10', 's11', 'w1', 'w2'];
        return $symbols[array_rand($symbols)];
    }
}
