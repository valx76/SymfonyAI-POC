<?php

namespace App\AI;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class Chat
{
    private const string SESSION_KEY = 'session-chat';

    public function __construct(
        private RequestStack $requestStack,
        #[Autowire(service: 'ai.agent.app')]
        private AgentInterface $agent,
    ) {
    }

    public function loadMessages(): MessageBag
    {
        $systemMessage = new MessageBag(
            Message::forSystem(
                <<<PROMPT
                    You are an helpful assistant that knows about the orders in the system.
                    To search for content you use the tool 'similarity_search' for generating the answer.
                    Only use content that you get from searching with that tool or your previous answers.
                    Don't make up information and if you can't find something, just say so.
                    If the user asks for his orders, don't ask to identify him, just return all the orders following the information he gives you.
                    Return the response as a markdown text.
                    If the users asks for a list, return it as a markdown list.
                PROMPT
            )
        );

        /** @var MessageBag $messages */
        $messages = $this->requestStack->getSession()->get(self::SESSION_KEY, $systemMessage);

        return $messages;
    }

    public function submitMessage(string $message): void
    {
        $messages = $this->loadMessages();

        $messages->add(Message::ofUser($message));
        $result = $this->agent->call($messages);

        \assert($result instanceof TextResult);

        $messages->add(Message::ofAssistant($result->getContent()));

        $this->saveMessages($messages);
    }

    public function reset(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    private function saveMessages(MessageBag $messages): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $messages);
    }
}
