<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Donations\PrimaNotaBulkPullService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class PrimaNotaBulkPullController extends Controller
{
    public function __construct(
        private readonly PrimaNotaBulkPullService $bulkPullService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $providers = $request->input('providers', []);
        if (! is_array($providers)) {
            return response()->json(['message' => 'providers must be an array.'], 422);
        }

        $mode = trim((string) $request->input('mode', 'all'));
        $fromDate = $request->input('fromDate');
        $fromDate = is_string($fromDate) ? $fromDate : null;
        $maxItems = (int) $request->input('maxItems', 200);
        $currencies = $request->input('currencies');
        if ($currencies !== null && ! is_array($currencies)) {
            return response()->json(['message' => 'currencies must be an array.'], 422);
        }

        try {
            @set_time_limit(600);
            $result = $this->bulkPullService->pull(
                $providers,
                $mode,
                $fromDate,
                $maxItems,
                is_array($currencies) ? $currencies : null,
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Bulk pull failed: '.$exception->getMessage(),
            ], 500);
        }

        return response()->json($result);
    }
}
