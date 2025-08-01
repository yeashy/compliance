<?php

namespace Yeashy\Compliance\Rules;

use Closure;
use Throwable;

abstract class ComplianceRule
{
    protected string $key = 'Some attribute';

    protected string $message = 'Some data is invalid';

    protected object $data;

    protected function key(): string
    {
        return $this->key;
    }

    protected function message(): string
    {
        return $this->message;
    }

    public function handle(object $data, Closure $next): object
    {
        $this->data = $data;

        try {
            if (! empty($this->data->__mustExit)) {
                return $next($this->data);
            }

            $this->validate();
        } catch (Throwable $exception) {
            $this->invalidateException($exception);
        }

        return $next($this->data);
    }

    abstract protected function validate(): void;

    protected function invalidate(?string $key = null, ?string $message = null): void
    {
        if (empty($this->data->__errorMessages)) {
            $this->data->__errorMessages = [];
        }

        $key = $key ?? $this->key ?? $this->key();
        $message = $message ?? $this->message ?? $this->message();

        if (empty($this->data->__errorMessages[$key])) {
            $this->data->__errorMessages[$key] = [];
        }

        $this->data->__errorMessages[$key][] = $message;
    }

    private function invalidateException(Throwable $exception): void
    {
        if (config('app.debug')) {
            $this->invalidate($this->key, 'Exception: ' . $exception->getMessage());
        } else {
            $this->invalidate($this->key, 'Unprocessable Content');
        }
    }

    protected function invalidateAndExit(?string $key = null, ?string $message = null): void
    {
        $this->invalidate($key, $message);

        $this->skipNext();
    }

    protected function skipNext(): void
    {
        $this->data->__mustExit = true;
    }
}
