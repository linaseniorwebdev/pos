<?php
/**
 * update permission for users
 * @since 3.13.15
 */
$this->load->model( 'Nexo_Stores' );

$stores         =   $this->Nexo_Stores->get();

array_unshift( $stores, [
    'ID'        =>  0
]);

foreach( $stores as $store ) {
    $store_prefix       =   $store[ 'ID' ] == 0 ? '' : 'store_' . $store[ 'ID' ] . '_';

}
foreach([
    'nexo.enter.stores',
] as $permission ) {
    foreach([ 'master', 'store.manager', 'sub-store.manager' ] as $role ) {
        $this->auth->allow_group( $role, $permission );
    }
}