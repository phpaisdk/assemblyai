# PHP AI SDK — AssemblyAI

Audio transcription with AssemblyAI for the framework-agnostic [PHP AI SDK](https://phpaisdk.com).

## Installation

```bash
composer require aisdk/assemblyai
```

Set `ASSEMBLYAI_API_KEY` in your environment.

## Usage

```php
use AiSdk\AssemblyAI;
use AiSdk\Content;
use AiSdk\Generate;

$result = Generate::transcription(Content::audio(__DIR__.'/meeting.mp3'))
    ->model(AssemblyAI::model('universal-3-5-pro'))
    ->run();

echo $result->output->text;
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/assemblyai) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
