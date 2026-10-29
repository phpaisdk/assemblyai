<?php

declare(strict_types=1);

namespace AiSdk\AssemblyAI\Models;

use AiSdk\AssemblyAI\AssemblyAIOptions;
use AiSdk\ContentSource;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\TranscriptionModelInterface;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Requests\TranscriptionRequest;
use AiSdk\Responses\TranscriptionResponse;
use AiSdk\Results\TranscriptData;
use AiSdk\Results\TranscriptSegment;
use AiSdk\Support\Usage;
use AiSdk\Utils\Support\Url;

final class AssemblyAITranscriptionModel extends BaseModel implements TranscriptionModelInterface
{
    public function __construct(private readonly string $modelId, private readonly AssemblyAIOptions $options) {} public function provider(): string
    {
        return AssemblyAIOptions::PROVIDER_NAME;
    } public function modelId(): string
    {
        return $this->modelId;
    } public function transcribe(TranscriptionRequest $request): TranscriptionResponse
    {
        $runner = $this->runner($this->options->sdk);
        $audioUrl = $request->audio->source() === ContentSource::Url ? $request->audio->url() : null;
        if ($audioUrl === null) {
            $sdk = $this->options->sdk ?? \AiSdk\Generate::sdk();
            $upload = $sdk->requestFactory->createRequest('POST', Url::joinPath($this->options->baseUrl, '/v2/upload'))->withBody($sdk->streamFactory->createStream((string) base64_decode((string) $request->audio->base64Data(), true)))->withHeader('Content-Type', 'application/octet-stream');
            foreach ($this->options->authHeaders() as $name => $value) {
                $upload = $upload->withHeader($name, $value);
            } $audioUrl = (string) ($runner->sendRequest($upload, $this->provider())->getBody());
            $payload = json_decode($audioUrl, true);
            $audioUrl = is_array($payload) ? (string) ($payload['upload_url'] ?? '') : '';
        } $submitted = $runner->postJson(Url::joinPath($this->options->baseUrl, '/v2/transcript'), array_replace(['audio_url' => $audioUrl, 'speech_models' => [$this->modelId]], $request->providerOptionsFor($this->provider())), $this->options->authHeaders(), $this->provider());
        $id = is_string($submitted['id'] ?? null) ? $submitted['id'] : '';
        if ($id === '') {
            throw new InvalidResponseException('AssemblyAI did not return a transcript id.');
        }
        $deadline = time() + 900;
        do {
            if ($this->options->pollingIntervalMs > 0) {
                usleep($this->options->pollingIntervalMs * 1000);
            } $payload = $runner->getJson(Url::joinPath($this->options->baseUrl, '/v2/transcript/' . rawurlencode($id)), $this->options->authHeaders(), $this->provider());
            if (time() > $deadline) {
                throw new \AiSdk\Exceptions\TimeoutException('AssemblyAI transcription did not finish within 15 minutes.', ['provider' => 'assemblyai', 'transcriptId' => $id]);
            }
        } while (($payload['status'] ?? '') === 'queued' || ($payload['status'] ?? '') === 'processing');
        if (($payload['status'] ?? '') === 'error') {
            throw new InvalidResponseException('AssemblyAI transcription failed: ' . (is_string($payload['error'] ?? null) ? $payload['error'] : 'unknown error'));
        }
        if (! is_string($payload['text'] ?? null)) {
            throw new InvalidResponseException('AssemblyAI returned a transcript without text.');
        } $segments = [];
        foreach ((array) ($payload['words'] ?? []) as $word) {
            if (is_array($word)) {
                $segments[] = new TranscriptSegment((string) ($word['text'] ?? ''), (float) ($word['start'] ?? 0) / 1000, (float) ($word['end'] ?? 0) / 1000, isset($word['speaker']) ? (string) $word['speaker'] : null);
            }
        } return new TranscriptionResponse(new TranscriptData((string) ($payload['text'] ?? ''), isset($payload['language_code']) ? (string) $payload['language_code'] : null, isset($payload['audio_duration']) ? (float) $payload['audio_duration'] : null, $segments), new Usage(), $payload, ['assemblyai' => ['id' => $id]]);
    }
}
