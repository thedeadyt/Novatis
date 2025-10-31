<?php

namespace App\Utils;

/**
 * Unified Response Utility
 * Standardizes JSON API responses
 */
class Response
{
    /**
     * Send success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return void
     */
    public static function success($data = null, string $message = '', int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @return void
     */
    public static function error(string $message, int $code = 400, array $errors = []): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send validation error response
     *
     * @param array $errors
     * @param string $message
     * @return void
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::error($message, 422, $errors);
    }

    /**
     * Send not found response
     *
     * @param string $message
     * @return void
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send unauthorized response
     *
     * @param string $message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden response
     *
     * @param string $message
     * @return void
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send internal server error response
     *
     * @param string $message
     * @return void
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }

    /**
     * Redirect to URL
     *
     * @param string $url
     * @param int $code
     * @return void
     */
    public static function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: $url");
        exit;
    }
}
