# aisdk/assemblyai

<a href="https://github.com/phpaisdk/assemblyai/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/assemblyai/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/assemblyai"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/assemblyai"></a>
<a href="https://packagist.org/packages/aisdk/assemblyai"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/assemblyai"></a>

------

Official AssemblyAI provider for the PHP AI SDK. Uses AssemblyAI's native pre-recorded transcription API.

## Installation

```bash
composer require aisdk/assemblyai
```

## Basic Usage

```php
use AiSdk\AssemblyAI;
use AiSdk\Content;
use AiSdk\Generate;

AssemblyAI::create(['apiKey' => $_ENV['ASSEMBLYAI_API_KEY']]);

$result = Generate::transcription()
    ->model(AssemblyAI::model('universal-3-pro'))
    ->audio(Content::audio('https://example.com/audio.mp3'))
    ->run();
```

## Configuration

| Variable | Description | Default |
| --- | --- | --- |
| `ASSEMBLYAI_API_KEY` | API key for authentication | Required |
| `ASSEMBLYAI_BASE_URL` | Base URL for API requests | `https://api.assemblyai.com` |

## Supported Capabilities

| Capability | Support |
| --- | --- |
| Pre-recorded transcription | Native |
| Remote audio URLs | Native |
| Local audio files | Uploaded before transcription |

The package submits `/v2/transcript` jobs and polls until they complete. Use `providerOptions('assemblyai', ['raw' => [...]])` for AssemblyAI transcription fields such as `language_detection`, `speaker_labels`, or `keyterms_prompt`.

## Documentation

- [AssemblyAI transcription API reference](https://www.assemblyai.com/docs/api-reference/transcripts/submit)
- [PHP AI SDK documentation](https://phpaisdk.com/docs/assemblyai)
