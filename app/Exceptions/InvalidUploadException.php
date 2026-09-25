<?php

namespace App\Exceptions;

use Exception;

/** An uploaded file that is not an accepted image / document type. */
class InvalidUploadException extends Exception
{
    /** Keeps the API's usual error shape. */
    public function render()
    {
        return response()->json(['msg' => 'error', 'error' => $this->getMessage(), 'reason' => 'invalid_upload'], 422);
    }
}
