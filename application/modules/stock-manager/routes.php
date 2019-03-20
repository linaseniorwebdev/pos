<?php
global $Routes;

$Routes->get( '/nexo/transfert/add', 'Nexo_Stock_Manager_Controller@new_transfert' );
$Routes->get( '/nexo/transfert/request', 'Nexo_Stock_Manager_Controller@request' );
$Routes->get( '/nexo/stock-transfert-invoice/{transfert_id}', 'Nexo_Stock_Manager_Controller@transfert_invoice' );
$Routes->match([ 'get', 'post' ], '/nexo/transfert/{params?}/{id?}', 'Nexo_Stock_Manager_Controller@transfert_history', [ 'defaultParameterRegex' => '[\w|.|-]+' ]);
$Routes->get( '/nexo/settings/stock', 'Nexo_Stock_Manager_Controller@settings' );

/**
 * Stock Transfert
 */
$Routes->get( 'stock-transfert/history/report/{id}', 'Nexo_Stock_Manager_Controller@transfert_invoice');