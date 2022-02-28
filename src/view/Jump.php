<?php

namespace mot\view;

use mot\component\View;

class Jump extends View
{
    public function setJumpPage($page = [])
    {
        $this->vars['jump_page'] = $page;
    }

    public function setLoginTips($tips = [])
    {
        $this->vars['login_tips'] = $tips;
    }

    public function parse($manifest = [])
    {
        parent::parse($manifest);

        if (isset($manifest['jump_page'])) {
            $this->setJumpPage($manifest['jump_page']);
        }
    }
}
