<?php

namespace Yeashy\Compliance\Pipelines\Processors;

use Illuminate\Pipeline\Pipeline;

abstract class PipelineProcessor
{
    protected string $pipelineClass = Pipeline::class;

    /**
     * @param  array<string>  $pipes
     */
    protected function processPipeline(object $data, array $pipes): object
    {
        return app($this->pipelineClass)
            ->send($data)
            ->through($pipes)
            ->then(fn ($result) => $result);
    }
}
