<?php
declare(strict_types=1);
namespace Clockwork\Core\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Docs:
 * https://symfony.com/doc/current/components/http_foundation.html
 */
class Request extends SymfonyRequest
{
    public function __construct()
    {
        parent::__construct($_GET, $_POST, [], [], [], [], []);
    }

    public static function query()
    {
//        return (new self())->query;

        return request();
    }

    public function parseQuery(string $key): array
    {
        return explode(',', $this->query->get($key));
    }
}