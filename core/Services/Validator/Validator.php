<?php

namespace Core\Services\Validator;

class Validator
{
    private string $dbGroup = 'default';
    private array $rules = [];
    private array $data = [];
    private array $attributes = [];
    private array $messages = [];
    private array $errors = [];
    public function dbGroup(string $dbGroup): self
    {
        $this->dbGroup = $dbGroup;
        return $this;
    }
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }
    public function rules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }
    public function attributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }
    public function messages(array $messages): self
    {
        $this->messages = $messages;
        return $this;
    }
    public function validate(): self
    {
        foreach ($this->rules as $dataKey => $rules) {

            $value = $this->data[$dataKey] ?? null;


            $isRequired = in_array('required', $rules);
            $hasValue = Rules::required($value);


            if (!($isRequired || $hasValue))
                continue;

            foreach ($rules as $rule) {

                preg_match('/(\w+)\[(.+)\]/', $rule, $matches);

                $params = [];

                if ($matches && count($matches) > 2) {
                    $rule = $matches[1];
                    $params = explode('][', array_slice($matches, 2)[0]);
                }

                $args = [$value, ...$params];

                // executing validation method
                if (!Rules::$rule(...$args)) {
                    $this->errors[$dataKey] = ['rule' => $rule, 'params' => $params];
                    break;
                }
            }

        }
        $this->parseValidationErrors();

        return $this;
    }

    public function addError(string $key, string $message): void
    {
        $this->errors[$key] = $message;
    }
    public function passed(): bool
    {
        return empty($this->errors);
    }
    public function failed(): bool
    {
        return !$this->passed();
    }
    public function getErrors(): array
    {
        return $this->errors;
    }
    public function reset(): void
    {
        $this->rules = [];
        $this->errors = [];
        $this->data = [];
        $this->attributes = [];
        $this->messages = [];
    }

    private function parseValidationErrors()
    {
        if (empty($this->errors))
            return;
        $messages = require_once 'validation_messages.php';

        foreach ($this->errors as $dataKey => $data) {

            if (isset($data['rule']) && isset($data['params'])) {

                $rule = $data['rule'];
                $params = $data['params'];
                $message = '';

                $params_replace = array_map(fn(int $index): string => '{param.' . ($index + 1) . '}', array_keys($params));

                if (isset($messages[$rule])) {
                    $fieldName = $this->attributes[$dataKey] ?? $dataKey;
                    $message = $this->messages[$dataKey][$rule] ?? $messages[$rule];
                    $message = str_replace(
                        ['{field}', ...$params_replace],
                        [$fieldName, ...(array) $params],
                        $message
                    );
                } else {
                    $message = "Validation Failed for rule '$rule'.";
                }

                $this->errors[$dataKey] = $message;

            }

        }

    }
}
