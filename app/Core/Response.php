<?php

namespace App\Core;

use App\Exceptions\ServerException;

/**
 * Response
 * 
 * - send()
 * - getContent()
 * - getStatus()
 * - getHeaders()
 * 
 * @param mixed $content    | Contenu retourné au client
 * @param int $status       | Status code HTTP
 * @param array $headers    | Tableau associatif ['name'=>'value'] pour header() php
 */
class Response
{
    private int $status;
    private array $headers;
    public function __construct(private mixed $content = '', int $status = 200, array $headers = [])
    {
        $this->status = $this->isValidHttpCode($status) ? $status : throw new ServerException("Code HTTP invalide : " . $status) ;
        $this->headers = !array_is_list($headers) ? $headers : throw new ServerException("Données invalides :" . json_encode($headers) . "\nUn tableau associatif est attendu");
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    private function isValidHttpCode($code): bool {
        $httpCodes = [
            100, 101, 102, 103,  // 1xx - Information
            200, 201, 202, 203, 204, 205, 206, 207, 208, 226,  // 2xx - Succès
            300, 301, 302, 303, 304, 305, 306, 307, 308,  // 3xx - Redirection
            400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 421, 422, 423, 424, 425, 426, 427, 428, 429, 431, 451,  // 4xx - Erreur client
            500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511  // 5xx - Erreur serveur
        ];
        return in_array($code, $httpCodes);
    }
}