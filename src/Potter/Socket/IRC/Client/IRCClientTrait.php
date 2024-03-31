<?php

declare(strict_types=1);

namespace Potter\Socket\IRC\Client;

use \Psr\{Container\ContainerInterface, EventDispatcher\EventDispatcherInterface, Link\LinkInterface};

trait IRCClientTrait 
{
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
        $this->writeSocket('PASS ' . $this->getPassword());
    }
    
    final public function getNickname(): string
    {
        return $this->getLinkAttributes()['nickname'];
    }
    
    final public function sendNickname(): void
    {
        $this->writeSocket('NICK ' . $this->getNickname());
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
        return $this->getLinkAttributes()['server'];
    }
    
    final public function sendUsername(): void
    {
        $this->writeSocket('USER ' . $this->getUsername() . ' ' . 
                $this->getHostname() . ' ' . 
                $this->getServerName() . ' '. 
                $this->getRealName());
    }
    
    abstract public function getContainer(): ContainerInterface;
    abstract public function writeSocket(string $data): void;
}
