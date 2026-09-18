<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Stripe BalanceTransaction fee/net is not published yet (asynchronous capture).
 * Webhook callers MUST return 200 pending, not 502.
 */
class StripeSettlementNotReadyException extends RuntimeException {}
