<?php
$Routes->get( 'nexopos/history/{page?}', 'ApiNexoSystem@history' );
$Routes->get( 'nexopos/full-order/{order_id}', 'ApiNexoOrders@full_order' );
$Routes->get( 'nexopos/orders', 'ApiNexoOrders@orders' );
$Routes->get( 'nexopos/system-details', 'ApiNexoSystem@details' );
// $Routes->get( 'nexopos/orders', 'ApiNexoSystem@orders' );
$Routes->get( 'nexopos/registers', 'ApiNexoRegisters@getAll' );
$Routes->get( 'nexopos/registers/idle/{id}', 'ApiNexoRegisters@idleRegister' );
$Routes->get( 'nexopos/registers/active/{id}', 'ApiNexoRegisters@activeRegister' );
$Routes->get( 'nexopos/customers', 'ApiNexoSystem@customers' );
$Routes->get( 'nexopos/products', 'ApiNexoSystem@products' );
$Routes->get( 'nexopos/categories/{id?}', 'ApiNexoCategories@categories' );
$Routes->get( 'nexopos/options', 'ApiNexoSystem@options' );
$Routes->get( 'nexopos/orders/refund-history/{order_id}', 'ApiNexoOrders@refundHistory' );
$Routes->get( 'nexopos/orders/payments/{order_id}', 'ApiNexoOrders@payments' );

$Routes->post( 'nexopos/reports/monthly-sales', 'ApiNexoReports@monthly_sales' );
$Routes->post( 'nexopos/order-status/{order_id}', 'ApiNexoOrders@setOrderStatus' );
$Routes->post( 'nexopos/physicals-and-digitals/', 'ApiNexoItems@physicals_and_digitals' );
$Routes->post( 'nexopos/post-grouped', 'ApiNexoItems@post_grouped' );
$Routes->post( 'nexopos/put-grouped/{id}', 'ApiNexoItems@put_grouped' );
$Routes->post( 'nexopos/reporst-by-email', 'ApiNexoReports@sendByEmail' );
$Routes->post( 'nexopos/supplies', 'ApiNexoItems@createSupply' );
$Routes->post( 'nexopos/options', 'ApiNexoSystem@setOptions' );
$Routes->post( 'nexopos/cashiers/week-sales/{cashier_id}', 'ApiNexoReports@cashierWeekReport' );
$Routes->post( 'nexopos/cashiers/card/{cashier_id}', 'ApiNexoReports@cashierCard' );
$Routes->post( 'nexopos/cashiers/register-history/{cashier_id}', 'ApiNexoReports@cashierRegisterHistory' );
$Routes->post( 'nexopos/orders/payment/{order_id}', 'ApiNexoOrders@payment' );
$Routes->post( 'nexopos/orders/refund/{order_id}', 'ApiNexoOrders@refund' );

/**
 * Sync Down
 */
$Routes->post( 'nexopos/woo_product', 'ApiNexoSystem@syncDownSingleProduct' );
$Routes->post( 'nexopos/woo_products', 'ApiNexoSystem@syncDownProducts' );
$Routes->post( 'nexopos/woo_category', 'ApiNexoSystem@syncDownSingleCategory' );
$Routes->post( 'nexopos/woo_categories', 'ApiWooCommerce@syncDownCategories' );
$Routes->post( 'nexopos/woo_order', 'ApiWooCommerce@syncDownSingleOrder' );
$Routes->post( 'nexopos/woo_customers', 'ApiWooCommerce@syncDownCustomers' );
$Routes->delete( 'nexopos/woo_product', 'ApiNexoSystem@syncDownDeleteSingleProduct' );

/**
 * Sync Up
 */
$Routes->post( 'nexopos/order', 'ApiNexoSystem@syncUpSingleOrder' );
$Routes->post( 'nexopos/product', 'ApiNexoSystem@syncUpSingleProduct' );
$Routes->post( 'nexopos/category', 'ApiNexoSystem@syncUpSingleCategory' );

$Routes->post( 'nexopos/import/customers', 'ApiNexoCustomers@importCSV' );
$Routes->post( 'nexopos/delete_history', 'ApiNexoSystem@deleteSelectedHistory' );

/**
 * POS V3 routes
 */
$Routes->get( 'nexopos/pos-v3/categories/{id?}', 'ApiPosV3Controller@getCategoriesAndProducts' );

/**
 * Reward System
 * @since 3.14.6
 */
$Routes->post( 'nexopos/rewards-system', 'ApiRewardSystemController@postReward' );
$Routes->post( 'nexopos/rewards-system/bulk-delete', 'ApiRewardSystemController@bulkDelete' );
$Routes->put( 'nexopos/rewards-system/{id}', 'ApiRewardSystemController@editReward' );
$Routes->get( 'nexopos/rewards-system/{page?}', 'ApiRewardSystemController@getPaginated' );
$Routes->delete( 'nexopos/rewards-system/{id}', 'ApiRewardSystemController@deleteReward' );
$Routes->delete( 'nexopos/rewards-system/rule/{id}', 'ApiRewardSystemController@deleteRule' );