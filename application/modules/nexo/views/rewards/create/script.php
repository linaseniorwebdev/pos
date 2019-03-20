<script>
    const RewardCreateVueData   =   <?php echo json_encode([
        'coupons'   =>  $coupons,
        'reward'    =>  @$reward,
        'url'       =>  [
            'put'   =>  site_url([ 'api', 'nexopos', 'rewards-system', '#', store_get_param( '?' ) ]),
            'post'  =>  site_url([ 'api', 'nexopos', 'rewards-system', store_get_param( '?' ) ]),
            'list'  =>  dashboard_url([ 'rewards-system?notice=created']),
            'delete'    =>  site_url([ 'api', 'nexopos', 'rewards-system', 'rule', '#', store_get_param( '?' )])
        ],
        'textDomain'    =>  [
            'confirmAction'         =>  __( 'Confirmez votre action', 'nexo' ),
            'confirmMessage'        =>  __( 'Souhaitez-vous supprimer cette règle ?', 'nexo' ),
            'errorOccured'          =>  __( 'Une erreur s\'est produite', 'nexo' ),
            'hasConflictedRules'    =>  __( '2 ou plusieurs règles semblent avoir des valeurs identiques. Vous devez vous assurez de définir des règles différentes.', 'nexo' ),
            'hasEmptyField'         =>  __( 'Impossible de continuer, tous les champs necessaires ne sont pas remplies : titre, coupon, objectif', 'nexo' ),
            'rulesMissing'          =>  __( 'Impossible d\'enregistrer une récompense sans règles.', 'nexo' )
        ]
    ]);?>;
</script>
<script src="<?php echo module_url( 'nexo' ) . '/js/rewards.create.vue.js';?>"></script>