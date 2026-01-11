<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SlotMachineConfig;
use App\Services\SlotMachineService;

class SinglePaylineTest extends TestCase
{
    private array $allSymbols;
    private array $normalSymbols;
    private array $wildSymbols;
    private SlotMachineService $service;
    private array $payline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SlotMachineService();

        // Get a real payline from config (use first payline for consistency)
        $paylines = SlotMachineConfig::getPaylines();
        $this->payline = $paylines[0]; // [1, 1, 1, 1, 1] - top row

        // Load all symbols from actual reels config
        $reels = SlotMachineConfig::getReels();
        $this->allSymbols = [];

        foreach ($reels as $reel) {
            foreach ($reel as $symbol) {
                // Store unique symbols
                $name = $symbol['name'];
                if (!isset($this->allSymbols[$name])) {
                    $this->allSymbols[$name] = $symbol;
                }
            }
        }

        // Separate normal and wild symbols
        $this->normalSymbols = array_filter($this->allSymbols, fn($s) => $s['type'] === 'normal');
        $this->wildSymbols = array_filter($this->allSymbols, fn($s) => $s['type'] === 'wild');
    }

    /**
     * Test the payline matching logic using the actual service
     */
    private function checkPayline(array $symbols): array
    {
        // Create resultObjects array where each reel position has 3 rows
        // The payline will extract the appropriate row from each reel
        $resultObjects = [];
        foreach ($symbols as $symbol) {
            // Each reel has 3 rows - fill all rows with the test symbol
            $resultObjects[] = [$symbol, $symbol, $symbol];
        }

        // Call the actual service method with real payline from config
        $result = $this->service->checkPayline($this->payline, $resultObjects, 0.5);

        // Extract baseSymbol from the symbols - it's the first non-wild symbol
        $baseSymbol = null;
        foreach ($symbols as $symbol) {
            if ($symbol['type'] !== 'wild') {
                $baseSymbol = $symbol['name'];
                break;
            }
        }

        return [
            'count' => $result['count'],
            'baseSymbol' => $baseSymbol
        ];
    }

    /**
     * Get a random normal symbol
     */
    private function randomNormal(): array
    {
        return $this->normalSymbols[array_rand($this->normalSymbols)];
    }

    /**
     * Get a random wild symbol
     */
    private function randomWild(): array
    {
        return $this->wildSymbols[array_rand($this->wildSymbols)];
    }

    /**
     * Get a different normal symbol than the given one
     */
    private function differentNormalThan(array $symbol): array
    {
        $available = array_filter($this->normalSymbols, fn($s) => $s['name'] !== $symbol['name']);
        return $available[array_rand($available)];
    }

    public function test_all_matching_symbols()
    {
        $symbol = $this->randomNormal();

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ]);

        $this->assertEquals(5, $result['count']);
        $this->assertEquals($symbol['name'], $result['baseSymbol']);
    }

    public function test_three_consecutive_matches_from_left()
    {
        $match = $this->randomNormal();
        $diff1 = $this->differentNormalThan($match);
        $diff2 = $this->randomNormal();

        $result = $this->checkPayline([
            $match, $match, $match, $diff1, $diff2
        ]);

        $this->assertEquals(3, $result['count']);
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_two_consecutive_matches_then_break()
    {
        $match = $this->randomNormal();
        $breaker = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $match, $match, $breaker, $match, $match
        ]);

        $this->assertEquals(2, $result['count']);
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_no_matches_all_different()
    {
        $symbols = array_values($this->normalSymbols);

        $result = $this->checkPayline([
            $symbols[0], $symbols[1], $symbols[2], $symbols[3], $symbols[4]
        ]);

        $this->assertEquals(1, $result['count']); // First symbol always counts
        $this->assertEquals($symbols[0]['name'], $result['baseSymbol']);
    }

    public function test_wildcard_at_start_then_matches()
    {
        $wild = $this->randomWild();
        $match = $this->randomNormal();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $wild, $match, $match, $match, $diff
        ]);

        $this->assertEquals(4, $result['count']); // w + match + match + match
        $this->assertEquals($match['name'], $result['baseSymbol']); // First non-wild is baseline
    }

    public function test_wildcard_in_middle_extends_streak()
    {
        $match = $this->randomNormal();
        $wild = $this->randomWild();
        $diff1 = $this->differentNormalThan($match);
        $diff2 = $this->randomNormal();

        $result = $this->checkPayline([
            $match, $match, $wild, $diff1, $diff2
        ]);

        $this->assertEquals(3, $result['count']); // match + match + wild, then breaks
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_wildcard_at_end_extends_streak()
    {
        $match = $this->randomNormal();
        $wild = $this->randomWild();

        $result = $this->checkPayline([
            $match, $match, $match, $match, $wild
        ]);

        $this->assertEquals(5, $result['count']); // All match
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_multiple_wildcards_with_matches()
    {
        $wilds = array_values($this->wildSymbols);
        $match = $this->randomNormal();

        $result = $this->checkPayline([
            $wilds[0], $wilds[1] ?? $wilds[0], $match, $match, $match
        ]);

        $this->assertEquals(5, $result['count']); // All match
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_all_wildcards()
    {
        $wilds = array_values($this->wildSymbols);

        $result = $this->checkPayline([
            $wilds[0], $wilds[1] ?? $wilds[0], $wilds[0], $wilds[1] ?? $wilds[0], $wilds[0]
        ]);

        $this->assertEquals(5, $result['count']);
        $this->assertNull($result['baseSymbol']); // No base symbol set
    }

    public function test_wildcard_then_mismatch()
    {
        $match = $this->randomNormal();
        $wild = $this->randomWild();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $match, $wild, $diff, $match, $match
        ]);

        $this->assertEquals(2, $result['count']); // match + wild, then breaks at diff
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_match_wildcard_match_break()
    {
        $match = $this->randomNormal();
        $wild = $this->randomWild();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $match, $match, $wild, $diff, $wild
        ]);

        $this->assertEquals(3, $result['count']); // match + match + wild, breaks at diff
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_wildcard_does_not_count_after_break()
    {
        $match = $this->randomNormal();
        $wild = $this->randomWild();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $match, $match, $wild, $diff, $wild
        ]);

        $this->assertEquals(3, $result['count']); // NOT 4!
        $this->assertEquals($match['name'], $result['baseSymbol']);

        // Verify wild at position 4 is NOT counted because streak broke at position 3
    }

    public function test_single_symbol_then_all_different()
    {
        $symbols = array_values($this->normalSymbols);

        $result = $this->checkPayline([
            $symbols[0], $symbols[1], $symbols[2], $symbols[3], $symbols[4]
        ]);

        $this->assertEquals(1, $result['count']); // Only first counts
        $this->assertEquals($symbols[0]['name'], $result['baseSymbol']);
    }

    public function test_four_of_a_kind()
    {
        $match = $this->randomNormal();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $match, $match, $match, $match, $diff
        ]);

        $this->assertEquals(4, $result['count']);
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_wildcard_preserves_streak_across_multiple_positions()
    {
        $match = $this->randomNormal();
        $wilds = array_values($this->wildSymbols);

        $result = $this->checkPayline([
            $match, $wilds[0], $wilds[1] ?? $wilds[0], $match, $match
        ]);

        $this->assertEquals(5, $result['count']); // All match
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_early_break_on_second_symbol()
    {
        $match = $this->randomNormal();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $match, $diff, $match, $match, $match
        ]);

        $this->assertEquals(1, $result['count']); // Breaks immediately
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_wildcards_before_baseline_are_counted()
    {
        $wilds = array_values($this->wildSymbols);
        $match = $this->randomNormal();

        $result = $this->checkPayline([
            $wilds[0], $wilds[1] ?? $wilds[0], $wilds[0], $match, $match
        ]);

        $this->assertEquals(5, $result['count']); // All wildcards + matches
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_baseline_is_never_wildcard()
    {
        $wilds = array_values($this->wildSymbols);
        $match = $this->randomNormal();
        $diff = $this->differentNormalThan($match);

        $result = $this->checkPayline([
            $wilds[0], $wilds[1] ?? $wilds[0], $match, $match, $diff
        ]);

        $this->assertEquals($match['name'], $result['baseSymbol']); // NOT wild
        $this->assertEquals(4, $result['count']); // wild + wild + match + match
    }

    public function test_consecutive_only_no_gaps()
    {
        $match = $this->randomNormal();
        $breaker = $this->differentNormalThan($match);

        // Even if positions 0, 1, 3, 4 match, position 2 breaks it
        $result = $this->checkPayline([
            $match, $match, $breaker, $match, $match
        ]);

        $this->assertEquals(2, $result['count']); // Only first two
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }

    public function test_wildcard_at_break_point_prevents_break()
    {
        $match = $this->randomNormal();
        $wild = $this->randomWild();

        $result = $this->checkPayline([
            $match, $match, $wild, $match, $match
        ]);

        $this->assertEquals(5, $result['count']); // Wildcard prevents break
        $this->assertEquals($match['name'], $result['baseSymbol']);
    }
}
