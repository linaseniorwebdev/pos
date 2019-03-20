<?php 
use Carbon\Carbon;
?>
<script>
    <?php $dateString    =   Carbon::parse( date_now() )->toDateString();?>
    var report      =   <?php echo json_encode([
        'url'       =>  dashboard_url([ 'reports', 'json-daily-log', store_get_param( '?' ) ]),
        'today'     =>  dashboard_url([ 'reports', 'save-daily-log', store_get_param( '?' ) . '&date=' . $dateString ]),
        'labels'    =>  [
            __( 'Lundi', 'nexo' ),
            __( 'Mardi', 'nexo' ),
            __( 'Mercredi', 'nexo' ),
            __( 'Jeudi', 'nexo' ),
            __( 'Vendredi', 'nexo' ),
            __( 'Samedi', 'nexo' ),
            __( 'Dimanche', 'nexo' ),
        ],
        'totalPaid'         =>  __( 'Commandes Payées', 'nexo' ),
        'totalRefunds'      =>  __( 'Commandes Remboursées', 'nexo' ),
        'totalUnpaid'       =>  __( 'Commandes Impayées', 'nexo' ),
        'totalPartially'    =>  __( 'Commandes Partielles', 'nexo' ),
        'totalSales'        =>  __( 'Toutes les ventes', 'nexo' ),
    ]);?>
</script>
<?php include_once( MODULESPATH . 'nexo/inc/angular/order-list/filters/money-format.php' );?>
<script src="<?php echo module_url( 'nexo' ) . '/js/dashboard-report.js';?>"></script>