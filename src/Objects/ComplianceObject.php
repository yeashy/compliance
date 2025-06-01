<?php

namespace Yeashy\Compliance\Objects;

final class ComplianceObject
{
    public bool $isValid = true;

    public string $message = '';

    public int $code = 0;

    /**
     * @var array<string>
     */
    public array $errors = [];

    public function valid(): self
    {
        $this->isValid = true;

        return $this;
    }

    public function invalid(): self
    {
        $this->isValid = false;

        return $this;
    }

    /**
     * @param  array<string>|string  $message
     * @return $this
     */
    public function message(array|string $message): self
    {
        is_array($message) ?: $this->message = $message[0];

        return $this;
    }

    public function code(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param  array<string>  $errors
     * @return $this
     */
    public function errors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
