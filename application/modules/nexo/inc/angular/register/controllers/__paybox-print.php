<?php 
/**
 * Load global values
 */
global  $Options, 
        $current_register, 
        $order;
if ( true == false ):?>
<script>
<?php endif;?>
$scope.canConfirmOrder 		=	true;
v2Checkout.paymentWindow.hideSplash();
v2Checkout.paymentWindow.close();

// fix issue when saving an order after having made a payment
$scope.cashPaidAmount			=	0;
$scope.paymentList				=	[];

if( _.isObject( returned ) ) {
    // Init Message Object
    var MessageObject	=	new Object;

    var data	=	NexoAPI.events.applyFilters( 'test_order_type', [ ( returned.order_type == 'nexo_order_comptant' ), returned ] );
    var test_order	=	data[0];

    if( test_order == true ) {

        <?php if ( store_option( 'nexo_enable_autoprint', 'yes' ) == 'yes'):?>

        if( NexoAPI.events.applyFilters( 'cart_enable_print', true ) ) {

            MessageObject.title	=	`<?php echo _s('Effectué', 'nexo');?>`;
            MessageObject.msg	=	`<?php echo _s('La commande est en cours d\'impression.', 'nexo');?>`;
            MessageObject.type	=	'success';

            <?php if ( store_option( 'nexo_print_gateway', 'normal_print' ) == 'normal_print' ):?>

            $( '#receipt-wrapper' ).remove();
            $( 'body' ).append( '<iframe id="receipt-wrapper" style="visibility:hidden;height:0px;width:0px;position:absolute;top:0;" src="<?php echo dashboard_url([ 'orders', 'receipt' ]);?>/' + returned.order_id + '?refresh=true&autoprint=true"></iframe>' );

            <?php elseif ( store_option( 'nexo_print_gateway', 'normal_print' ) == 'nexo_print_server' ):?>

                <?php
                /**
                 * let's check if a print should 
                 * convert the output as base64 
                 * or print using NPS XML Engine
                 */
                if( store_option( 'nps_print_base64', 'no' ) === 'no' ):
                ?>

                const registerOpen 	=	'?cash-register-action=open';
                
                <?php 
                /**
                 * Helps to have multiple copies from NPS
                 */
                for( $i = 0; $i < store_option( 'nexo_nps_print_copies', 1 ); $i++ ):?>

                $.ajax( `<?php echo dashboard_url([ 'local-print' ]);?>` + '/' + returned.order_id, {
                    success 	:	function( printResult ) {
                        $.ajax( '<?php echo store_option( 'nexo_print_server_url' );?>/api/print' + registerOpen, {
                            type  	:	'POST',
                            data 	:	{
                                'content' 	:	printResult,
                                'printer'	:	'<?php echo store_option( 'nexo_pos_printer' );?>'
                            },
                            dataType 	:	'json',
                            success 	:	function( result ) {
                                console.log( result );
                            }, 
                            error : ( error ) => {
                                console.log( error );
                            }
                        });
                    }
                });

                <?php endfor;?>

                <?php else:?>
                const url           =   `<?php echo store_option( 'nexo_print_server_url' );?>/api/print-base64`;
                const printerName   =   `<?php echo store_option( 'printer_takeway' );?>`;
                HttpRequest.get( `<?php echo dashboard_url([ 'orders', 'receipt' ]);?>/${returned.order_id}/base64?refresh=true&ignore_header=true` ).then( result => {
                    $( '#print-base64' ).remove();
                    $( 'body' ).append( `<div id="print-base64">${result.data}</div>` );

                    console.log( result.data );
        
                    html2canvas( document.getElementById( 'print-base64' ), {
                        logging: true
                    }).then(function(canvas) {
                        const image     =   canvas.toDataURL();
                        HttpRequest.post( url, {
                            'base64' 	:	image,
                            'printer'	:	printerName
                        }).then( result => {
                            $( '#print-base64' ).remove();
                        }).catch( error => {
                            $( '#print-base64' ).remove();
                        })
                    }).catch( err => console.log( err ) );
                });

                <?php endif;?>

            <?php elseif ( store_option( 'nexo_print_gateway' ) === 'register_nps' && store_option( 'nexo_enable_registers' ) === 'oui' ):?>

                <?php if ( empty( $current_register[ 'ASSIGNED_PRINTER' ] ) || ! filter_var( $current_register[ 'NPS_URL' ], FILTER_VALIDATE_URL ) ):?>
                    NexoAPI.Notify().warning(
                        `<?php echo __( 'Impossible d\'imprimer', 'nexo' );?>`,
                        `<?php echo __( 'Aucune imprimante n\'est assignée à la caisse enregistreuse ou l\'URL du serveur d\'impression est incorrecte.', 'nexo' );?>`
                    );
                <?php else:?>

                const registerOpen 	=	'?cash-register-action=open';

                <?php 
                /**
                 * Helps to have multiple copies from NPS
                 */
                for( $i = 0; $i < store_option( 'nexo_nps_print_copies', 1 ); $i++ ):?>

                $.ajax( '<?php echo dashboard_url([ 'local-print' ]);?>' + '/' + returned.order_id, {
                    success 	:	function( printResult ) {
                        $.ajax( '<?php echo $current_register[ 'NPS_URL' ];?>/api/print' + registerOpen, {
                            type  	:	'POST',
                            data 	:	{
                                'content' 	:	printResult,
                                'printer'	:	'<?php echo $current_register[ 'ASSIGNED_PRINTER' ];?>'
                            },
                            dataType 	:	'json',
                            success 	:	function( result ) {
                                NexoAPI.Toast()( `<?php echo __( 'Tâche d\'impression soumisse', 'nexo' );?>` );
                            },
                            error		:	() => {
                                NexoAPI.Notify().warning(
                                    `<?php echo __( 'Impossible d\'imprimer', 'nexo' );?>`,
                                    `<?php echo __( 'NexoPOS n\'a pas été en mesure de se connecter au serveur d\'impression ou ce dernier à retourner une erreur inattendue.', 'nexo' );?>`
                                );
                            }
                        });
                    }
                });

                <?php endfor;?>
                
                <?php endif;?>
            <?php endif;?>
        }
        // Remove filter after it's done
        NexoAPI.events.removeFilter( 'cart_enable_print' );

        <?php else:?>

        MessageObject.title	=	'<?php echo _s('Effectué', 'nexo');?>';
        MessageObject.msg	=	'<?php echo _s('La commande a été enregistrée.', 'nexo');?>';
        MessageObject.type	=	'success';

        <?php endif;?>
    } else if ( test_order != null ) { // let the user customize the response
        if( data[1].message != undefined ) {
            MessageObject.title	=	'<?php echo _s('Une erreur s\'est produite', 'nexo');?>';
            MessageObject.msg	=	data[1].message;
            MessageObject.type	=	'danger';
        } else {
            <?php if (@$Options[ store_prefix() . 'nexo_enable_autoprint' ] == 'yes'):?>
            MessageObject.title	=	'<?php echo _s('Effectué', 'nexo');?>';
            MessageObject.msg	=	'<?php echo _s('La commande a été enregistrée, mais ne peut pas être imprimée tant qu\'elle n\'est pas complète.', 'nexo');?>';
            MessageObject.type	=	'info';

            <?php else:?>
            MessageObject.title	=	'<?php echo _s('Effectué', 'nexo');?>';
            MessageObject.msg	=	'<?php echo _s('La commande a été enregistrée', 'nexo');?>';
            MessageObject.type	=	'info';
            <?php endif;?>
        }
    }

    <?php if (@$Options[ store_prefix() . 'nexo_enable_smsinvoice' ] == 'yes'):?>
    /**
    * Send SMS
    **/
    // Do Action when order is complete and submited
    NexoAPI.events.doAction( 'is_cash_order', [ v2Checkout, returned ] );
    <?php endif;?>


    // Filter Message Callback
    // add filtred data to callback message
    var data				=	NexoAPI.events.applyFilters( 'callback_message', [ MessageObject, returned, data[0] ] );
    MessageObject		=	data[0];

    // For Success
    if( MessageObject.type == 'success' ) {

        NexoAPI.Toast()( MessageObject.msg );

        // For Info
    } else if( MessageObject.type == 'info' ) {
        NexoAPI.Toast()( MessageObject.msg );
    } else if ( MessageObject.type == 'danger' ) {
        NexoAPI.Notify().warning( MessageObject.title, MessageObject.msg );
    }
}

<?php if (! isset($order)):?>
v2Checkout.resetCart();
<?php else:?>
// If order is not more editable
if( returned.order_type != 'nexo_order_devis' ) {
    v2Checkout.resetCart();
    document.location	=	'<?php echo dashboard_url([ 'orders' ]);?>';
}
<?php endif;?>