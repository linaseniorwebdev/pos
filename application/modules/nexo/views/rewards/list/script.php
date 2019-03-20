<script>
const rewardData    =   <?php echo json_encode([
    'url'   =>  [
        'create'    =>  dashboard_url([ 'rewards-system', 'create' ]),
        'delete'    =>  site_url([ 'api', 'nexopos', 'rewards-system', '#', store_get_param( '?' ) ]),
        'get'      =>   site_url([ 'api', 'nexopos', 'rewards-system', '#', store_get_param( '?' ) ]),
        'bulkDelete'    =>  site_url([ 'api', 'nexopos', 'rewards-system', 'bulk-delete', store_get_param( '?' ) ])
    ]
]);?>;

const textDomain    =   <?php echo json_encode([
    'action'                =>  __( 'Action', 'nexo' ),
    'mustFillSomething'     =>  __( 'Vous devez spécifier quelque chose à rechercher', 'nexo' ),
    'wouldYouDeleteThis'    =>  __( 'Souhaitez-vous supprimer cette entrée', 'nexo' ),
    'confirmAction'         =>  __( 'Veuillez confirmer votre action', 'nexo' ),
    'shouldSelectAnEntry'   =>  __( 'Vous devez au moins selectionner un élément', 'nexo' ),
    'warning'               =>  __( 'Attention', 'nexo' ),
    'deleteSelectedEntries' =>  __( 'Souhaitez-vous supprimer toutes les entrées sélectionnées. Vous devez vous assurer de dissocier les récompenses et les groupes de clients avant de continuer.', 'nexo' )
]);?>
</script>
<script src="<?php echo module_url( 'nexo' ) . '/js/rewards.list.vue.js';?>"></script>