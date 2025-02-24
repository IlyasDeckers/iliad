<?php
namespace Clockwork\Core\Concerns;

trait Editable {
    public function formatMessage(): void
    {
        $fields = collect([
            'message', 'subject'
        ]);

        foreach ($this->variables as $variable) {
            $this->replace($fields, $variable);
        }

        // dd($this->query->message);
    }

    private function replace($fields, $variable): void
    {
        $fields->each(function ($field) use ($variable) {
            $toReplace = '{{ ' . $variable . ' }}';
            $fieldName = str_replace('-', '_', $variable);
            $hasVariable = strpos(
                $this->query[$field], $toReplace
            );

            if($hasVariable !== false) {
                $this->query[$field] = str_replace(
                    $toReplace,
                    $this->setFieldName($fieldName),
                    $this->query[$field]
                );
            } 
        });
    }

    private function setFieldName($fieldName)
    {
        if ($fieldName === 'total_ex') {
            $result = $this->purchase->total;
        } elseif ($fieldName === 'total_inc') {
            $result = $this->purchase->total_incl;
        } elseif ($fieldName === 'name') {
            $result = $this->purchase->timesheet->user->name;
        } elseif ($fieldName === 'customer') {
            $result = $this->purchase->timesheet->customer->name;
        } elseif ($fieldName === 'hours') {
            $result = $this->purchase->hours;
        } else {
            $result = $this->purchase->$fieldName;
        }

        return $result;
    }
}