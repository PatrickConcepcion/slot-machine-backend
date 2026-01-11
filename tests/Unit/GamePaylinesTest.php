<?php

namespace Tests\Unit;

use App\Services\SlotMachineConfig;
use Tests\TestCase;

class GamePaylinesTest extends TestCase
{
    public function test_all_paylines_are_properly_formatted()
    {
        $paylines = SlotMachineConfig::getPaylines();

        $this->assertIsArray($paylines);
        $this->assertCount(20, $paylines, 'Should have exactly 20 paylines');

        foreach ($paylines as $index => $payline) {
            $this->assertCount(5, $payline, "Payline {$index} should have exactly 5 positions");

            foreach ($payline as $position => $row) {
                $this->assertIsInt($row, "Payline {$index} position {$position} should be an integer");
                $this->assertGreaterThanOrEqual(1, $row, "Payline {$index} position {$position} should be >= 1");
                $this->assertLessThanOrEqual(3, $row, "Payline {$index} position {$position} should be <= 3");
            }
        }
    }

    public function test_paylines_have_no_duplicates()
    {
        $paylines = SlotMachineConfig::getPaylines();

        $serialized = array_map(fn($payline) => json_encode($payline), $paylines);
        $unique = array_unique($serialized);

        $this->assertCount(
            count($paylines),
            $unique,
            'All paylines should be unique (no duplicates)'
        );
    }

    public function test_specific_known_paylines_exist()
    {
        $paylines = SlotMachineConfig::getPaylines();

        // Test that common paylines exist
        $this->assertContains([1, 1, 1, 1, 1], $paylines, 'Top row payline should exist');
        $this->assertContains([2, 2, 2, 2, 2], $paylines, 'Middle row payline should exist');
        $this->assertContains([3, 3, 3, 3, 3], $paylines, 'Bottom row payline should exist');
    }

    public function test_paylines_with_sample_reel_result()
    {
        $paylines = SlotMachineConfig::getPaylines();

        // Create a sample reel result (5 reels x 3 rows)
        $result = [
            ['s1', 's2', 's3'],  // Reel 1
            ['s1', 's4', 's5'],  // Reel 2
            ['s1', 's6', 's7'],  // Reel 3
            ['s1', 's8', 's9'],  // Reel 4
            ['s1', 's10', 's11'], // Reel 5
        ];

        // Test the first payline [1, 1, 1, 1, 1] - should be all s1
        $firstPayline = $paylines[0];
        $symbols = [];

        foreach ($firstPayline as $index => $row) {
            $symbols[] = $result[$index][$row - 1];
        }

        $this->assertEquals(['s1', 's1', 's1', 's1', 's1'], $symbols);
    }

    public function test_all_paylines_can_be_processed()
    {
        $paylines = SlotMachineConfig::getPaylines();

        // Simulate processing each payline
        foreach ($paylines as $index => $payline) {
            $this->assertIsArray($payline, "Payline {$index} should be an array");

            // Simulate extracting symbols from a payline
            $count = 0;
            foreach ($payline as $position => $row) {
                // Just verify we can iterate through
                $count++;
            }

            $this->assertEquals(5, $count, "Should iterate through 5 positions for payline {$index}");
        }
    }

    public function test_paylines_cover_all_three_rows()
    {
        $paylines = SlotMachineConfig::getPaylines();

        $usesRow1 = false;
        $usesRow2 = false;
        $usesRow3 = false;

        foreach ($paylines as $payline) {
            if (in_array(1, $payline)) $usesRow1 = true;
            if (in_array(2, $payline)) $usesRow2 = true;
            if (in_array(3, $payline)) $usesRow3 = true;
        }

        $this->assertTrue($usesRow1, 'At least one payline should use row 1');
        $this->assertTrue($usesRow2, 'At least one payline should use row 2');
        $this->assertTrue($usesRow3, 'At least one payline should use row 3');
    }
}
