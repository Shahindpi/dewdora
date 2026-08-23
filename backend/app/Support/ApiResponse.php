<?php

namespace App\Support;

class ApiResponse
{
    /**
     * Successful response.
     */
    public static function success(
        mixed $data = null,
        string $message = 'Request successful.',
        int $status = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }


    /**
     * Error response.
     */
    public static function error(
        string $message = 'Something went wrong.',
        mixed $errors = null,
        int $status = 400
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'data' => null,
        ], $status);
    }


    /**
     * Validation error response.
     */
    public static function validation(
        mixed $errors,
        string $message = 'Validation failed.'
    ) {
        return self::error(
            $message,
            $errors,
            422
        );
    }


    /**
     * Not found response.
     */
    public static function notFound(
        string $message = 'Resource not found.'
    ) {
        return self::error(
            $message,
            null,
            404
        );
    }


    /**
     * Unauthorized response.
     */
    public static function unauthorized(
        string $message = 'Unauthenticated.'
    ) {
        return self::error(
            $message,
            null,
            401
        );
    }


    /**
     * Forbidden response.
     */
    public static function forbidden(
        string $message = 'You do not have permission to perform this action.'
    ) {
        return self::error(
            $message,
            null,
            403
        );
    }
}