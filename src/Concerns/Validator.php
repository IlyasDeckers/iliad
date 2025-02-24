<?php
namespace Clockwork\Core\Concerns;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait Validator
{
    /**
     * Validate the incomming request.
     *
     * @param string $function
     * @param Request $request
     * @return void
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
     *  Apply rules to the given request
     *
     * @param $rules
     * @param int $status
     * @throws Exception
     */
    public function enforce($rules, int $status = 412): void
    {
        $messages = tap(collect(), function ($messages) use ($rules) {
            collect($rules)->each(function ($rule) use ($messages) {
                if (!$rule->passes()) {
                    $messages->push([
                        'code' => $rule->code(),
                        'message' => $rule->message()
                    ]);

                    if ($rule->break()) {
                        return false;
                    }
                }

                return true;
            });
        });

        $_messages = '';
        foreach ($messages as $message) {
            $_messages = $message['message'] . ', ' . $_messages;
        }

        if (!$messages->isEmpty()) {
            throw new Exception(
                $_messages
            );
        }
    }

}