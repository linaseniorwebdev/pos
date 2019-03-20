<?php

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_soundfx',
    'label'        =>    __('Activer les effets sonores', 'nexo'),
    'options'    =>    array(
        'disable'        =>    __('Désactiver', 'nexo'),
        'enable'        =>    __('Activer', 'nexo')
    )
), $namespace, 1 );