<?php

namespace mot\view;

use mot\component\View;

class Login extends View
{

    public function setLoginFields($fileds = [])
    {
        $this->vars['login_fields'] = $fileds;
    }

    public function setLoginSubmit($submit = [])
    {
        if (empty($submit['button'])) {
            $submit['button'] = [
                'title' => 'Submit',
                'url' => '',
                'method' => 'post',
                'class' => 'primary',
            ];
        }
        $this->vars['login_submit'] = $submit;
    }

    public function setLoginTips($tips = [])
    {
        $this->vars['login_tips'] = $tips;
    }

    public function parse($manifest = [])
    {
        parent::parse($manifest);

        if (isset($manifest['login_fields'])) {
            $this->setLoginFields($manifest['login_fields']);
        }
        if (isset($manifest['login_tips'])) {
            $this->setLoginTips($manifest['login_tips']);
        }
        $this->setLoginSubmit($manifest['login_submit']);
    }
}
