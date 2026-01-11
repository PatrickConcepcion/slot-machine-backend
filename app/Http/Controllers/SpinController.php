<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpinRequest;
use App\Services\SlotMachineService;

class SpinController extends Controller
{
    private SlotMachineService $slotMachineService;

    public function __construct(SlotMachineService $slotMachineService)
    {
        $this->slotMachineService = $slotMachineService;
    }

    public function spin(SpinRequest $request)
    {
        $result = $this->slotMachineService->spin($request->validated('bet'));

        return response()->json($result, 200);
    }
}
