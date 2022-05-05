<?php

namespace mot\view;

use mot\component\View;

class Table extends View
{
    public function setTableColumns($columns = [])
    {
        foreach ($columns as &$column) {
            if (!isset($column['name'])) {
                $column['name'] = '';
            }
            if (!isset($column['type'])) {
                $column['type'] = 'text';
            }
        }

        $this->vars['table_columns'] = $columns;
    }

    public function setTableItems($items = [])
    {
        $tableItems = [];
        foreach ($items as $item) {
            $columns = [];
            foreach ($this->vars['table_columns'] as $column) {
                $column['content'] = isset($item[$column['name']]) ? $item[$column['name']] : '';
                if (isset($column['handler']) && is_callable($column['handler'])) {
                    $columns[] = array_merge($column, $column['handler']($item));
                    continue;
                }
                if (isset($column['callback']) && is_callable($column['callback'])) {
                    $column['content'] = $column['callback']($item);
                }
                if (isset($column['format']) && is_callable($column['format'])) {
                    $column['content'] = $column['format']($column['content']);
                }
                $columns[] = $column;
            }
            $tableItems[] = $columns;
        }

        $this->vars['table_items'] = $tableItems;
    }

    public function parse($manifest = [])
    {
        parent::parse($manifest);

        if (isset($manifest['table_columns'])) {
            $this->setTableColumns($manifest['table_columns']);
        }

        if (isset($manifest['table_items'])) {
            if (!is_array($manifest['table_items'])) {
                $manifest['table_items'] = json_decode(json_encode($manifest['table_items']), true);
            }
            $this->setTableItems($manifest['table_items']);
        }
    }
}
