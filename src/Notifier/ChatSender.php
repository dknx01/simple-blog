<?php

namespace App\Notifier;

use App\Entity\Suggestion;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Twig\Environment;

class ChatSender
{
    private ChatterInterface $chatter;
    private Environment $environment;

    /**
     * @param ChatterInterface $chatter
     * @param Environment $environment
     */
    public function __construct(ChatterInterface $chatter, Environment $environment)
    {
        $this->chatter = $chatter;
        $this->environment = $environment;
    }

    /**
     * @param Suggestion $suggestion
     * @throws TransportExceptionInterface
     */
    public function sendChatterMessageForSuggestion(Suggestion $suggestion): void
    {
        $message = (new ChatMessage('Neuer Vorschlag/Idee'))
            ->subject($this->environment
                ->render('suggestion/telegramm_message.html.twig', ['suggestion' => $suggestion])
            )
            ->transport('telegram');
        $this->chatter->send($message);
    }
}