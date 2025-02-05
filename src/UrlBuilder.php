<?php

namespace Enj0yer\Zub;

class UrlBuilder 
{
    private $url = '';

    private function __construct($url) 
    {
        $this->url = $url;
    }

    public static function new(string ...$url): static
    {
        $url = static::normalizeUrl(...$url);
        return new static($url);
    }

    private static function normalizeUrl(string ...$url): string
    {
        $joinedUrl = implode('/', $url);
        if (str_contains($joinedUrl, 'https')) {
            return preg_replace('#(?<!https:)/{2,}#', '/', $joinedUrl);
        } else if (str_contains($joinedUrl, 'http')) {
            return preg_replace('#(?<!http:)/{2,}#', '/', $joinedUrl);
        } else {
            return "";
        }
    }

    private static function buildQueryParameters(array $parameters) {
        $result = [];
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $result[] = implode('&', array_map(fn ($val) => (string) $key .'='. (string) $val, $value));
            } else {
                $result[] = (string) $key .'='. (string) $value;
            }
        }
        return implode('&', $result);
    }

    private static function addQueryParameters(string $url, array $parameters): string
    {
        $buildedParameters = static::buildQueryParameters($parameters);
        return static::removeSingleTrailingSlash($url) . (!empty($buildedParameters) ? ('?'.$buildedParameters) : $buildedParameters);
    }

    private static function addUrlParameters(string $url, array $parameters): string
    {
        [$replaceable, $other] = explode('?', $url, 2);
        foreach ($parameters as $key => $value) {
            if (str_contains($replaceable, '{'.(string) $key.'}') && !empty($key)) {
                $replaceable = str_replace('{'.(string) $key.'}', $value, $replaceable);
            }
        }
        if (empty($other)) {
            return $replaceable;
        } else {
            return implode('?', [$replaceable, $other]);
        }
    }

    private static function removeSingleTrailingSlash(string $url): string
    {
        return str_ends_with($url, '/') ? substr($url, 0, strlen($url) - 1) : $url;
    }

    public function withUrlParameters(array $parameters): self
    {
        if (!empty($this->url)) {
            $this->url = static::addUrlParameters($this->url, $parameters);
        }
        return $this;
    }

    public function withQueryParameters(array $parameters): self
    {
        if (!empty($this->url)) {
            $this->url = static::addQueryParameters($this->url, $parameters);
        }
        return $this;
    }

    public function get(): string
    {
        return $this->url;
    }

    public function __tostring()
    {
        return $this->url;
    }
}