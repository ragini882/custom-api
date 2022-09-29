<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait ResponseTrait
{
    private $status_validation_failed = 422;
    private $status_unauthorized = 401;
    private $status_forbidden = 403;

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

    /**
     * Returns http unauthorized response with 401 status code
     * 
     * @param string (optional) $message
     * 
     * @returns \Illuminate\Http\JsonResponse
     */
    public function sendUnauthorizedResponse($message = null)
    {
        return $this->sendJsonResponse(
            [
                "code" => $this->status_unauthorized,
                "message" => $message ?? "Unauthorized",
                "data" => (object)[],
            ],
            $this->status_unauthorized
        );
    }
}
