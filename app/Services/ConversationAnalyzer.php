<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ConversationAnalyzer
{
    private $groqClient;

    public function __construct()
    {
        // Initialize Guzzle HTTP Client for Groq API
        $this->groqClient = new Client([
            'base_uri' => 'https://api.groq.com/openai/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Analyze conversation and generate scoring
     * 
     * @param string $conversation Conversation transcript
     * @return array Analysis result
     */
    public function analyzeConversation(string $conversation): array
    {
        try {
            // Log the start of analysis
            Log::debug('Starting conversation analysis', [
                'conversation_length' => strlen($conversation)
            ]);

            // Generate scoring prompt
            $prompt = $this->getScoringPrompt($conversation);
            
            // Log the prompt
            Log::debug('Generated analysis prompt', [
                'prompt' => $prompt
            ]);

            // Call Groq API for conversation analysis
            $response = $this->groqClient->post('chat/completions', [
                'json' => [
                    'messages' => [
                        [
                            'role' => 'user', 
                            'content' => $prompt
                        ]
                    ],
                    'model' => 'mixtral-8x7b-32768',
                    'temperature' => 0.3,
                    'max_tokens' => 500
                ]
            ]);

            // Parse the response
            $responseBody = json_decode($response->getBody(), true);
            
            // Log raw API response
            Log::debug('Groq API Response', [
                'raw_response' => $responseBody
            ]);
            
            // Extract and parse the AI-generated content
            $analysisContent = $responseBody['choices'][0]['message']['content'];
            
            // Attempt to parse the JSON response
            $result = $this->parseAnalysisResult($analysisContent);

            // Log the final result
            Log::info('Analysis completed successfully', [
                'score' => $result['score'],
                'summary' => $result['summary'],
                'reasoning' => $result['reasoning']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'conversation_length' => strlen($conversation)
            ]);

            return $this->getDefaultErrorResponse($e);
        }
    }

    /**
     * Generate scoring prompt for conversation analysis
     */
    private function getScoringPrompt(string $conversation): string
    {
        return <<<PROMPT
You are an expert conversation analyst for a loan advisory service. 
Analyze the following conversation transcript and provide a JSON-formatted 
assessment focusing on lead quality and potential:

Conversation:
{$conversation}

Please provide a JSON response with the following structure:
{
    "score": number (1-10),
    "summary": "Brief conversation summary",
    "reasoning": "Detailed analysis explaining the score"
}

Scoring Criteria:
- Lead Interest (0-4 points)
- Conversation Depth (0-3 points)
- Potential for Conversion (0-3 points)

Focus on:
- Clarity of loan requirements
- User's engagement level
- Potential for scheduling a consultation
PROMPT;
    }

    /**
     * Parse the AI-generated analysis result
     */
    private function parseAnalysisResult($content)
    {
        try {
            // Decode the JSON response
            $analysis = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format');
            }

            // Extract the main fields
            $score = $analysis['score'] ?? 1;
            $summary = $analysis['summary'] ?? 'No summary available';
            
            // Handle nested reasoning structure
            $reasoning = '';
            if (isset($analysis['reasoning'])) {
                if (is_array($analysis['reasoning'])) {
                    // If reasoning is an array/object, format it nicely
                    foreach ($analysis['reasoning'] as $key => $value) {
                        if (is_string($value)) {
                            $reasoning .= "$key: $value\n";
                        }
                    }
                } else {
                    // If reasoning is a string, use it directly
                    $reasoning = $analysis['reasoning'];
                }
            }

            return [
                'score' => $score,
                'summary' => $summary,
                'reasoning' => trim($reasoning) ?: 'No reasoning provided'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to parse analysis result', [
                'error' => $e->getMessage(),
                'content' => $content
            ]);
            
            return [
                'score' => 1,
                'summary' => 'Error analyzing conversation',
                'reasoning' => 'Analysis failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate default error response
     */
    private function getDefaultErrorResponse(\Exception $e): array
    {
        return [
            'score' => 1,
            'summary' => 'Error analyzing conversation',
            'reasoning' => "Analysis failed: {$e->getMessage()}"
        ];
    }
}
