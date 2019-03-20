<script type="text/javascript">
tendooApp.controller( 'posTabs', [ '$scope', function( $scope ){
    $scope.totalItems   =   0;
    $scope.adjust       =   function() {
        if ( layout.is( 'sm' ) || layout.is( 'xs' ) ) {
            setTimeout( () => {
                v2Checkout.adjustForMobile();
                NexoAPI.events.doAction( 'switch_to_mobile' );
            }, 300 );
        } else {
            v2Checkout.adjustForDesktop();
            NexoAPI.events.doAction( 'switch_to_desktop' );
        }

        if ( ( layout.is( 'sm' ) || layout.is( 'xs' ) || layout.is( 'md' ) ) ) {
            $scope.showPart( 'grid', $( '.meta-row .tab-cart li' ).eq(0) );
        } else if ( ( layout.is( 'lg' ) || layout.is( 'xg' ) ) ) {
            $scope.showPart( 'all', $( '.meta-row .tab-cart li' ).eq(0) );
        } 
    }
    $scope.showPart     =   function( tab, $element ) {
        
        if ( $element.currentTarget !== undefined ) {
            $element    =   $element.currentTarget;
        }

        if( tab === 'cart' ) {
            $scope.cartIsActive = 'active';
            $scope.gridIsActive = '';
            $( $element ).closest( '.gui-row-tag' ).find( '.meta-row' ).eq(1).hide();
            $( $element ).closest( '.gui-row-tag' ).find( '.meta-row' ).eq(0).show();
        } else if( tab === 'grid' ) {
            $scope.cartIsActive = '';
            $scope.gridIsActive = 'active';
            $( $element ).closest( '.gui-row-tag' ).find( '.meta-row' ).eq(1).show();
            $( $element ).closest( '.gui-row-tag' ).find( '.meta-row' ).eq(0).hide();
        } else {
            $scope.cartIsActive = '';
            $scope.gridIsActive = '';
            $( $element ).closest( '.gui-row-tag' ).find( '.meta-row' ).eq(1).show();
            $( $element ).closest( '.gui-row-tag' ).find( '.meta-row' ).eq(0).show();
        }
    }

    $scope.computeItemsNumbers  =   function() {
        $scope.totalItems       =   0;
        if ( v2Checkout.CartItems.length > 0 ) {
            $scope.totalItems   =   v2Checkout.CartItems
                .map( item => parseFloat( item.QTE_ADDED ) )
                .reduce( ( prev, next ) => prev + next );
        }
    }

    NexoAPI.events.addAction( 'add_to_cart', () => {
        $scope.computeItemsNumbers();
    });

    NexoAPI.events.addAction( 'open_order_on_pos', () => {
        $scope.computeItemsNumbers();
    });

    NexoAPI.events.addAction( 'toggle_fullscreen', () => {
        $scope.adjust();
    })

    $scope.adjust();

    $( window ).resize( function() {
        $scope.adjust();
    })
}])
</script>
