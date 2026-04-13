<?php

namespace App\Exceptions;

use Exception;

class NotPharmacistException extends Exception
{
    public function render()
    {
        return response()->json([
            'error' => 'User is not a pharmacist',
        ], 403);
    }
}