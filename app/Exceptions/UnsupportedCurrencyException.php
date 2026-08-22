<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a payment currency is not in CRM currencyList / site allowed list.
 * Callers should treat this as a soft skip (not a hard ingest failure).
 */
class UnsupportedCurrencyException extends InvalidArgumentException {}
