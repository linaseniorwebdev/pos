<?php
global $Routes;

$Routes->get( 'nexo/templates/customers-main', 'NexoTemplateController@customers_main' );
$Routes->get( 'nexo/templates/customers-form', 'NexoTemplateController@customers_form' );
$Routes->get( 'nexo/templates/shippings', 'NexoTemplateController@shippings' );
$Routes->get( 'nexo/templates/orders/{name}', 'NexoTemplateController@orders', ['defaultParameterRegex' => '[\w|.|-]+']);
$Routes->get( 'nexo/template/{name}', 'NexoTemplateController@load' );

/**
 * Stores Routes
 */
$Routes->get( 'nexo/stores', 'NexoStoreController@lists' );
$Routes->get( 'nexo/stores/success/{id}', 'NexoStoreController@lists' );
$Routes->get( 'nexo/stores/edit/{id}', 'NexoStoreController@lists' );
$Routes->get( 'nexo/stores/add', 'NexoStoreController@add' );
$Routes->get( 'nexo/stores/all', 'NexoStoreController@all' );
$Routes->get( 'nexo/stores/delete/{id}', 'NexoStoreController@lists' );
$Routes->get( 'nexo/stores/delete_file/{field}/{filename}', 'NexoStoreController@lists', ['defaultParameterRegex' => '[\w|.|-]+']);
$Routes->post( 'nexo/stores/ajax_list', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/upload_file/{field}', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/update_validation/{id}', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/insert_validation', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/insert', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/update/{id}', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/ajax_list_info', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/export', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/print', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/export_csv', 'NexoStoreController@lists' );
$Routes->post( 'nexo/stores/bulk_delete', 'NexoStoreController@lists' );

$Routes->match([ 'get', 'post' ], 'stores/{id}/{any?}', 'NexoStoreController@stores' )->where([ 
    'id'      => '[0-9]+', 
    'any'     =>   '.*' 
]);

if ( ! is_multistore() ) {
    include( dirname( __FILE__ ) . '/routes-array.php' );
}