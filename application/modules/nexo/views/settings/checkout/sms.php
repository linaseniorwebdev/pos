<?php
$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    store_prefix() . 'nexo_enable_smsinvoice',
    'label'        =>    __('Envoyer une facture par SMS', 'nexo'),
    'description'        =>    __('Permet d\'envoyer une facture par SMS pour les commandes complètes aux clients enregistrés.', 'nexo'),
    'options'    =>    array(
        ''            =>    __('Veuillez choisir une option', 'nexo'),
        'yes'        =>    __('Oui', 'nexo'),
        'no'        =>    __('Non', 'nexo')
    )
), $namespace, 1);