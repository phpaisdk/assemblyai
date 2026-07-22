<?php

declare(strict_types=1);

namespace AiSdk\AssemblyAI;

use AiSdk\Support\Sdk;
use AiSdk\Utils\Support\Env;
use AiSdk\Utils\Support\Url;

final class AssemblyAIOptions
{
    public const string DEFAULT_BASE_URL = 'https://api.assemblyai.com';
    public const string PROVIDER_NAME = 'assemblyai';
    /** @param array<string,string> $headers */ public function __construct(public readonly string $apiKey, public readonly string $baseUrl = self::DEFAULT_BASE_URL, public readonly int $pollingIntervalMs = 1000, public readonly array $headers = [], public readonly ?Sdk $sdk = null) {} /** @param array<string,mixed> $config */ public static function fromArray(array $config = []): self
    {
        $key = Env::loadApiKey(isset($config['apiKey']) ? (string) $config['apiKey'] : null, 'ASSEMBLYAI_API_KEY', self::PROVIDER_NAME);
        $base = Url::withoutTrailingSlash(Env::loadOptionalSetting(isset($config['baseUrl']) ? (string) $config['baseUrl'] : null, 'ASSEMBLYAI_BASE_URL') ?? self::DEFAULT_BASE_URL);
        /** @var array<string,string> $headers */ $headers = isset($config['headers']) && is_array($config['headers']) ? $config['headers'] : [];
        $sdk = $config['sdk'] ?? null;
        return new self($key, $base, max(0, (int) ($config['pollingIntervalMs'] ?? 1000)), $headers, $sdk instanceof Sdk ? $sdk : null);
    } /** @return array<string,string> */ public function authHeaders(): array
    {
        return array_merge(['Authorization' => $this->apiKey], $this->headers);
    }
}
