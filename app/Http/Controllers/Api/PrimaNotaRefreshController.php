<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Donations\PrimaNotaPaymentStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class PrimaNotaRefreshController extends Controller
{
    public function __construct(
        private readonly PrimaNotaPaymentStatusService $paymentStatusService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $primaNotaId = trim((string) $request->input('primaNotaId', ''));
        if ($primaNotaId === '') {
            return response()->json(['message' => 'primaNotaId is required.'], 422);
        }

        try {
            $result = $this->paymentStatusService->refreshFromPrimaNotaId($primaNotaId);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'updated' => false,
            ], 422);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
                'updated' => false,
            ], 502);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Refresh from Stripe failed.',
                'updated' => false,
            ], 500);
        }

        return response()->json($result);
    }
}
