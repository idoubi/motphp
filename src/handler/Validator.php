<?php

namespace mot\handler;

class Validator
{
    protected $validator;
    protected $fields;

    public function __construct(array $fields)
    {
        $this->validator = new \Valitron\Validator([]);
        $this->fields = $fields;

        if ($this->fields) {
            $this->buildRules();
        }
    }

    protected function buildRules()
    {
        $validator = $this->validator;
        $fields = $this->fields;

        foreach ($fields as $field) {
            if (empty($field['name'])) {
                continue;
            }
            if (!empty($field['required'])) {
                $requiredMsg = !empty($field['title']) ? $field['title'] . '必填' : $field['name'] . ' is required';
                $validator = $validator->rule('required', $field['name'])->message($requiredMsg);
            }
            if (empty($field['validate']) || !is_array($field['validate'])) {
                continue;
            }
            foreach ($field['validate'] as $validate) {
                if (empty($validate['rule'])) {
                    continue;
                }
                $params = isset($validate['params']) && is_array($validate['params']) ? $validate['params'] : [];
                $validator = $validator->rule($validate['rule'], $field['name'], ...$params);
                if (!empty($validate['message'])) {
                    $validator = $validator->message($validate['message']);
                }
            }
        }

        $this->validator = $validator;
    }

    public function validateData($data): array
    {
        $validator = $this->validator->withData($data);

        return [$validator->validate(), $validator->errors()];
    }
}
