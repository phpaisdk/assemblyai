# AssemblyAI provider for PHP AI SDK

```bash
composer require aisdk/assemblyai
```

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

The provider uploads non-URL audio, submits it to AssemblyAI’s `/v2/transcript` endpoint, and polls until completion. Provider options map directly to AssemblyAI’s native transcription request fields. See [AssemblyAI’s API reference](https://www.assemblyai.com/docs/api-reference/transcripts/submit).
