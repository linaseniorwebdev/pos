<script>
$( document ).ready( function() {
    const TransfertHistoryVue   =   new Vue({
        el : '.content-header',
        mounted() {
            this.mount();
            $( document ).ajaxComplete( ( event, xhr, settings ) => {
                if ( settings.url.match( /ajax_list/ ) !== null ) {
                    this.mount();
                }
            })
        },
        methods: {
            showExhaustedStock( request ) {
                swal({
                    title: '<?php echo _s( 'Attention Needed', 'stock-manager' );?>',
                    html: `
                    <div id="list-item-vue">
                        <p>${request.message}</p>
                        <ul class="list-group" style="height: 300px;overflow-y: auto;">
                            <li v-for="item in request.items" class="list-group-item">{{ item.full.DESIGN }} &mdash; {{ item.full.QUANTITE_RESTANTE - item.item.QUANTITY }}</li>
                        </ul>
                    </div>
                    `,
                    showCancelButton: true,
                    preConfirm: ( data ) => {
                        return new Promise( ( resolve, reject ) => {
                            resolve( this.proceedStockRequest( request ) );
                        });
                    },
                    onOpen: () => {
                        console.log( $( '#list-item-vue' ).length );
                        const ListItemVue   =   new Vue({
                            el: '#list-item-vue',
                            mounted() {
                                console.log( this );
                            },
                            data: { request }
                        });
                    }
                }).then( result => {

                })
            },
            
            proceedStockRequest( request ) {
                swal.close();
                return swal({
                    title : '<?php echo __( 'Transfering Stock', 'stock-manager' );?>',
                    onOpen: () => {
                        swal.showLoading();
                        HttpRequest.post( '<?php echo site_url([ 'api', 'nexopos' ]);?>/proceed_request/<?php echo store_get_param( '?' );?>', {
                            transfert_id: request.transfert_id
                        }).then( result => {
                            if ( result.data.status === 'failed' ) {
                                swal.showValidationError( result.data.message );
                            } else {
                                NexoAPI.Toast()( result.data.message );
                                swal.close();
                            }
                        }).catch( error => {
                            NexoAPI.Toast()( '<?php echo __( 'An error occured during the transfert', 'stock-manager' );?>' );
                        })
                    },
                    showCancelButton: false,
                    showCloseButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                }).then( result => {
                    console.log( result );
                })
            },
            mount() {
                [{
                    title: '<?php echo __( 'Accept The Stock Transfer', 'stock-manager' );?>',
                    slug : 'approve_transfert'
                },{
                    title: '<?php echo __( 'Reject The Stock Transfer', 'stock-manager' );?>',
                    slug : 'reject_transfert'
                },{
                    title: '<?php echo __( 'Cancel The Stock Transfer', 'stock-manager' );?>',
                    slug : 'cancel_transfert'
                },{
                    title: '<?php echo __( 'Approve A Stock Request', 'stock-manager' );?>',
                    slug : 'approve_request'
                }].forEach( option => {
                    $( '.' + option.slug + '_btn' ).bind( 'click', ( e ) => {
                        if( option.slug === 'approve_request' ) {
                            swal({
                                title: '<?php echo __( 'Would you like to proceed ?', 'stock-manager' );?>',
                                text: `<?php echo _s( 'You\'re about to approve a stock transfert request. 
                                Before doing that, a verification will be made over your current stock.', 'stock-manager' );?>`,
                                showCancelButton: true,
                                confirmButtonText: `<?php echo _s( 'Proceed', 'stock-manager' );?>`,
                                preConfirm: ( note ) => {
                                    return new Promise( ( resolve, reject ) => {
                                        HttpRequest.post( '<?php echo site_url([ 'api', 'nexopos' ]);?>/verification/<?php echo store_get_param( '?' );?>', {
                                            description: $( '[name="transfert_description"]' ).val(),
                                            transfert_id: $( e.currentTarget ).data( 'item-id' )
                                        }).then( result => {
                                            if ( result.data.status === 'failed' ) {
                                                swal.showValidationError( result.data.message );
                                            } else {
                                                return resolve( result.data );
                                            }
                                        }).catch( error => {
                                            NexoAPI.Toast()( '<?php echo __( 'An error occured during the transaction', 'stock-manager' );?>' );
                                        })
                                    })
                                }
                            }).then( result => {
                                if ( result.value.status === 'success' ) {
                                    this.proceedStockRequest( result.value );
                                } else if ( result.value.status === 'info' ) {
                                    this.showExhaustedStock( result.value );
                                }
                            })
                        } else {
                            swal({
                                title: option.title,
                                html: `
                                <p><?php echo __( 'Please consider providing a reason of the current action. This might help the recipient to understand the action.', 'stock-manager' );?></p>
                                <div class="form-group has-success has-feedback">
                                    <label class="control-label" for="inputSuccess2"><?php echo __( 'Description', 'stock-manager' );?></label>
                                    <textarea style="height:200px;" name="transfert_description" class="form-control" id="inputSuccess2" aria-describedby="inputSuccess2Status"></textarea>
                                </div>
                                `,
                                showCancelButton: true,
                                preConfirm: ( note ) => {
                                    return new Promise( ( resolve, reject ) => {
                                        HttpRequest.post( '<?php echo site_url([ 'api', 'nexopos' ]);?>/' + option.slug + '/<?php echo store_get_param( '?' );?>', {
                                            description: $( '[name="transfert_description"]' ).val(),
                                            transfert_id: $( e.currentTarget ).data( 'item-id' )
                                        }).then( result => {
                                            if ( result.data.status === 'failed' ) {
                                                swal.showValidationError( result.data.message );
                                            } else {
                                                return resolve( result.data );
                                            }
                                        }).catch( error => {
                                            NexoAPI.Toast()( '<?php echo __( 'An error occured during the transaction', 'stock-manager' );?>' );
                                        })
                                    })
                                }
                            }).then( response => {
                                NexoAPI.Toast()( response.value.message );
                                $( '#ajax_refresh_and_loading' ).trigger( 'click' );
                            });
                        }
                        return false;
                    });
                });
            }
        }
    })
})
</script>