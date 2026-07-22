<?php

declare(strict_types=1);

namespace AiSdk\AssemblyAI;

use AiSdk\AssemblyAI\Models\AssemblyAITranscriptionModel;
use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\TranscriptionModelInterface;
use AiSdk\Contracts\TranscriptionProviderInterface;

final class AssemblyAIProvider extends BaseProvider implements TranscriptionProviderInterface
{
    public function __construct(public readonly AssemblyAIOptions $options) {} public function name(): string
    {
        return AssemblyAIOptions::PROVIDER_NAME;
    } protected function transcriptionModel(string $modelId): TranscriptionModelInterface
    {
        return new AssemblyAITranscriptionModel($modelId, $this->options);
    }
}
