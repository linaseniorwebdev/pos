<?php
$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_disable_frontend',
    'label'        =>    __('Masquer le FrontEnd', 'nexo'),
    'options'    =>    array(
        'enable'        =>    __('Oui', 'nexo'),
        'disable'        =>    __('Non', 'nexo')
    ),
    'description'    =>    __('Cette option vous permet d\'effectuer une redirection vers le tableau de bord durant l\'accès à l\'interface publique', 'nexo')
), $namespace, 1);