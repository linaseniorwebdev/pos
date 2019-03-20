<?php
global $StoreRoutes;

$StoreRoutes->get( '/nexo/transfert/add', 'Nexo_Stock_Manager_Controller@new_transfert' );
$StoreRoutes->get( '/nexo/transfert/request', 'Nexo_Stock_Manager_Controller@request' );
$StoreRoutes->get( '/nexo/stock-transfert-invoice/{transfert_id}', 'Nexo_Stock_Manager_Controller@transfert_invoice', [ 'defaultParameterRegex' => '[\w|.|-]+' ]);
$StoreRoutes->match([ 'get', 'post' ], '/nexo/transfert/{params?}/{id?}', 'Nexo_Stock_Manager_Controller@transfert_history', [ 'defaultParameterRegex' => '[\w|.|-]+' ]);
$StoreRoutes->get( '/nexo/settings/stock', 'Nexo_Stock_Manager_Controller@settings' );