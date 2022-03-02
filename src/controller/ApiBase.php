<?php

namespace mot\controller;

use mot\component\Controller;
use mot\handler\DataModel;
use mot\handler\Validator;

class ApiBase extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function output($manifest = [])
    {
        $layout = !empty($manifest['layout']) ? $manifest['layout'] : 'stdout';

        $params = $this->request->params();

        if (!empty($manifest['params']) && is_array($manifest['params'])) {
            try {
                $paramFields = $manifest['params'];
                $validator = new Validator($paramFields);
                list($validateRes, $validateErrors) =  $validator->validateData($params);
                if (!$validateRes) {
                    foreach ($validateErrors as $v) {
                        if ($v && count($v) > 0) {
                            return $this->response->error($v[0]);
                        }
                    }
                }
            } catch (\Exception $e) {
                return $this->response->error('validate error: ' . $e->getMessage());
            }
        }

        if (in_array($layout, ['json', 'stdout'])) {
            if (empty($manifest['data']) && !empty($manifest['data_query'])) {
                $model = new DataModel($manifest['data_query']);
                if (!empty($manifest['data_query']['act']) && $manifest['data_query']['act'] == 'first') {
                    $manifest['data'] = $model->findFirstData();
                } else {
                    list($manifest['data'], $manifest['paginate']) = $model->getPagedData($this->path, $this->request->params());
                }
            }
            if ($layout == 'json') {
                return $this->response->json($manifest['data']);
            }
            if ($layout == 'stdout') {
                return $this->response->stdout(0, 'success', $manifest['data']);
            }
        }

        return $this->response->string('');
    }
}
