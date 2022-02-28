<?php

namespace mot\view;

use mot\component\View;

class Card extends View
{

    public function setCardItems($items = [])
    {
        $this->vars['card_items'] = $items;
    }

    public function parse($manifest = [])
    {
        parent::parse($manifest);

        if (isset($manifest['card_items'])) {
            $this->setCardItems($manifest['card_items']);
        }
    }
}
