<?php

namespace mot\controller;

use mot\component\Controller;
use mot\handler\DataModel;

class ApiBase extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function output($manifest = [])
    {
        $layout = !empty($manifest['layout']) ? $manifest['layout'] : 'stdout';

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
