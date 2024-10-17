<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidDataException extends Exception
{
public function render(): JsonResponse
{
    return response()->json([
        'message' => 'Invalid Data',
        'details' => $this->getMessage(),
    ], 422);
}
}
