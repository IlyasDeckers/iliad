<?php
namespace Iliad\Concerns;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait Validator
{
    /**
     * Validate the incoming request.
     *
     * @throws ValidationException
     */
    private function validator(string $function, Request $request): void
    {
        if (isset($this->rules[$function]) && !is_null($this->rules[$function])) {
            $this->validate($request,
                (new $this->rules[$function])->rules()
            );
        }
    }

    /**
     * Apply rules to the given request
     *
     * @param array<int, object> $rules
     * @throws Exception
     */
    public function enforce(array $rules, int $status = 412): void
    {
        $messages = collect($rules)
            ->map(function ($rule) {
                if (!$rule->passes()) {
                    return [
                        'code' => $rule->code(),
                        'message' => $rule->message()
                    ];
                }
                return null;
            })
            ->filter()
            ->values();

        $errorMessages = $messages->pluck('message')->implode(', ');

        if (!$messages->isEmpty()) {
            throw new Exception($errorMessages);
        }
    }
}