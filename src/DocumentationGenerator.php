<?php

namespace Molbal\AiPhpdoc;

class DocumentationGenerator
{
    /**
     * createDocBlock
     *
     * @param string $function The function to generate the doc block for
     *
     * @return string The generated doc block
     */
    public static function createDocBlock(string $function): string
    {
        $key = getenv('OPENAI_KEY');
        $baseUri = getenv('BASE_URI');
        $model = getenv('MODEL');

        if (!$model) {
            $model = 'gpt-3.5-turbo-instruct';
        }

        $openai = \OpenAI::client($key);
        if ($baseUri) {
            $openai = \OpenAI::factory()
                ->withApiKey($key)
                ->withBaseUri($baseUri)
                ->make();
        }
        $prompt = "Read the following PHP function: " . $function . ". Write the PHPDoc block in English for the method named " . $function . ", remove any unnecessary code and comments, including if it's an empty constructor. Do not add any additional comments except for the required PHPDocs, unless you detect obvious errors.";

        $completion = $openai->completions()->create([
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => 1024,
            'stop' => ['"""'],
            'temperature' => 0.3
        ]);

        try {
            // Check if the OpenAI API returned an error response
            if (isset($completion['error'])) {
                throw new \RuntimeException($completion['error']);
            }

            return $completion['choices'][0]['text'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('An error occurred while trying to get the doc block: ' . $e->getMessage());
        }
    }
}
