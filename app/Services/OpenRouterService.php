<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class OpenRouterService {
    private $client;
    private $logger;

    public function __construct() {
        // Initialize Guzzle HTTP Client
        $this->client = new Client([
            'base_uri' => 'https://openrouter.ai/api/v1/',
            'timeout'  => 30.0,
        ]);

        // Set up logging
        $this->logger = new Logger('openrouter');
        $this->logger->pushHandler(
            new StreamHandler(storage_path('logs/openrouter.log'), Logger::DEBUG)
        );
    }

    /**
     * Generate AI response for incoming message
     * 
     * @param string $userMessage
     * @param array $conversationHistory
     * @return string
     */
    public function generateResponse(string $userMessage, array $conversationHistory = []): string {
        try {
            // Log incoming message for debugging
            $this->logger->debug('Generating response for message:', [
                'user_message' => $userMessage,
                'history_count' => count($conversationHistory)
            ]);

            // Clean and validate the user message
            $userMessage = trim($userMessage);
            if (empty($userMessage)) {
                return "I'm not sure what you're asking. Could you please provide more details?";
            }

            // Prepare conversation context
            $messages = $this->prepareMessages($userMessage, $conversationHistory);

            // Generate response
            $response = $this->callOpenRouterApi($messages);

            // Log the generated response
            $this->logger->debug('Generated response:', [
                'response' => $response
            ]);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Error generating response:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getErrorMessage();
        }
    }

    /**
     * Call OpenRouter API to generate response
     * 
     * @param array $messages Conversation messages
     * @return string Generated response or error message
     */
    private function callOpenRouterApi(array $messages): string {
        try {
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name'),
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'model' => config('services.openrouter.model', 'openai/gpt-4'),
                    'messages' => $messages
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            $this->logger->debug('OpenRouter API Response:', $responseBody);

            if (isset($responseBody['choices']) && !empty($responseBody['choices'])) {
                return $responseBody['choices'][0]['message']['content'];
            }

            $this->logger->error('Unexpected OpenRouter response format');
            return $this->getErrorMessage();

        } catch (RequestException $e) {
            $this->logger->error('OpenRouter HTTP Error: ' . $e->getMessage());
            
            if ($e->hasResponse()) {
                $errorStatusCode = $e->getResponse()->getStatusCode();
                $this->logger->error("HTTP Status Code: $errorStatusCode");
            }

            return $this->getConnectionErrorMessage();

        } catch (\Exception $e) {
            $this->logger->error('Unexpected OpenRouter Error: ' . $e->getMessage());
            return $this->getErrorMessage();
        }
    }

    /**
     * Prepare messages with system prompt and conversation history
     * 
     * @param string $userMessage
     * @param array $conversationHistory
     * @return array
     */
    private function prepareMessages(string $userMessage, array $conversationHistory): array {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ]
        ];

        // Add conversation history (limit to last 5 messages)
        $recentHistory = array_slice($conversationHistory, -5);
        $messages = array_merge($messages, $recentHistory);

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        return $messages;
    }

    /**
     * Get system prompt for AI context
     * 
     * @return string
     */
    private function getSystemPrompt(): string {
        return <<<PROMPT
You are Sarah, a knowledgeable and empathetic human-like loan advisor. Your role is to assist website visitors with their loan inquiries and guide them towards scheduling a meeting with our expert loan advisors.

Key Responsibilities:
1. Initial Contact
   - Greet users warmly and professionally (only once).
   - Verify their interest in loan services and dig deeper into their needs
   - Maintain a friendly, approachable and a conversational tone.

2. Loan Assessment
   - Inquire about loan purpose (personal/business)
   - Ask about desired loan amount
   - Gather basic information about their financial situation
   - Understand their timeline and urgency

3. Lead Qualification
   - Ask relevant questions to qualify leads
   - Keep conversation focused on loan services
   - Handle objections professionally
   - Guide interested clients to scheduling a meeting either online or in person.

Communication Guidelines:
- Use warm, professional language
- Include appropriate emojis for friendliness (😊, 📱, 💼, etc.)
- Ask one question at a time
- Keep responses concise and clear with follow up questions in a natural manner
- Maintain professionalism in all situations

Standard Responses:
1. Initial Message:
"Are you looking for a Personal Loan or Business Loan?"
(need to be free flow here with answers and questions led by the visitor)

2. Scheduling Request:
"I'd be happy to have one of our loan experts call you to discuss this further. Would you like to schedule a quick call? 📱"

3. Scheduling Link:
"Perfect! Please use this link to schedule a time that works best for you: https://cal.com/webdaddy/30min 📅"

4. Contact Source Query Response:
"You previously inquired through our website. If you'd prefer not to receive messages from us, simply reply 'STOP'."

Important Rules:
1. Never share specific loan terms or rates
2. Always direct detailed questions to the consultation call
3. Maintain GDPR and privacy compliance in Singapore context
4. Keep focus on scheduling consultation
5. Handle objections professionally
6. Never use aggressive sales tactics
7. Message size should not exceed more than 25 words

Response Structure:
1. Acknowledge their message
2. Provide relevant information
3. Ask ONE specific question
4. Keep responses under 160 characters when possible

Example Conversation Flow:
User: "Hi"
Assistant: "Hello! 😊I am Sarah, your personal AI loan assistant. Can interest you with some amazing ways to get your loan approved?"
User: "Yes"
Assistant: "Excellent! 👋 Are you looking for a Personal Loan or Business Loan?"
User: "Personal"
Assistant: "Thanks! To assist you better, what would the personal loan for? 💭"

Schedule Focus:
- After identifying basic needs, guide toward scheduling
- Use calendar link for scheduling
- Follow up if no schedule action taken

Remember: Your goal is to qualify leads and schedule consultations while maintaining a helpful, professional manner.
PROMPT;
    }

    /**
     * Get a generic error message for service issues
     * 
     * @return string
     */
    private function getErrorMessage(): string {
        return "I'm sorry, I'm having trouble processing your request right now. Please try again later.";
    }

    /**
     * Get a specific error message for connection issues
     * 
     * @return string
     */
    private function getConnectionErrorMessage(): string {
        return "I'm sorry, I'm having trouble connecting to our service. Please try again later.";
    }
}
