let salesCoreModal;
class orderBindjQueryEvent {
    constructor() {
        $( document ).ajaxComplete( () => {
            this.bind();
        });
        // initial bind
        this.bind();
    }

    /**
     * bind jquery Events
     * @return void
     */
    bind() {
        $( 'a.order-details' ).unbind( 'click' );
        $( 'a.order-details' ).bind( 'click', function() {
            salesCoreModal     =   new SalesCoreModal( $( this ).data( 'order-id' ) );
            // if ( $( this ).attr( 'scm-is-bound' ) === undefined ) {
            //     $( this ).attr( 'scm-is-bound', 'true' );
            // }
        });
    }
}
$( document ).ready( function() {
    new orderBindjQueryEvent;
    // new SalesCoreModal( 1 );
});