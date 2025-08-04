<?php

namespace Yeashy\Compliance\Pipelines\Processors;

use Illuminate\Support\Arr;
use Yeashy\Compliance\Objects\ComplianceObject;

final class ComplianceValidator extends PipelineProcessor
{
    private const DEFAULT_STATUS_CODE = 422;

    /**
     * @param  array<string>  $pipes
     */
    public function validate(object $data, array $pipes): ComplianceObject
    {
        $result = $this->processPipeline($data, $pipes);

        if (! empty($result->__errorMessages)) {
            return $this->validatedData()
                ->invalid()
                ->message(Arr::first($result->__errorMessages))
                ->errors($result->__errorMessages)
                ->code($result->__statusCode ?? self::DEFAULT_STATUS_CODE);
        }

        return $this->validatedData();
    }

    private function validatedData(): ComplianceObject
    {
        return new ComplianceObject;
    }
}
