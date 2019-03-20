<script>
    tendooApp.controller( 'nexoNotificationCTRL', [
        '$scope', 
        '$http',
        '$compile',
        function( 
            $scope, 
            $http,
            $compile
        ) {
            $scope.notices      =   [];

            $scope.getNotices   =   function() {
                $http.get( '<?php echo site_url([ 'rest', 'nexo', 'notices', User::id(), store_get_param( '?' ) ]);?>', {
                    headers	:	{
                    '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
                    }
                }).then( function( returned ) {
                    $scope.notices  =   returned.data;
                })
            }

            $scope.deleteAll    =   function( notices ) {
                swal({
                    title: '<?php echo __( 'Souhaitez-vous tout supprimer ?', 'nexo' );?>',
                    text: `<?php echo _s( 'Vous êtes sur le point de supprimer toutes vos notifications. Cette action est irreversible. Souhaitez-vous continuer ?', 'nexo' );?>`,
                    showCancelButton: true
                }).then( result => {
                    if ( result.value ) {
                        let ids     =   notices.map( notice => notice.ID );
                        $http.post( '<?php echo site_url([ 'rest', 'nexo', 'delete_notices', store_get_param( '?' ) ]);?>', {
                            ids
                        }, {
                            headers	:	{
                                '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
                            }
                        }).then( function( returned ) {
                            console.log( returned.data );
                            $scope.notices  =   [];
                        });
                    }
                })
            }   

            $scope.delete       =   function( id ) {
                $http.delete( '<?php echo site_url([ 'rest', 'nexo', 'notices' ]);?>/' + id + '<?php echo store_get_param( '?' );?>', {
                    headers	:	{
                        '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
                    }
                }).then( function( returned ) {
                    // delete the notice
                    var noticeToDelete      =   null;
                    _.each( $scope.notices, function( notice, index ) {
                        if( notice.ID == id ) {
                            noticeToDelete      =   index;
                        }
                    });

                    $scope.notices.splice( noticeToDelete, 1 );
                });
            }
            
            $scope.toggleMenu       =   function( action ) {
                if( ! $( '.notifications-menu' ).hasClass( 'open' ) && action != 'close' ) {
                    return $( '.notifications-menu' ).addClass( 'open' );
                }
                return $( '.notifications-menu' ).removeClass( 'open' );
            }

            $scope.menu             =   function( action ) {
                if( action == 'open' ) {
                    return $( '.notifications-menu' ).addClass( 'open' );
                }
                return $( '.notifications-menu' ).removeClass( 'open' );
            }

            $scope.getNotices();

            $( document ).bind( 'click', function( e ){
                if( $( e.srcElement ).closest( '.nexo-notifications' ).length == 0 ) {
                    $scope.menu( 'close' );
                }
            });
        }
    ])
</script>