<?php
if ( is_multistore() && multistore_enabled() ) {
    global $StoreRoutes;
    $Routes     =   $StoreRoutes;

    $Routes->get( '', 'NexoDashboardController@index' );
} else {
    global $Routes;
}

$Routes->get( 'nexo/about', 'NexoAboutController@index' );

/**
 * Customers Routes
 */
$Routes->get( 'nexo/customers/add', 'NexoCustomersController@add' );
$Routes->get( 'nexo/customers/edit/{id}', 'NexoCustomersController@edit' );
$Routes->get( 'nexo/customers/reset-coupon-count/{id}', 'NexoCustomersController@resetCounter' );
$Routes->match([ 'get', 'post' ], 'nexo/customers/coupons/{customer_id}/{params?}/{id?}', 'NexoCustomersController@customerCoupons' );

$Routes->get( 'nexo/rewards-system', 'NexoRewardSystemController@listRewards' );
$Routes->get( 'nexo/rewards-system/create', 'NexoRewardSystemController@create' );
$Routes->get( 'nexo/rewards-system/edit/{id}', 'NexoRewardSystemController@edit' );
$Routes->get( 'nexo/rewards-system/delete/{id}', 'NexoRewardSystemController@delete' );

$Routes->match([ 'get', 'post' ], 'nexo/customers/{param?}/{id?}', 'NexoCustomersController@lists' );

$Routes->get( 'nexo/items/import/', 'NexoImportController@items' );
$Routes->get( 'nexo/items/history/{barcode}', 'NexoItemsController@history' );
$Routes->get( 'nexo/items/stock-adjustment/', 'NexoItemsController@stock_supply' );
$Routes->get( 'nexo/items', 'NexoItemsController@lists' );
$Routes->get( 'nexo/items/add', 'NexoItemsController@lists' );
$Routes->get( 'nexo/items/edit/{id}', 'NexoItemsController@editItem' );
$Routes->get( 'nexo/items/delete/{id}', 'NexoItemsController@lists' );
$Routes->get( 'nexo/items/success/{id}', 'NexoItemsController@lists' );
$Routes->get( 'nexo/items/delete_file/{id}/{filename}', 'NexoItemsController@lists', [ 'defaultParameterRegex' => '[\w|.|-]+' ]);
$Routes->post( 'nexo/items/export', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/print', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/export_csv', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/ajax_list', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/ajax_list_info', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/insert', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/insert_validation', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/bulk_delete', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/update_validation/{id}', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/update/{id}', 'NexoItemsController@lists' );
$Routes->post( 'nexo/items/upload_file/{id}', 'NexoItemsController@lists' );
$Routes->match([ 'get', 'post' ], 'nexo/items/supply-history/{barcode}/{action?}/{id?}', 'NexoItemsController@supply', [ 'defaultParameterRegex' => '[\w|.|-]+' ]);
// $Routes->match([ 'get', 'post' ], 'nexo/items/{action?}/{id?}/{filename?}', 'NexoItemsController@lists', [ 'defaultParameterRegex' => '[\w|.|-]+' ] );
// $Routes->get( 'nexo/items/supply-history/{barcode}', 'NexoItemsController@supply' );


$Routes->get( 'nexo/grouped-items/add', 'NexoItemsController@grouped_items' );
$Routes->get( 'nexo/grouped-items/edit/{id}', 'NexoItemsController@grouped_items' );

/**
 * Settings Routes
 */
$Routes->get( 'nexo/settings/home', 'NexoSettingsController@home' );
$Routes->get( 'nexo/settings/checkout', 'NexoSettingsController@checkout' );
$Routes->get( 'nexo/settings/items', 'NexoSettingsController@items' );
$Routes->get( 'nexo/settings/customers', 'NexoSettingsController@customers' );
$Routes->get( 'nexo/settings/email', 'NexoSettingsController@email' );
$Routes->get( 'nexo/settings/payments-gateways', 'NexoSettingsController@payments' );
$Routes->get( 'nexo/settings/reset', 'NexoSettingsController@reset' );
$Routes->get( 'nexo/settings/reports', 'NexoSettingsController@reports' );
$Routes->get( 'nexo/settings/expenses', 'NexoSettingsController@expenses' );
$Routes->get( 'nexo/settings/invoices', 'NexoSettingsController@invoices' );
$Routes->get( 'nexo/settings/keyboard', 'NexoSettingsController@keyboard' );
$Routes->get( 'nexo/settings/providers', 'NexoSettingsController@providers' );
$Routes->get( 'nexo/settings/orders', 'NexoSettingsController@orders' );
$Routes->get( 'nexo/settings/stores', 'NexoSettingsController@stores' );
$Routes->get( 'nexo/settings/stripe', 'NexoSettingsController@stripe' );
$Routes->get( 'nexo/settings', 'NexoSettingsController@home' );

/**
 * Supplies Routes
 */
$Routes->get( 'nexo/supplies/', 'NexoSuppliesController@lists' );
$Routes->get( 'nexo/supplies/stock', 'NexoItemsController@add_supply' );
$Routes->get( 'nexo/supplies/add', 'NexoSuppliesController@lists' );
$Routes->get( 'nexo/supplies/edit/{id}', 'NexoSuppliesController@lists' );
$Routes->get( 'nexo/supplies/delete/{id}', 'NexoSuppliesController@lists' );
$Routes->get( 'nexo/supplies/success/{id}', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/update_validation/{shipping_id}', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/update/{shipping_id}', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/insert_validation', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/insert', 'NexoSuppliesController@lists' );
$Routes->get( 'nexo/supplies/invoice/{shipping_id}', 'NexoSuppliesController@delivery_invoice' );
$Routes->get( 'nexo/supplies/labels/{shipping_id}', 'NexoPrintController@shipping_item_codebar' );
$Routes->get( 'nexo/supplies/detailed-worth/{shipping_id}', 'NexoSuppliesController@detailed_worth' );
$Routes->get( 'nexo/supplies/items/{shipping_id}', 'NexoSuppliesController@delivery_items' );
$Routes->get( 'nexo/supplies/items/{shipping_id}/edit/{item_id}', 'NexoSuppliesController@delivery_items' );
$Routes->get( 'nexo/supplies/items/{shipping_id}/success/{item_id}', 'NexoSuppliesController@delivery_items' );
$Routes->post( 'nexo/supplies/items/{shipping_id}/ajax_list_info', 'NexoSuppliesController@delivery_items' );
$Routes->post( 'nexo/supplies/items/{shipping_id}/ajax_list', 'NexoSuppliesController@delivery_items' );
$Routes->post( 'nexo/supplies/items/{shipping_id}/update_validation/{item_id}', 'NexoSuppliesController@delivery_items' );
$Routes->post( 'nexo/supplies/items/{shipping_id}/update/{item_id}', 'NexoSuppliesController@delivery_items' );
$Routes->post( 'nexo/supplies/ajax_list', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/insert_validation', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/ajax_list_info', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/export', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/print', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/export_csv', 'NexoSuppliesController@lists' );
$Routes->post( 'nexo/supplies/bulk_delete', 'NexoSuppliesController@lists' );
$Routes->get( 'nexo/supplies/refresh/{shipping_id}', 'NexoSuppliesController@refresh' );

/**
 * Providers Routes
 */
$Routes->get( 'nexo/providers', 'NexoProvidersController@lists' );
$Routes->get( 'nexo/providers/add', 'NexoProvidersController@add' );
$Routes->get( 'nexo/providers_history/{provider_id}', 'NexoProvidersController@history' );
$Routes->get( 'nexo/providers/edit/{id}', 'NexoProvidersController@lists' );
$Routes->get( 'nexo/providers/delete/{id}', 'NexoProvidersController@lists' );
$Routes->get( 'nexo/providers/success/{id}', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/export', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/print', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/insert', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/insert_validation', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/export_csv', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/ajax_list_info', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/ajax_list', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/bulk_delete', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/update_validation/{id}', 'NexoProvidersController@lists' );
$Routes->post( 'nexo/providers/update/{id}', 'NexoProvidersController@lists' );

/**
 * Orders Routes
 */
$Routes->get( 'nexo/orders', 'NexoOrdersController@lists' );
$Routes->get( 'nexo/orders/receipt/{order_id}/{option?}', 'NexoPrintController@order_receipt' );
$Routes->get( 'nexo/orders/invoice/{order_id}', 'NexoPrintController@invoice' );
$Routes->get( 'nexo/orders/delete/{order_id}', 'NexoOrdersController@deleteOrder' );
$Routes->get( 'nexo/orders/refund-receipt/{order_id}', 'NexoPrintController@refundReceipt' );
$Routes->post( 'nexo/orders/export', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/print', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/export_csv', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/ajax_list_info', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/ajax_list', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/bulk_delete', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/update_validation/{id}', 'NexoOrdersController@lists' );
$Routes->post( 'nexo/orders/update/{id}', 'NexoOrdersController@lists' );

/**
 * 
 */
$Routes->get( 'nexo/refunds/invoice/{order_id}', 'NexoPrintController@order_refund' );
$Routes->get( 'nexo/pos', 'NexoRegistersController@__use' );
$Routes->get( 'nexo/use/register/{register_id}/{order_id?}', 'NexoRegistersController@__use' );
$Routes->get( 'nexo/pos-v3', 'NexoRegistersController@posV3' );
$Routes->get( 'nexo/close/register/{register_id}', 'NexoRegistersController@__use' );
$Routes->get( 'nexo/open/register/{register_id}', 'NexoRegistersController@__use' );
$Routes->get( 'nexo/register-history/{register_id}', 'NexoRegistersController@__use' );
$Routes->get( 'nexo/reports/monthly-sales', 'NexoReportsController@journalier' );
$Routes->get( 'nexo/reports/save-daily-log/{date?}', 'NexoReportsController@saveDailyLog' );
$Routes->get( 'nexo/reports/json-daily-log', 'NexoReportsController@getDailyLog' );
$Routes->get( 'nexo/reset-barcode', 'NexoItemsController@reset_barcode', ['defaultParameterRegex' => '[\w|.|-]+']);
$Routes->get( 'nexo/generate-barcode/{barcode}/{type?}', 'NexoItemsController@generate_barcode' );
$Routes->get( 'nexo/resample-barcode/{id}/{barcode}/{type?}', 'NexoItemsController@resample_barcode', ['defaultParameterRegex' => '[\w|.|-]+']);

$Routes->match([ 'get', 'post' ], 'nexo/registers/{action?}/{id?}', 'NexoRegistersController@lists' );
$Routes->match([ 'get', 'post' ], 'nexo/coupons/{action?}/{id?}', 'NexoCouponsController@lists' );
$Routes->match([ 'get', 'post' ], 'nexo/taxes/{param?}/{id?}', 'NexoTaxesController@index' );
$Routes->match([ 'get', 'post' ], 'nexo/suppliers/{action?}/{id?}', 'NexoProvidersController@lists' );
$Routes->match([ 'get', 'post' ], 'nexo/groups-customers/{param?}/{id?}', 'NexoCustomersController@groups', ['defaultParameterRegex' => '[\w|.|-]+']);
$Routes->match([ 'get', 'post' ], 'nexo/categories/{param?}/{id?}/{filename?}', 'NexoCategoriesController@lists', ['defaultParameterRegex' => '[\w|.|-]+']);

$Routes->get( 'nexo/import/customers', 'NexoCustomersController@showImport', ['defaultParameterRegex' => '[\w|.|-]+']);
$Routes->post( 'nexo/customers/{param}', 'NexoCustomersController@lists' );
$Routes->post( 'nexo/reset', 'NexoResetController@index', ['defaultParameterRegex' => '[\w|.|-]+']);
$Routes->post( 'nexo/upload_images', 'NexoItemsController@uploadImages' );
$Routes->get( 'nexo/local-print/{order_id}', 'NexoPrintController@printResult' );
$Routes->get( 'nexo/logs-history', 'NexoLogHistoryController@logHistory' );
$Routes->get( 'nexo/test-print', function() {
    $textString     =    
    '<?xml version="1.0" encoding="UTF-8"?>
    <document>
        <text>
            <text-line>' .
    toEscTable([
        [
            [
                'title' =>  'item',
                'width' =>  20,
            ], [
                'title' =>  'Quantity',
                'width' =>  'auto',
            ], [
                'title' =>  'Price',
                'width' =>  'auto',
            ]
        ], 
        [ 'ok', '110', '20 $' ],
        [ '->modifier 20$', '', '' ],
        // [ 'produ avec une ligne', '110', '20 $' ],
        // [ 'produ avec azd adz adza azdazd azdazdad azd ligne', '110', '20 $' ],
    ], [
        'maxLetter'     =>  '85',
        'bodyLines'     =>  false,
        'fillWith'      =>  ' '
    ])
        . '</text-line>
        </text>
        <paper-cut></paper-cut>
        </document>';

    $my_img = imagecreate( 600, 700 );
    $background = imagecolorallocate( $my_img, 0, 0, 255 );
    $text_colour = imagecolorallocate( $my_img, 255, 255, 0 );
    $line_colour = imagecolorallocate( $my_img, 128, 255, 0 );
    imagestring( $my_img, 4, 30, 25, $textString, $text_colour );
    imagesetthickness ( $my_img, 5 );
    imageline( $my_img, 30, 45, 165, 45, $line_colour );
    
    header( "Content-type: image/png" );
    imagepng( $my_img );
    imagecolordeallocate( $line_color );
    imagecolordeallocate( $text_color );
    imagecolordeallocate( $background );
    imagedestroy( $my_img );
    
    return $textString;
});