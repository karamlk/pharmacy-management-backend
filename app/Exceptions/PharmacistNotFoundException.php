<?php

namespace App\Exceptions;

use Exception;

class PharmacistNotFoundException extends Exception
{
    public function render()
    {
        return response()->json([
            'error' => 'user not found',
        ], 404);
    }
}