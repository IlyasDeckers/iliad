<?php
namespace Iliad\Concerns;

trait Editable {
    /**
     * @var array<int, string> $variables
     */
    protected array $variables = [];

    public function formatMessage(): void
    {
        $fields = collect([
            'message', 'subject'
        ]);

        foreach ($this->variables as $variable) {
            $this->replace($fields, $variable);
        }
    }

    private function replace(\Illuminate\Support\Collection $fields, string $variable): void
    {
        $fields->each(function (string $field) use ($variable) {
            $toReplace = '{{ ' . $variable . ' }}';
            $fieldName = str_replace('-', '_', $variable);
            $hasVariable = str_contains($this->query[$field], $toReplace);

            if($hasVariable) {
                $this->query[$field] = str_replace(
                    $toReplace,
                    $this->setFieldName($fieldName),
                    $this->query[$field]
                );
            }
        });
    }

    private function setFieldName(string $fieldName): mixed
    {
        return match($fieldName) {
            'total_ex' => $this->purchase->total,
            'total_inc' => $this->purchase->total_incl,
            'name' => $this->purchase->timesheet->user->name,
            'customer' => $this->purchase->timesheet->customer->name,
            'hours' => $this->purchase->hours,
            default => $this->purchase->$fieldName,
        };
    }
}