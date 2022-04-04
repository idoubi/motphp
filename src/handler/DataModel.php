<?php

namespace mot\handler;

class DataModel
{
    protected $manifest;
    protected $handler;

    public function __construct(array $manifest, $db = null)
    {
        if (empty($manifest['model']) && empty($manifest['table'])) {
            throw new \Exception('invalid orm object');
        }

        $handler = null;

        if ($manifest['model']) {
            // create orm with model
            if (is_string($manifest['model'])) {
                $handler = model($manifest['model']);
            } elseif (is_object($manifest['model'])) {
                $handler = $manifest['model'];
            }
        } elseif ($manifest['table'] && $db) {
            // create orm with table
            if (is_string($manifest['table'])) {
                $handler = $db->table($manifest['table']);
            }
        }

        if (!$handler || !is_object($handler)) {
            throw new \Exception('invalid model');
        }

        $this->manifest = $manifest;
        $this->handler = $handler;
    }

    public function getQueryHandler()
    {
        $this->buildHandlerWithColumns();
        $this->buildHandlerWithWhere();
        $this->buildHandlerWithOrder();
        $this->buildHandlerWithLimit();

        return $this->handler;
    }

    public function getInsertHandler()
    {
        return $this->handler;
    }

    public function getUpdateHandler()
    {
        $this->buildHandlerWithWhere();
        $this->buildHandlerWithOrder();
        $this->buildHandlerWithLimit();

        return $this->handler;
    }

    public function getPagedData(string $requestPath, array $requestParams): array
    {
        $pageRows = $this->manifest['page_rows'] ?: 20;

        $paginator = $this->getQueryHandler()->paginate($pageRows);

        $data = [];
        if ($paginator) {
            $p = new Paginator($paginator, $requestPath, $requestParams);

            $data = json_decode(json_encode($paginator->items()), true);
            foreach ($data as &$v) {
                $v = $this->handleSubQuery($v);
            }
            $paginate = $p->getPaginate();

            return [$data, $paginate];
        }

        return [[], []];
    }

    public function findFirstData(): array
    {

        $res = $this->getQueryHandler()->first();

        if ($res) {
            $res = json_decode(json_encode($res), true);
            $res = $this->handleSubQuery($res);
            if (!empty($this->manifest['after_handler']) && is_callable($this->manifest['after_handler'])) {
                $newRes = $this->manifest['after_handler']($res);
                if ($newRes) {
                    $res = $newRes;
                }
            }

            return $res;
        }

        return [];
    }

    public function insertData(array $data): array
    {
        $data = $this->autofillData($data);

        $res = $this->getInsertHandler()->create($data);

        return $res ? json_decode(json_encode($res), true) : [];
    }

    public function updateData(array $data): int
    {
        $data = $this->autofillData($data);

        $res = $this->getUpdateHandler()->update($data);

        return $res ? $res : 0;
    }

    protected function handleSubQuery($data)
    {
        if (empty($this->manifest['sub_query']) || !is_array($this->manifest['sub_query'])) {
            return $data;
        }

        $subQuery = $this->manifest['sub_query'];

        foreach ($subQuery as $column => $query) {
            if (!$column || !is_string($column) || !$query || !is_array($query)) {
                continue;
            }

            $model = new self($query);
            $handler = $model->getQueryHandler();
            if (!empty($query['map']) && is_array($query['map'])) {
                foreach ($query['map'] as $subField => $field) {
                    if (!$field || !is_string($field)) {
                        continue;
                    }
                    if (!is_string($subField)) {
                        $subField = $field;
                    }
                    if (!isset($data[$field])) {
                        continue;
                    }

                    $handler = $handler->where($subField, $data[$field]);
                }
            }

            if (!empty($query['act']) && $query['act'] == 'first') {
                $res = $handler->first();
            } else {
                $res = $handler->get();
            }

            $res = $res ? $res->toArray() : [];
            if (!empty($query['after_handler']) && is_callable($query['after_handler'])) {
                $newRes = $query['after_handler']($res);
                if ($newRes) {
                    $res = $newRes;
                }
            }
            $data[$column] = $res;
        }

        return $data;
    }

    protected function buildHandlerWithColumns()
    {
        if (empty($this->manifest['columns']) || !is_array($this->manifest['columns'])) {
            return;
        }

        $handler = $this->handler;
        $columns = $this->manifest['columns'];

        $handler = $handler->select(...$columns);

        $this->handler = $handler;
    }

    protected function buildHandlerWithWhere()
    {
        if (empty($this->manifest['where']) || !is_array($this->manifest['where'])) {
            return;
        }

        $handler = $this->handler;
        $where = $this->manifest['where'];

        foreach ($where as $field => $value) {
            if (!$field) {
                continue;
            }
            if (is_null($value)) {
                $handler = $handler->whereNull($field);
                continue;
            }
            if (is_string($value) || is_int($value) || is_bool($value)) {
                $handler = $handler->where($field, $value);
                continue;
            }
            if (is_array($value) && count($value) >= 2) {
                $extra = [
                    'between' => 'whereBetween',
                    'not between' => 'whereNotBetween',
                    'in' => 'whereIn',
                    'not in' => 'whereNotIn',
                ];
                if (isset($extra[$value[0]])) {
                    $handler = $handler->$extra[$value[0]]($field, $value[1]);
                    continue;
                }
                if ($value[0] == 'not' && is_null($value[1])) {
                    $handler = $handler->whereNotNull($field);
                    continue;
                }

                $handler = $handler->where($field, $value[0], $value[1]);
            }
        }

        $this->handler = $handler;
    }

    protected function buildHandlerWithOrder()
    {
        if (empty($this->manifest['order']) || !is_array($this->manifest['order'])) {
            return;
        }

        $handler = $this->handler;
        $order = $this->manifest['order'];

        foreach ($order as $field => $type) {
            if (!$field) {
                continue;
            }
            if (!in_array($type, ['asc', 'desc'])) {
                $type = 'asc';
            }

            $handler = $handler->orderBy($field, $type);
        }

        $this->handler = $handler;
    }

    protected function buildHandlerWithLimit()
    {
        if (empty($this->manifest['limit']) || !is_int($this->manifest['limit'])) {
            return;
        }

        $handler = $this->handler;
        $limit = $this->manifest['limit'];

        $handler = $handler->limit($limit);

        $this->handler = $handler;
    }

    protected function autofillData(array $data)
    {
        if (empty($this->manifest['autofill']) || !is_array($this->manifest['autofill'])) {
            return $data;
        }

        $autofilledData = $this->manifest['autofill'];

        return array_merge($autofilledData, $data);
    }
}
