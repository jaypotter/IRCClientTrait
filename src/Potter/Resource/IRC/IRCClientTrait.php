<?php

declare(strict_types=1);

namespace Potter\Resource\IRC;

use \Potter\Event\Event;
use \Psr\{Container\ContainerInterface, EventDispatcher\EventDispatcherInterface, Link\LinkInterface};

trait IRCClientTrait 
{
    private string $lastIRCMessage;
    private string $lastPrivateMessageSender;
    private int $lastServerMessageNumber;
    private string $messageOfTheDay = '';
    private string $pingToken;
    
    final public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getContainer()->get('event_dispatcher');
    }
    
    final public function getLink(): LinkInterface
    {
        return $this->getContainer()->get('link');
    }
    
    private function getLinkAttributes(): array
    {
        return $this->getLink()->getAttributes();
    }
    
    final public function getPassword(): string
    {
        return $this->getLinkAttributes()['password'];
    }
    
    final public function sendPassword(): void
    {
        $this->writeResource('PASS ' . $this->getPassword());
    }
    
    final public function getNickname(): string
    {
        return $this->getLinkAttributes()['nickname'];
    }
    
    final public function sendNickname(): void
    {
        $this->writeResource('NICK ' . $this->getNickname());
    }
    
    final public function getUsername(): string
    {
        return $this->getLinkAttributes()['username'];
    }
    
    final public function getRealName(): string
    {
        return $this->getLinkAttributes()['realname'];
    }
    
    final public function getHostname(): string
    {
        return $this->getLinkAttributes()['hostname'];
    }
    
    final public function getServerName(): string
    {
        return $this->getLinkAttributes()['servername'];
    }
    
    final public function sendUsername(): void
    {
        $this->writeResource('USER ' . $this->getUsername() . ' ' . 
                $this->getHostname() . ' ' . 
                $this->getServerName() . ' '. 
                $this->getRealName());
    }
    
    final public function handleConnection(): void
    {
        $this->sendPassword();
        $this->sendNickname();
        $this->sendUsername();
    }
    
    final public function getLastIRCMessage(): string
    {
        return $this->lastIRCMessage;
    }
    
    final public function handleMessage(): void
    {
        if (!str_contains($message = $this->getLastMessage(), ' :')) {
            return;
        }
        $this->lastIRCMessage = ($split = explode(' :', $message, 2))[1];
        $eventDispatcher = $this->getEventDispatcher();
        if ($split[0] === "PING") {
            $this->pingToken = $this->lastIRCMessage;
            $eventDispatcher->dispatch(new Event('onPing', $this));
            return;
        }
        $left = explode(' ', $split[0]);
        if (ctype_digit($left[1])) {
            $this->lastServerMessageNumber = intval($left[1]);
            $this->handleServerMessage();
            return;
        }
        if ($left[1] === "PRIVMSG") {
            $this->lastPrivateMessageSender = substr($left[0], 1, strpos($leftSide[0], '!') - 2);
            $eventDispatcher->dispatch(new Event('onPrivateMessage', $this));
            return;
        }
    }
    
    final public function getLastServerMessageNumber(): int
    {
        return $this->lastServerMessageNumber;
    }
    
    final public function handleServerMessage(): void
    {
        $eventDispatcher = $this->getEventDispatcher();
        switch ($this->getLastServerMessageNumber()) {
            case 372:
                $eventDispatcher->dispatch(new Event('onMessageOfTheDay', $this));
                return;
            case 376:
                // $eventDispatcher->dispatch(new Event('onCompleteMessageOfTheDay', $this));
                return;
        }
    }
    
    final public function getMessageOfTheDay(): string
    {
        return $this->messageOfTheDay;
    }
    
    final public function handleMessageOfTheDay(): void
    {
        $this->messageOfTheDay .= $this->lastIRCMessage . PHP_EOL;
    }
    
    final public function pong(string $token): void
    {
        $this->writeResource('PONG :' . $token);
    }
    
    final public function handlePing(): void
    {
        $this->pong($this->pingToken);
    }
    
    final public function getLastPrivateMessageSender(): string
    {
        return $this->lastPrivateMessageSender;
    }
    
    final public function handlePrivateMessage(): void
    {
        echo 'Private Message From ' . $this->getLastPrivateMessageSender() . ': ' . $this->getLastIRCMessage() . PHP_EOL;
    }
    
    abstract public function getContainer(): ContainerInterface;
    abstract public function getLastMessage(): string;
    abstract public function writeResource(string $data): void;
}
