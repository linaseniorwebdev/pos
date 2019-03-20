<?php
$this->Gui->add_item(array(
    'type'          =>    'select',
    'name'          =>    store_prefix() . 'nexo_enable_registers',
    'label'         =>    __('Utiliser les caisses enregistreuses', 'nexo'),
    'options'       =>    array(
		''		    =>	__( 'Veuillez choisir une option', 'nexo' ),
        'oui'       =>    __('Oui', 'nexo'),
        'non'       =>    __('Non', 'nexo')
    )
), $namespace, 1);

if ( store_option( 'nexo_enable_registers' ) === 'oui' ):

    $this->Gui->add_item(array(
        'type'        =>    'select',
        'name'        =>    store_prefix() . 'nexo_cashier_session_counted',
        'label'        =>    __( 'Activer les sessions des utilisateurs', 'nexo'),
        'description'   =>  __( 'Permet de compter le nombre de temps d\'activité d\'un caissier', 'nexo' ),
        'options'    =>    array(
            ''		=>	__( 'Veuillez choisir une option', 'nexo' ),
            'yes'        =>    __('Oui', 'nexo'),
            'no'        =>    __('Non', 'nexo')
        )
    ), $namespace, 1);

    $this->Gui->add_item(array(
        'type'        =>    'select',
        'name'        =>    store_prefix() . 'nexo_cashier_idle_after',
        'label'        =>    __( 'Temps d\'inactivité du caissier', 'nexo'),
        'description'   =>  __( 'Si vous souhaitez compter le temps d\'activité des caissiers, il est préférable de ne compter que le temps durant lequel ils sont actifs.', 'nexo' ),
        'options'    =>    [
            5   =>  __( '5 minutes', 'nexo' ),
            10   =>  __( '10 minutes', 'nexo' ),
            20   =>  __( '20 minutes', 'nexo' ),
            30   =>  __( '30 minutes', 'nexo' ),
            45   =>  __( '45 minutes', 'nexo' ),
            60   =>  __( '60 minutes', 'nexo' ),
            120   =>  __( '120 minutes', 'nexo' ),
        ]
    ), $namespace, 1);

endif;