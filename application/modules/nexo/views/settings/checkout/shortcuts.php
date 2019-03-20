<?php
$this->Gui->add_item(array(
    'type'        =>    'text',
    'label'        =>    __('Touches Raccourcis', 'nexo'),
    'name'        =>    store_prefix() . 'keyshortcuts',
    'description'    =>    __('Définissez des valeurs numériques séparée par des tirets verticaux. Exemple : 50|75|99.5|200.', 'nexo')
), $namespace, 1);