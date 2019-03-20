<?php
$this->Gui->add_item([
    'type'  =>  'dom',
    'content'   =>  $this->load->module_view( 'nexo', 'settings.tabs-header', compact( 'tabs', 'activeTab' ), true )
], @$namespace, 1);

$filePath   =   dirname( __FILE__ ) . DIRECTORY_SEPARATOR . ( @$subPath !== null ? $subPath . DIRECTORY_SEPARATOR : '' ) . $activeTab . '.php';
if ( is_file( $filePath ) ) {
    include_once( $filePath );
} else {
    echo '<br>';
    echo tendoo_error( sprintf( __( 'Impossible de retrouver le fichier "%s"', 'nexo' ), $filePath ) );
}
?>