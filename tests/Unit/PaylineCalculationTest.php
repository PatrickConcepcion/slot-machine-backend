<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SlotMachineConfig;
use App\Services\SlotMachineService;

class PaylineCalculationTest extends TestCase
{
    private array $allSymbols;
    private array $normalSymbols;
    private array $wildSymbols;
    private SlotMachineService $service;
    private array $payline;
    private array $paytable;
    private array $wildPaytable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SlotMachineService();

        // Get a real payline from config
        $paylines = SlotMachineConfig::getPaylines();
        $this->payline = $paylines[0]; // [1, 1, 1, 1, 1] - top row

        // Load paytables
        $this->paytable = config('gameplay.paytable');
        $this->wildPaytable = config('gameplay.wild_paytable');

        // Load all symbols from actual reels config
        $reels = SlotMachineConfig::getReels();
        $this->allSymbols = [];

        foreach ($reels as $reel) {
            foreach ($reel as $symbol) {
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
     * Helper to check payline and get full result including calculations
     */
    private function checkPayline(array $symbols, float $betPerLine = 0.5): array
    {
        $resultObjects = [];
        foreach ($symbols as $symbol) {
            $resultObjects[] = [$symbol, $symbol, $symbol];
        }

        return $this->service->checkPayline($this->payline, $resultObjects, $betPerLine);
    }

    /**
     * Get a symbol by name
     */
    private function getSymbol(string $name): array
    {
        return $this->allSymbols[$name];
    }

    /**
     * Get first available normal symbol
     */
    private function getFirstNormal(): array
    {
        return reset($this->normalSymbols);
    }

    /**
     * Get first available wild symbol
     */
    private function getFirstWild(): array
    {
        return reset($this->wildSymbols);
    }

    public function test_base_multiplier_calculated_correctly_for_count_3()
    {
        // Get a symbol that has a defined multiplier for count 3
        $symbol = $this->getFirstNormal();
        $expectedMult = $this->paytable[$symbol['name']][3] ?? 0;

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol,
            $this->getSymbol('s1'), $this->getSymbol('s2')
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(3, $result['count']);
        $this->assertEquals($expectedMult, $result['mult']);
        $this->assertEquals(1.2, $result['betPerLine']);
    }

    public function test_base_multiplier_calculated_correctly_for_count_4()
    {
        $symbol = $this->getFirstNormal();
        $expectedMult = $this->paytable[$symbol['name']][4] ?? 0;

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol,
            $this->getSymbol('s1')
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(4, $result['count']);
        $this->assertEquals($expectedMult, $result['mult']);
    }

    public function test_base_multiplier_calculated_correctly_for_count_5()
    {
        $symbol = $this->getFirstNormal();
        $expectedMult = $this->paytable[$symbol['name']][5] ?? 0;

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(5, $result['count']);
        $this->assertEquals($expectedMult, $result['mult']);
    }

    public function test_wild_multiplier_is_1_when_no_wilds_present()
    {
        $symbol = $this->getFirstNormal();

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(1, $result['wildMult']);
    }

    public function test_wild_multiplier_calculated_for_single_wild()
    {
        $symbol = $this->getFirstNormal();
        $wild = $this->getFirstWild();
        $expectedWildMult = $this->wildPaytable[$wild['name']] ?? 0;

        $result = $this->checkPayline([
            $wild, $symbol, $symbol, $symbol, $symbol
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(5, $result['count']);
        $this->assertEquals($expectedWildMult, $result['wildMult']);
    }

    public function test_wild_multiplier_calculated_for_multiple_wilds()
    {
        $symbol = $this->getFirstNormal();
        $wilds = array_values($this->wildSymbols);
        $wild1 = $wilds[0];
        $wild2 = $wilds[1] ?? $wilds[0];

        $expectedWildMult = ($this->wildPaytable[$wild1['name']] ?? 0) + ($this->wildPaytable[$wild2['name']] ?? 0);

        $result = $this->checkPayline([
            $wild1, $wild2, $symbol, $symbol, $symbol
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(5, $result['count']);
        $this->assertEquals($expectedWildMult, $result['wildMult']);
    }

    public function test_total_calculation_without_wilds()
    {
        $symbol = $this->getFirstNormal();
        $betPerLine = 2.0;
        $expectedMult = $this->paytable[$symbol['name']][5] ?? 0;
        $expectedTotal = $betPerLine * $expectedMult * 1; // wildMult = 1

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ], $betPerLine);

        $this->assertEquals($expectedTotal, $result['total']);
    }

    public function test_total_calculation_with_wilds()
    {
        $symbol = $this->getFirstNormal();
        $wild = $this->getFirstWild();
        $betPerLine = 2.0;

        $expectedMult = $this->paytable[$symbol['name']][5] ?? 0;
        $expectedWildMult = $this->wildPaytable[$wild['name']] ?? 0;
        $expectedTotal = $betPerLine * $expectedMult * $expectedWildMult;

        $result = $this->checkPayline([
            $wild, $symbol, $symbol, $symbol, $symbol
        ], $betPerLine);

        $this->assertEquals($expectedTotal, $result['total']);
    }

    public function test_loss_when_count_less_than_3()
    {
        $symbols = array_values($this->normalSymbols);

        $result = $this->checkPayline([
            $symbols[0], $symbols[0],
            $symbols[1], $symbols[2], $symbols[3]
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(2, $result['count']);
        $this->assertEquals(0, $result['mult']);
        $this->assertEquals(0, $result['wildMult']);
        $this->assertEquals(0, $result['total']);
    }

    public function test_bet_per_line_reflected_in_result()
    {
        $symbol = $this->getFirstNormal();
        $betPerLine = 6.0; // From total bet 120 / 20 paylines

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ], $betPerLine);

        $this->assertEquals($betPerLine, $result['betPerLine']);
    }

    public function test_calculation_with_different_bet_amounts()
    {
        $symbol = $this->getFirstNormal();
        $mult = $this->paytable[$symbol['name']][3] ?? 0;

        // Test with different valid bet per line amounts
        // These correspond to total bets: 10, 40, 100, 120 divided by 20 paylines
        $betAmounts = [0.5, 2.0, 5.0, 6.0];

        foreach ($betAmounts as $betPerLine) {
            $result = $this->checkPayline([
                $symbol, $symbol, $symbol,
                $this->getSymbol('s1'), $this->getSymbol('s2')
            ], $betPerLine);

            $expectedTotal = $betPerLine * $mult * 1; // wildMult = 1
            $this->assertEquals($expectedTotal, $result['total']);
        }
    }

    public function test_wilds_only_counted_in_winning_portion()
    {
        $symbol = $this->getFirstNormal();
        $wild = $this->getFirstWild();
        $diff = array_values(array_filter($this->normalSymbols, fn($s) => $s['name'] !== $symbol['name']))[0];

        // Pattern: match, match, wild, diff, wild
        // Should only count first wild (positions 0-2), not the wild after break
        $result = $this->checkPayline([
            $symbol, $symbol, $wild, $diff, $wild
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(3, $result['count']);
        $expectedWildMult = $this->wildPaytable[$wild['name']] ?? 0;
        $this->assertEquals($expectedWildMult, $result['wildMult']);
    }

    public function test_all_wilds_calculation()
    {
        $wilds = array_values($this->wildSymbols);
        $wild1 = $wilds[0];
        $wild2 = $wilds[1] ?? $wilds[0];

        // All wilds - no base symbol, no multiplier
        $result = $this->checkPayline([
            $wild1, $wild2, $wild1, $wild2, $wild1
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertEquals(5, $result['count']);
        $this->assertEquals(0, $result['mult']); // No base symbol
        $this->assertEquals(0, $result['wildMult']); // No base symbol, no calculation
        $this->assertEquals(0, $result['total']);
    }

    public function test_payline_check_includes_all_fields()
    {
        $symbol = $this->getFirstNormal();

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ], 1.2); // From total bet 24 / 20 paylines

        // Verify all required fields are present
        $this->assertArrayHasKey('paylineCheck', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('betPerLine', $result);
        $this->assertArrayHasKey('mult', $result);
        $this->assertArrayHasKey('wildMult', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_payline_check_returns_symbol_names()
    {
        $symbol = $this->getFirstNormal();

        $result = $this->checkPayline([
            $symbol, $symbol, $symbol, $symbol, $symbol
        ], 1.2); // From total bet 24 / 20 paylines

        $this->assertIsArray($result['paylineCheck']);
        $this->assertCount(5, $result['paylineCheck']);
        $this->assertEquals($symbol['name'], $result['paylineCheck'][0]);
    }
}
