<?php

namespace mot\controller;

use mot\component\Controller;
use mot\handler\Validator;
use mot\handler\DataModel;

class AdminBase extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function success($msg = 'success', $url = '')
    {
        if ($this->request->isPost()) {
            return $this->response->stdout(0, $msg, ['redirect_url' => $url, 'interval' => 3]);
        }

        return $this->jump('success', $msg, $url);
    }

    public function error($msg = 'error', $url = '')
    {
        if ($this->request->isPost()) {
            return $this->response->stdout(-1, $msg, ['redirect_url' => $url, 'interval' => 3]);
        }

        return $this->jump('error', $msg, $url);
    }

    public function jump($type, $msg = '', $url = '', $interval = 3)
    {
        $manifest = [
            'title' => $msg,
            'layout' => 'jump',
            'jump_page' => [
                'type' => $type,
                'title' => $msg,
                'url' => $url,
                'interval' => $interval
            ]
        ];

        return $this->render($manifest);
    }

    public function render(array $manifest)
    {
        $layout = !empty($manifest['layout']) ? $manifest['layout'] : 'blank';

        if ($layout == 'form' && $this->request->isPost()) {
            return $this->handleSubmit($manifest);
        }

        return $this->showView($layout, $manifest);
    }

    protected function showView(string $layout, array $manifest)
    {
        $this->initView($layout);

        if ($layout == 'table' && !isset($manifest['table_items']) && isset($manifest['table_query'])) {
            try {
                $model = new DataModel($manifest['table_query'], $this->db);
                list($manifest['table_items'], $manifest['paginate']) = $model->getPagedData($this->path, $this->request->params());
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        if ($layout == 'form' && !isset($manifest['form_data']) && isset($manifest['form_query'])) {
            try {
                $model = new DataModel($manifest['form_query'], $this->db);
                $manifest['form_data'] = $model->findFirstData();
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        if ($layout == 'card' && !isset($manifest['card_items']) && isset($manifest['card_query'])) {
            try {
                $model = new DataModel($manifest['card_query'], $this->db);
                list($items, $manifest['paginate']) = $model->getPagedData($this->path, $this->request->params());
                if (isset($manifest['card_map']) && is_callable($manifest['card_map'])) {
                    $cardItems = [];
                    foreach ($items as $item) {
                        $cardItems[] = $manifest['card_map']($item);
                    }
                    $manifest['card_items'] = $cardItems;
                }
            } catch (\Exception $e) {
            }
        }

        $this->view->parse($manifest);

        $layoutFile = sprintf('layouts/%s.html', $layout);
        $vars = $this->view->getVars();

        $res = $this->view->render($layoutFile, $vars);
        die($res);
    }

    protected function initView(string $layout)
    {
        $view = $this->view->init($layout);

        if (isset($this->system)) {
            $view->setSystem($this->system);
        }
        if (isset($this->title)) {
            $view->setTitle($this->title);
        }
        if (isset($this->gobackUrl)) {
            $view->setGobackUrl($this->gobackUrl);
        }
        if (isset($this->logo)) {
            $view->setLogo($this->logo);
        }
        if (isset($this->user)) {
            $view->setUser($this->user);
        }
        if (isset($this->headnav)) {
            $view->setHeadNav($this->headnav);
        }
        if (isset($this->sidenav)) {
            $view->setSideNav($this->sidenav);
        }
        if (isset($this->tabnav)) {
            $view->setTabNav($this->tabnav);
        }
        if (isset($this->crumb)) {
            $view->setCrumb($this->crumb);
        }

        $this->view = $view;
    }

    public function handleSubmit(array $manifest)
    {
        $params = $this->request->params() ?: [];

        if (empty($manifest['form_fields']) || !is_array($manifest['form_fields'])) {
            return $this->error('invalid form fields');
        }
        $formFields = $manifest['form_fields'];

        if (empty($manifest['form_submit']) || !is_array($manifest['form_submit'])) {
            return $this->error('invalid form submit');
        }
        $formSubmit = $manifest['form_submit'];

        if (empty($formSubmit['act']) || !in_array($formSubmit['act'], ['insert', 'update'])) {
            return $this->error('invalid form submit act');
        }
        $act = $formSubmit['act'];

        try {
            $this->filterGroupFields($formFields, $params);
            $validator = new Validator($formFields);
            list($validateRes, $validateErrors) =  $validator->validateData($params);
            if (!$validateRes) {
                foreach ($validateErrors as $v) {
                    if ($v && count($v) > 0) {
                        return $this->error($v[0]);
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->error('validate error: ' . $e->getMessage());
        }

        $fieldNames = [];
        foreach ($formFields as $field) {
            if (empty($field['name'])) {
                continue;
            }
            $fieldNames[] = $field['name'];
        }

        $fieldValues = [];
        foreach ($fieldNames as $fieldName) {
            if (isset($params[$fieldName])) {
                $fieldValues[$fieldName] = $params[$fieldName];
            }
        }

        if (!empty($formSubmit['before_handler']) && is_callable($formSubmit['before_handler'])) {
            $newFieldValus = $formSubmit['before_handler']($fieldValues);
            if ($newFieldValus && is_array($newFieldValus)) {
                $fieldValues = $newFieldValus;
            }
        }

        $model = new DataModel($formSubmit, $this->db);

        if ($act == 'insert') {
            try {
                $res = $model->insertData($fieldValues);
                if (isset($res['id'])) {
                    $fieldValues['id'] = $res['id'];
                }
            } catch (\Exception $e) {
                return $this->error('insert data failed: ' . $e->getMessage());
            }
        }

        if ($act == 'update') {
            try {
                $res = $model->updateData($fieldValues);
            } catch (\Exception $e) {
                return $this->error('update data failed: ' . $e->getMessage());
            }
        }

        $successUrl = '';
        $successMessage = 'success';
        if (!empty($manifest['form_submit']['success'])) {
            if (is_callable($manifest['form_submit']['success'])) {
                return $manifest['form_submit']['success']($fieldValues);
            }
            if (isset($manifest['form_submit']['success']['url'])) {
                $successUrl = $manifest['form_submit']['success']['url'];
            }
            if (isset($manifest['form_submit']['success']['message'])) {
                $successMessage = $manifest['form_submit']['success']['message'];
            }
        }

        return $this->success($successMessage, $successUrl);
    }

    private function filterGroupFields(&$fields, &$params)
    {
        $groupNames = [];
        foreach ($fields as $k => $field) {
            if (empty($field['name']) || empty($field['group_change']) || empty($params[$field['name']])) {
                continue;
            }

            $groupNames[] = sprintf('%s_%s', $field['name'], $params[$field['name']]);
        }

        foreach ($fields as $k => $field) {
            if (empty($field['name']) || empty($field['group'])) {
                continue;
            }

            if (!in_array($field['group'], $groupNames)) {
                unset($fields[$k]);
                unset($params[$field['name']]);
            }
        }
    }
}
