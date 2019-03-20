<?php
$Routes->post( 'nexopos/approve_transfert', 'ApiNexoStockManager@approve_transfert' );
$Routes->post( 'nexopos/cancel_transfert', 'ApiNexoStockManager@cancel_transfert' );
$Routes->post( 'nexopos/reject_transfert', 'ApiNexoStockManager@reject_transfert' );
$Routes->post( 'nexopos/approve_request', 'ApiNexoStockManager@approve_request' );
$Routes->post( 'nexopos/verification', 'ApiNexoStockManager@verification' );
$Routes->post( 'nexopos/proceed_request', 'ApiNexoStockManager@proceedStockRequest' );