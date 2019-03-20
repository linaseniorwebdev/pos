<?php

use Carbon\Carbon;

/**
 * Get Orders
 */
$date           =   Carbon::parse( date_now() );
$orders         =   $this->db
    ->where( 'DATE_CREATION >=', $date->copy()->startOfDay()->toDateTimeString() ) 
    ->where( 'DATE_CREATION <=', $date->copy()->endOfDay()->toDateTimeString() ) 
    ->get( store_prefix() . 'nexo_commandes' )
    ->result_array();
$ordersCode     =   array_map( function( $order ) {
    return $order[ 'CODE' ];
}, $orders );

/**
 * Get items for defined orders
 */
// $items          =   $this->db->where_in( 'REF_COMMAND_CODE', $ordersCode )
// ->get( store_prefix() . 'nexo_commandes_produits' )
// ->result_array();

/**
 * Calculating Taxes
 */
$totalTaxes     =   array_sum( array_map(function( $order ) {
    return floatval( $order[ 'TVA' ] );
}, $orders ) );

/**
 * Calculating Total excluding taxes
 */
$totalWithoutTaxes     =   array_sum( array_map(function( $order ) {
    return floatval( $order[ 'TOTAL' ] ) - $order[ 'TVA' ];
}, $orders ) );

/**
 * Total With taxes included
 */
$totalWithTaxes     =   array_sum( array_map(function( $order ) {
    return floatval( $order[ 'TOTAL' ] );
}, $orders ) );

$this->load->model( 'Nexo_Misc' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div class="container-fluid">
        <div class="text-center">
            <h2><?php echo store_option( 'site_name' );?></h2>
            <H4><?php echo __( 'Rapport des ventes journalières', 'nexo' );?></H4>
            <p><?php echo Carbon::parse( date_now() )->toDateString();?></p>
            <small><?php echo sprintf( __( 'Ce rapport à été envoyée depuis l\'installation %s', 'nexo' ), base_url() );?></small>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12 text-center" style="height: 500px">
                
            </div>
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td><?php echo __( 'Ventes', 'nexo' );?></td>
                            <td class="text-center" style="width:100px" width="200"><?php echo __( 'Quantité', 'nexo' );?></td>
                            <td class="text-center" width="200"><?php echo __( 'Total', 'nexo' );?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orderType   =   [
                            'nexo_order_comptant'   =>  __( 'Complètes', 'nexo' ),
                            'order_order_advance'   =>  __( 'Incomplètes', 'nexo' ),
                            'nexo_order_devis'      =>  __( 'Sans Paiement', 'nexo' ),
                        ];
                        ?>
                        <?php foreach( array_keys( $orderType ) as $type ):?>
                        <?php
                        $_orders    =   array_filter( $orders, function( $order ) use ( $type ) {
                            return $order[ 'TYPE' ] === $type;
                        });

                        /**
                         * Calculating the total for these order type
                         */
                        $_total         =   array_sum( array_map( function( $order ) {
                            return  floatval( $order[ 'TOTAL' ] );     
                        }, $_orders ) );
                        ?>
                        <tr>
                            <td><?php echo $orderType[ $type ];?></td>
                            <td class="text-right"><?php echo count( $_orders );?></td>
                            <td class="text-right"><?php echo $this->Nexo_Misc->cmoney_format( $_total );?></td>
                        </tr>
                        <?php endforeach;?>

                        <tr class="table-info">
                            <td><?php echo __( 'Total avec TVA', 'nexo' );?></td>
                            <td></td>
                            <td class="text-right"><?php echo $this->Nexo_Misc->cmoney_format( $totalWithTaxes );?></td>
                        </tr>
                        <tr class="table-danger">
                            <td><?php echo __( 'TVA', 'nexo' );?></td>
                            <td></td>
                            <td class="text-right"><?php echo $this->Nexo_Misc->cmoney_format( $totalTaxes );?></td>
                        </tr>
                        <tr class="table-success">
                            <td><?php echo __( 'Total sans TVA', 'nexo' );?></td>
                            <td></td>
                            <td class="text-right"><?php echo $this->Nexo_Misc->cmoney_format( $totalWithoutTaxes );?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <style>
    <?php include_once( MODULESPATH . '/nexo/inc/bootstrap-style.php' );?>
    </style>
</body>
</html>