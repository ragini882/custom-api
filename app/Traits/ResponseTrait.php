<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait ResponseTrait
{
    private $status_validation_failed = 422;

    /**
     * Returns http validation failed error response with 422 status code
     * 
     * @param array $data
     * @param string $message
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function sendValidationFailedResponse($data, $message)
    {
        throw new HttpResponseException(
            response()->json([
                'data' => (object) ['errors' => $data],
                'message' => $message,
                'code' => $this->status_validation_failed
            ], $this->status_validation_failed)
                ->setEncodingOptions(JSON_NUMERIC_CHECK)
                ->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION)
                ->setEncodingOptions(JSON_UNESCAPED_SLASHES)
        );
    }
}
