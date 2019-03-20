<?php

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'site_name',
    'label'        =>    __('Nom de la boutique', 'nexo'),
    'desc'        =>    __('Vous pouvez utiliser le nom du site', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_address_1',
    'label'        =>    __('Address 1', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_address_2',
    'label'        =>    __('Address 2', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_city',
    'label'        =>    __('Ville', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_phone',
    'label'        =>    __('Téléphone pour la boutique', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>   store_prefix() . 'nexo_shop_street',
    'label'        =>    __('Rue de la boutique', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_pobox',
    'label'        =>    __('Boite postale', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_email',
    'label'        =>    __('Email pour la boutique', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'text',
    'name'        =>    store_prefix() . 'nexo_shop_fax',
    'label'        =>    __('Fax pour la boutique', 'nexo')
), $namespace, 1);

$this->Gui->add_item(array(
    'type'        =>    'textarea',
    'name'        =>    store_prefix() . 'nexo_other_details',
    'label'        =>    __('Détails supplémentaires', 'nexo'),
    'description'    =>    __('Ce champ est susceptible d\'être utilisé au pied de page des rapports', 'nexo')
), $namespace, 1);