<?php

namespace mot\component;

use mot\view\Table;
use mot\view\Form;
use mot\view\Card;
use mot\view\Login;
use mot\view\Jump;

class View
{
    protected $vars = [
        'system' => [],
        'logo' => [],
        'user' => [],
        'title' => '',
        'goback_url' => '',
        'layout' => '',
        'headnav' => [],
        'sidenav' => [],
        'tabnav' => [],
        'crumb' => [],
        'search' => [],
        'tip' => [],
        'toolbar' => [],
        'table_columns' => [],
        'table_items' => [],
        'form_fields' => [],
        'form_data' => [],
        'form_submit' => [],
        'card_items' => [],
        'paginate' => [],
        'login_fields' => [],
        'login_submit' => [],
        'jump_page' => [],
    ];

    public function __construct($view)
    {
        $this->innerView = $view;
    }

    public function init($layout)
    {
        $this->view = new self($this->innerView);

        if ($layout == 'table') {
            $this->view = new Table($this->innerView);
        }
        if ($layout == 'form') {
            $this->view = new Form($this->innerView);
        }
        if ($layout == 'card') {
            $this->view = new Card($this->innerView);
        }
        if ($layout == 'login') {
            $this->view = new Login($this->innerView);
        }
        if ($layout == 'jump') {
            $this->view = new Jump($this->innerView);
        }

        $this->view->setLayout($layout);

        return $this->view;
    }

    public function parse($manifest = [])
    {
        if (isset($manifest['system'])) {
            $this->setSystem($manifest['system']);
        }
        if (isset($manifest['title'])) {
            $this->setTitle($manifest['title']);
        }
        if (isset($manifest['goback_url'])) {
            $this->setGobackUrl($manifest['goback_url']);
        }
        if (isset($manifest['logo'])) {
            $this->setLogo($manifest['logo']);
        }
        if (isset($manifest['user'])) {
            $this->setUser($manifest['user']);
        }
        if (isset($manifest['headnav'])) {
            $this->setHeadNav($manifest['headnav']);
        }
        if (isset($manifest['sidenav'])) {
            $this->setSideNav($manifest['sidenav']);
        }
        if (isset($manifest['tabnav'])) {
            $this->setTabNav($manifest['tabnav']);
        }
        if (isset($manifest['crumb'])) {
            $this->setCrumb($manifest['crumb']);
        }
        if (isset($manifest['search'])) {
            $this->setSearch($manifest['search']);
        }
        if (isset($manifest['tip'])) {
            $this->setTip($manifest['tip']);
        }
        if (isset($manifest['toolbar'])) {
            $this->setToolbar($manifest['toolbar']);
        }
        if (isset($manifest['paginate'])) {
            $this->setPaginate($manifest['paginate']);
        }
    }

    public function setLayout($layout = '')
    {
        $this->vars['layout'] = $layout;
    }

    public function setSystem($system = [])
    {
        $this->vars['system'] = $system;
    }

    public function setTitle($title = '')
    {
        $this->vars['title'] = $title;
    }

    public function setGobackUrl($url = '')
    {
        $this->vars['goback_url'] = $url;
    }

    public function setLogo($logo = [])
    {
        $this->vars['logo'] = $logo;
    }

    public function setUser($user = [])
    {
        $this->vars['user'] = $user;
    }

    public function setHeadNav($headnav = [])
    {
        $this->vars['headnav'] = $headnav;
    }

    public function setSideNav($sidenav = [])
    {
        $this->vars['sidenav'] = $sidenav;
    }

    public function setTabNav($tabnav = [])
    {
        $this->vars['tabnav'] = $tabnav;
    }

    public function setCrumb($crumb = [])
    {
        $this->vars['crumb'] = $crumb;
    }

    public function setSearch($search = [])
    {
        $this->vars['search'] = $search;
    }

    public function setTip($tip = [])
    {
        if (is_string($tip)) {
            $tip = [
                'content' => $tip
            ];
        }
        $this->vars['tip'] = $tip;
    }

    public function setToolbar($toolbar = [])
    {
        $this->vars['toolbar'] = $toolbar;
    }

    public function setPaginate($paginate = [])
    {
        $this->vars['paginate'] = $paginate;
    }

    public function setJump($jump = [])
    {
        $this->vars['jump'] = $jump;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->innerView, $method)) {
            return call_user_func_array([$this->innerView, $method], $arguments);
        }

        throw new \Exception('method not exists: ' . $method);
    }
}
