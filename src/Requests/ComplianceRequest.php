<?php

namespace Yeashy\Compliance\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request as FacadeRequest;
use Illuminate\Validation\ValidationException;
use Yeashy\Compliance\Objects\ComplianceObject;
use Yeashy\Compliance\Pipelines\Processors\ComplianceValidator;

abstract class ComplianceRequest extends FormRequest
{
    /**
     * @var array<string>
     */
    protected array $compliances = [];

    /**
     * @var array<string>
     */
    protected array $postCompliances = [];

    /**
     * @var array<string>
     */
    protected array $getCompliances = [];

    /**
     * @var array<string>
     */
    protected array $putCompliances = [];

    /**
     * @var array<string>
     */
    protected array $patchCompliances = [];

    /**
     * @var array<string>
     */
    protected array $deleteCompliances = [];

    /**
     * @var array<string>
     */
    protected array $headCompliances = [];

    /**
     * @var array<string>
     */
    protected array $optionsCompliances = [];

    /**
     * @throws ValidationException
     */
    public function validateResolved(): void
    {
        parent::validateResolved();

        $validated = $this->processCompliances();

        if (! $validated->isValid()) {
            throw ValidationException::withMessages($validated->getErrors())
                ->status($validated->getCode());
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        $complianceValidated = $this->processCompliances();

        $allMessages = $complianceValidated->getErrors();

        /**
         * @var string $attribute
         * @var array<string> $messages
         */
        foreach ($validator->getMessageBag()->toArray() as $attribute => $messages) {
            if (empty($allMessages[$attribute])) {
                $allMessages[$attribute] = [];
            }

            $allMessages[$attribute] = array_merge($messages, $allMessages[$attribute]);
        }

        $exception = ValidationException::withMessages($allMessages);

        if ($complianceValidated->getCode()) {
            $exception->status($complianceValidated->getCode());
        }

        throw $exception;
    }

    private function processCompliances(): ComplianceObject
    {
        $pipeline = match (FacadeRequest::getMethod()) {
            'GET' => $this->getCompliances,
            'POST' => $this->postCompliances,
            'PUT' => $this->putCompliances,
            'PATCH' => $this->patchCompliances,
            'DELETE' => $this->deleteCompliances,
            'HEAD' => $this->headCompliances,
            'OPTIONS' => $this->optionsCompliances,
            default => [],
        };

        $validator = new ComplianceValidator;

        $pipeline = array_merge($pipeline, $this->compliances);

        return $validator->validate(
            (object) array_merge(
                FacadeRequest::all(),
                FacadeRequest::route()->parameters(),
            ), $pipeline);
    }
}
