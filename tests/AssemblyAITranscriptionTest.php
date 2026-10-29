<?php

declare(strict_types=1);
use AiSdk\AssemblyAI;
use AiSdk\Content;
use AiSdk\Generate;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

afterEach(function (): void {
    Generate::reset();
    AssemblyAI::reset();
});
it('submits and polls an AssemblyAI transcription', function (): void {
    $client = new class implements ClientInterface {
        public array $requests = [];
        private array $responses;
        public function __construct()
        {
            $this->responses = [['id' => 'tr_1', 'status' => 'queued'], ['id' => 'tr_1', 'status' => 'completed', 'text' => 'Hello world', 'language_code' => 'en_us', 'audio_duration' => 1.2, 'words' => [['text' => 'Hello', 'start' => 0, 'end' => 500], ['text' => 'world', 'start' => 600, 'end' => 1200]]]];
        } public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->requests[] = $request;
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(array_shift($this->responses)));
        }
    };
    $factory = new Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
    AssemblyAI::create(['apiKey' => 'assembly-test', 'pollingIntervalMs' => 0]);
    $result = Generate::transcription()->model(AssemblyAI::model('universal-3-pro'))->audio(Content::audio('https://example.com/audio.mp3'))->run();
    expect($result->output->text)->toBe('Hello world')->and($result->output->segments[1]->end)->toBe(1.2)->and((string) $client->requests[0]->getUri())->toBe('https://api.assemblyai.com/v2/transcript')->and((string) $client->requests[1]->getUri())->toBe('https://api.assemblyai.com/v2/transcript/tr_1');
});

it('fails an AssemblyAI transcript that has no id, no text or an unreadable error', function (array $responses): void {
    $client = new class ($responses) implements ClientInterface {
        public function __construct(private array $responses) {}

        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(array_shift($this->responses)));
        }
    };
    $factory = new Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
    AssemblyAI::create(['apiKey' => 'aai-test', 'pollingIntervalMs' => 0]);
    set_error_handler(static fn(int $no, string $str) => throw new ErrorException($str));

    try {
        expect(fn() => Generate::transcription()->model(AssemblyAI::model('universal'))->audio(Content::audio('https://example.com/a.mp3'))->run())
            ->toThrow(AiSdk\Exceptions\InvalidResponseException::class);
    } finally {
        restore_error_handler();
    }
})->with([
    'no id' => [[['status' => 'queued']]],
    'completed without text' => [[['id' => 't1'], ['id' => 't1', 'status' => 'completed']]],
    'error as array' => [[['id' => 't1'], ['id' => 't1', 'status' => 'error', 'error' => ['x']]]],
]);
