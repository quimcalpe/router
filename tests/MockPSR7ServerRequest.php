<?php
namespace QuimCalpe\Router\Test;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class MockPSR7ServerRequest implements ServerRequestInterface
{
    private $queryParams;

    public function __construct(array $queryParams = [])
    {
        $this->queryParams = $queryParams;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getBody() {}

    public function getStatusCode() {}

    public function getReasonPhrase() {}

    public function getProtocolVersion() {}

    public function withStatus($code, $reasonPhrase = '') {}

    public function withProtocolVersion($version) {}

    public function getHeaders() {}

    public function hasHeader($name) {}

    public function getHeader($name) {}

    public function getHeaderLine($name) {}

    public function withHeader($name, $value) {}

    public function withAddedHeader($name, $value) {}

    public function withoutHeader($name) {}

    public function withBody(StreamInterface $body) {}

    public function getServerParams() {}

    public function getCookieParams() {}

    public function withCookieParams(array $cookies) {}

    public function withQueryParams(array $query) {}

    public function getUploadedFiles() {}

    public function withUploadedFiles(array $uploadedFiles) {}

    public function getParsedBody() {}

    public function withParsedBody($data) {}

    public function getAttributes() {}

    public function getAttribute($name, $default = null) {}

    public function withAttribute($name, $value) {}

    public function withoutAttribute($name) {}

    public function getRequestTarget() {}

    public function withRequestTarget($requestTarget) {}

    public function getMethod() {}

    public function withMethod($method) {}

    public function getUri() {}

    public function withUri(UriInterface $uri, $preserveHost = false) {}
}
