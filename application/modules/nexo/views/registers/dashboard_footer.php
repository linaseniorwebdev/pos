<script type="text/javascript">
    "use strict";

    $( document ).ready(function(e) {
        $( '.open_register' ).bind( 'click', function(){
            const $this     =   $( this );
            HttpRequest.get( '<?php echo site_url( array( 'api', 'nexopos', 'registers', store_get_param('?') ) );?>' ).then( result => {
                const registers     =   result.data;
                const openByUser    =   registers.filter( register => register.USED_BY === '<?php echo User::id();?>' && $( this ).data( 'item-id' ) !== parseInt( register.ID ) && register.STATUS === 'opened' );
                const targeted      =   registers.filter( register => $( this ).data( 'item-id' ) === parseInt( register.ID ) )[0];

                if ( openByUser.length > 0 ) {
                    return NexoAPI.Notify().warning(
                        '<?php echo _s( 'Attention', 'nexo' );?>',
                        `<?php echo __( 'Vous ne pouvez plus ouvrir de nouvelle caisse enregistreuse, car vous avez déjà ouvert une caisse. 
                        Fermez cette caisse et essayez à nouveau', 'nexo' );?>`
                    );
                }

                if( targeted.STATUS == 'opened' ) {
                    if( targeted.USED_BY != '<?php echo User::id();?>' && <?php echo ( User::in_group([ 'master', 'store.manager' ]) ) ? 'false == true' : 'true == true';?> ) {
                        // Display confirm box to logout current user and login
                        bootbox.alert( '<?php echo _s( 'Impossible d\'accéder à une caisse en cours d\'utilisation. Si le problème persiste, contactez l\'administrateur.', 'nexo' );?>' );
                    } else {
                        bootbox.alert( '<?php echo _s( 'Vous allez être redirigé vers la caisse...', 'nexo' );?>' );
                        // Document Location
                    }
                } else if( targeted.STATUS == 'locked' ) {
                    bootbox.alert( '<?php echo _s( 'Impossible d\'accéder à une caisse verrouillée. Si le problème persiste, contactez l\'administrateur.', 'nexo' );?>' );

                } else if( targeted.STATUS == 'closed' ) {
                    var dom		=	'<h3 class="modal-title"><?php echo _s( 'Ouverture de la caisse', 'nexo' );?></h3><hr style="margin:10px 0px;">';

                        dom		+=	'<p><?php echo tendoo_info( sprintf( _s( '%s, vous vous préparez à ouvrir une caisse. Veuillez spécifier le montant initiale de la caisse', 'nexo' ), User::pseudo() ) );?></p>' +
                                    `
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic-addon1"><?php echo _s( 'Solde d\'ouverture de la caisse', 'nexo' );?></span>
                                            <input type="text" class="form-control open_balance" placeholder="<?php echo _s( 'Montant', 'nexo' );?>" aria-describedby="basic-addon1">
                                        </div>  
                                    </div>                                
                                    <div class="form-group">
                                        <label for="textarea"><?php echo __( 'Remarques', 'nexo' );?></label>
                                        <textarea name="" id="textarea" class="form-control note" rows="3" required="required"></textarea>
                                    </div>
                                    `;

                    bootbox.confirm( dom, function( action ) {
                        if( action ) {
                            $.ajax( '<?php echo site_url( array( 'rest', 'nexo', 'open_register' ) );?>/' + $this.data( 'item-id' ) + '?<?php echo store_get_param( null );?>', {
                                dataType	:	'json',
                                type		:	'POST',
                                data		:	_.object( [ 'date', 'balance', 'used_by', 'note' ], [ '<?php echo date_now();?>', $( '.open_balance' ).val(), '<?php echo User::id();?>', $( '.note' ).val() ]),
                                success: function( data ){
                                    bootbox.alert( '<?php echo _s( 'La caisse a été ouverte. Veuillez patientez...', 'nexo' );?>' );
                                    document.location	=	'<?php echo dashboard_url([ 'use', 'register' ]);?>/' + $this.data( 'item-id');
                                }
                            });
                        }
                    });

                    // Set custom width
                    $( '.modal-title' ).closest( '.modal-dialog' ).css({
                        'width'		:	'50%'
                    })
                }
            }).catch( ( err ) => {
                bootbox.alert( '<?php echo _s( 'Une erreur s\'est produite durant l\'ouverture de la caisse.', 'nexo' );?>' );
            })

            return false;
        });

        $( '.close_register' ).bind( 'click', function(){
            var $this	=	$( this );
            $.ajax( '<?php echo site_url( array( 'rest', 'nexo', 'register_status' ) );?>/' + $( this ).data( 'item-id' ) + '?<?php echo store_get_param( null );?>', {
                success		:	function( data ){
                    // Somebody is logged in
                    if( data[0].STATUS == 'opened' ) {

                        if( data[0].USED_BY != '<?php echo User::id();?>' && <?php echo ( User::in_group([ 'master', 'store.manager' ]) ) ? 'false == true' : 'true == true';?> ) {
                            bootbox.alert( '<?php echo _s( 'Vous ne pouvez pas fermer cette caisse. Si le problème persiste, contactez l\'administrateur.', 'nexo' );?>' );
                            return;
                        }

                        var dom		=	'<h3 class="modal-title"><?php echo _s( 'Fermeture de la caisse', 'nexo' );?></h3><hr style="margin:10px 0px;">';

                            dom		+=	'<p><?php echo tendoo_info( sprintf( _s( '%s, vous vous préparez à fermer une caisse. Veuillez spécifier le montant finale de la caisse', 'nexo' ), User::pseudo() ) );?></p>' +
                                        `<div class="form-group">
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><?php echo _s( 'Solde d\'ouverture de la caisse', 'nexo' );?></span>
                                                <input type="text" class="form-control open_balance" placeholder="<?php echo _s( 'Montant', 'nexo' );?>" aria-describedby="basic-addon1">
                                            </div>  
                                        </div> 
                                        <div class="form-group">
                                            <label for="textarea"><?php echo __( 'Remarques', 'nexo' );?></label>
                                            <textarea name="" id="textarea" class="form-control note" rows="3" required="required"></textarea>
                                        </div>
                                        `

                        bootbox.confirm( dom, function( action ) {
                            if( action == true ) {
                                $.ajax( '<?php echo site_url( array( 'rest', 'nexo', 'close_register' ) );?>/' + $this.data( 'item-id' ) + '?<?php echo store_get_param( null );?>', {
                                    dataType	:	'json',
                                    type		:	'POST',
                                    data		:	_.object( [ 'date', 'balance', 'used_by', 'note' ], [ '<?php echo date_now();?>', $( '.open_balance' ).val(), '<?php echo User::id();?>', $( '.note' ).val() ]),
                                    success: function( data ){
                                        bootbox.alert( '<?php echo _s( 'La caisse a été fermée. Veuillez patientez...', 'nexo' );?>' );
                                        document.location	=	'<?php echo current_url();?>';
                                    }
                                });
                            }
                        });

                        // Set custom width
                        $( '.modal-title' ).closest( '.modal-dialog' ).css({
                            'width'		:	'50%'
                        })

                    } else if( data[0].STATUS == 'locked' ) {

                        bootbox.alert( '<?php echo _s( 'Impossible de fermer une caisse verrouillée. Si le problème persiste, contactez l\'administrateur.', 'nexo' );?>' );

                    } else if( data[0].STATUS == 'closed' ) {

                        bootbox.alert( '<?php echo _s( 'Cette caisse est déjà fermée.', 'nexo' );?>' );

                    }

                },
                dataType	:	"json",
                error		:	function(){
                    bootbox.alert( '<?php echo _s( 'Une erreur s\'est produite durant l\'ouverture de la caisse.', 'nexo' );?>' );
                }
            })

            return false;
        });

        $( '.register_history' ).bind( 'click', function(){

            $.ajax( '<?php echo site_url( array( 'rest', 'nexo', 'register_activities' ) );?>/' + $( this ).data( 'item-id' ) + '?<?php echo store_get_param( null );?>', {
                success	:	function( data ){
                    var dom			=	'<h4><?php echo _s( 'Historique de la caisse', 'nexo' );?></h4>';
                    var lignes		=	'';

                    if( ! _.isEmpty( data ) ) {
                        _.each( data, function( val, key ) {

                            let type    =   '';
                            switch( val.TYPE ) {
                                case 'opening' : type = '<?php echo _s( 'Ouvrir', 'nexo' );?>'; break;
                                case 'closing' : type = '<?php echo _s( 'Fermer', 'nexo' );?>'; break;
                                case 'idle_starts' : type = '<?php echo _s( 'Début d\'inactivité ', 'nexo' );?>'; break;
                                case 'idle_ends' : type = '<?php echo _s( 'Fin d\'inactivité ', 'nexo' );?>'; break;
                            }

                            lignes 	+=
                            '<tr>' +
                                '<td>' + val.name + '</td>' +
                                '<td>' + type + '</td>' +
                                '<td>' + NexoAPI.DisplayMoney( val.BALANCE ) + '</td>' +
                                '<td>' + ( val.NOTE == '' ? '<?php echo __( 'Aucune note.', 'nexo' );?>' : val.NOTE ) + '</td>' +
                                '<td>' + val.DATE_CREATION + '</td>' +
                            '</tr>';
                        });
                    } else {
                        lignes	+=	'<tr><td colspan="5"><?php echo _s( 'Aucune historique pour cette caisse', 'nexo' );?></td></tr>';
                    }

                        dom			+=
                    '<table class="table table-bordered table-striped">' +
                        '<thead>' +
                            '<tr>' +
                                '<td><?php echo _s( 'Auteur', 'nexo' );?></td>' +
                                '<td><?php echo _s( 'Action', 'nexo' );?></td>' +
                                '<td><?php echo _s( 'Montant', 'nexo' );?></td>' +
                                '<td><?php echo _s( 'Remarques', 'nexo' );?></td>' +
                                '<td><?php echo _e( 'Date', 'nexo' );?></td>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' +
                            lignes
                        '</tbody>' +
                    '</table>';

                    bootbox.alert( dom, function( action ){

                    });

                    // Set custom width
                    $( '.modal-title' ).closest( '.modal-dialog' ).css({
                        'width'		:	'80%'
                    })
                },
                dataType	:	'json',

            });

            return false;
        });
    });
</script>