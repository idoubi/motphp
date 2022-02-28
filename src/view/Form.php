<?php

namespace mot\view;

use mot\component\View;

class Form extends View
{

    public function setFormFields($fileds = [])
    {
        $this->vars['form_fields'] = $fileds;
    }

    public function setFormData($data = [])
    {
        $this->vars['form_data'] = $data;
        foreach ($this->vars['form_fields'] as &$field) {
            if (!$field['name'] || !isset($data[$field['name']])) {
                continue;
            }

            $field['value'] = $data[$field['name']];
        }
    }

    public function parse($manifest = [])
    {
        parent::parse($manifest);

        if (isset($manifest['form_fields'])) {
            $this->setFormFields($manifest['form_fields']);
        }
        if (isset($manifest['form_data'])) {
            $this->setFormData($manifest['form_data']);
        }
    }
}
