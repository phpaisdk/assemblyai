<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\AssemblyAI\AssemblyAIOptions;
use AiSdk\AssemblyAI\AssemblyAIProvider;
use AiSdk\Contracts\Model;

final class AssemblyAI
{
    private static ?AssemblyAIProvider $default = null;
    /** @param array<string,mixed> $config */ public static function create(array $config = []): AssemblyAIProvider
    {
        return self::$default = new AssemblyAIProvider(AssemblyAIOptions::fromArray($config));
    } public static function default(): AssemblyAIProvider
    {
        return self::$default ??= self::create();
    } public static function reset(): void
    {
        self::$default = null;
    } public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
