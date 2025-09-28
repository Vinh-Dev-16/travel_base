<?php

namespace Vinhdev\Travel\Contracts\DataMappers;

class NotificationData
{
    private string $title;
    private string $body;
    private ?array $data = null;

    public function __construct(string $title, string $body, ?array $data = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;
        return $this;
    }
}