<?php
namespace QuimCalpe\Router\Test;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class MockPSR7ServerRequest implements ServerRequestInterface
{
    public function __construct(private array $queryParams = []) {}

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getBody(): StreamInterface
    {
        return new MockPSR7Stream('');
    }

    public function getProtocolVersion(): string { return '1.1'; }
    public function withProtocolVersion(string $version): MessageInterface { return $this; }
    public function getHeaders(): array { return []; }
    public function hasHeader(string $name): bool { return false; }
    public function getHeader(string $name): array { return []; }
    public function getHeaderLine(string $name): string { return ''; }
    public function withHeader(string $name, $value): MessageInterface { return $this; }
    public function withAddedHeader(string $name, $value): MessageInterface { return $this; }
    public function withoutHeader(string $name): MessageInterface { return $this; }
    public function withBody(StreamInterface $body): MessageInterface { return $this; }

    public function getServerParams(): array { return []; }
    public function getCookieParams(): array { return []; }
    public function withCookieParams(array $cookies): ServerRequestInterface { return $this; }
    public function withQueryParams(array $query): ServerRequestInterface { return $this; }
    public function getUploadedFiles(): array { return []; }
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface { return $this; }
    public function getParsedBody() { return null; }
    public function withParsedBody($data): ServerRequestInterface { return $this; }
    public function getAttributes(): array { return []; }
    public function getAttribute(string $name, $default = null) { return $default; }
    public function withAttribute(string $name, $value): ServerRequestInterface { return $this; }
    public function withoutAttribute(string $name): ServerRequestInterface { return $this; }

    public function getRequestTarget(): string { return '/'; }
    public function withRequestTarget(string $requestTarget): RequestInterface { return $this; }
    public function getMethod(): string { return 'GET'; }
    public function withMethod(string $method): RequestInterface { return $this; }
    public function getUri(): UriInterface { throw new \RuntimeException('not implemented'); }
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface { return $this; }
}
