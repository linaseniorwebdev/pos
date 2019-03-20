Vue.component( 'app-orders-payment', ( resolve, reject ) => {
    HttpRequest( `/dashboard/nexo/templates/orders/payment` ).then( ({data}) => {
        resolve({
            template: data,
            props: [ 'orderId' ],
            data() {
                return Object.assign({
                    order: {},
                    isLoading: false,
                    isSubmitting: false,
                    canProceed: false,
                    payments: [],
                    paymentsHistory: [],
                    gateway: '',
                    amount: 0
                }, PaymentData )
            },  
            mounted() {
                this.loadOrder();
            },
            methods: {
                getTotal( totals ) {
                    const plusNumbers  =   totals.filter( total => total.OPERATION === 'incoming' ).map( total => parseFloat( total.MONTANT ) );
                    const minusNumbers  =   totals.filter( total => total.OPERATION === 'outcoming' ).map( total => parseFloat( total.MONTANT ) );

                    const plus      =   plusNumbers.length === 0 ? 0 : plusNumbers.reduce( ( total, newNumber ) => total + newNumber );
                    const minus      =   minusNumbers.length === 0 ? 0 : minusNumbers.reduce( ( total, newNumber ) => total + newNumber );
                    return (plus - minus).toFixed(2);
                },
                /**
                 * Return a human name of a payment 
                 * operation
                 * @param {string} namespace 
                 */
                getOperationHumanName( namespace ) {
                    return textDomain[ namespace ] || namespace;
                },

                /**
                 * Bind field selection for amount
                 * @return boolean
                 */
                bindSelectAmountField() {
                    setTimeout( () => {
                        $( '.amount-field' ).bind( 'focus', function() {
                            $( this ).select();
                        });
                    }, 200 );
                },

                /**
                 * Load order as it's saved on the db
                 * @return void
                 */
                loadOrder() {
                    this.isLoading      =   true;

                    Promise.all([
                        HttpRequest.get( SalesListUrls.order.replace( '#', this.orderId ) ),
                        HttpRequest.get( this.url.paymentList.replace( '#', this.orderId ) )
                    ]).then( results => {
                        this.order              =   results[0].data.order;
                        this.paymentsHistory    =   results[1].data;
                        this.isLoading          =   false;

                        switch( this.order.TYPE ) {
                            case 'nexo_order_comptant': 
                                this.canProceed     =   false;
                            break;
                            case 'nexo_order_devis':
                            case 'nexo_order_advance': 
                                this.canProceed     =   true;
                            break;
                        }

                        this.bindSelectAmountField();
                    });
                },

                getPaymentHumanName( namespace ) {
                    return this.rawGateways[ namespace ] || textDomain.unknow;
                },  

                /**
                 * Reset the entire object
                 * @return void
                 */
                reset() {
                    this.order              =   {};
                    this.amount             =   0;
                    this.gateway            =   '';
                    this.paymentsHistory    =   [];
                },  

                /**
                 * Submit the payement to the db
                 * @return void
                 */
                proceedPayment() {
                    if ( ! this.canProceedToPayment ) {
                        return NexoAPI.Toast()( textDomain.requirePaymentGateway );
                    }

                    swal({
                        title: textDomain.confirm,
                        html: textDomain.proceedToPayment,
                        showCancelButton: true
                    }).then( result => {
                        if( result.value ) {
                            this.isSubmitting  =   true;
                            HttpRequest.post( this.url.payment.replace( '#', this.orderId ), {
                                amount      :   this.amount,
                                namespace   :   this.gateway
                            }).then( result => {
                                this.isSubmitting  =   false;
                                if( result.data.status === 'success' ) {
                                    NexoAPI.Toast()( textDomain.paymentSuccess );
                                    this.reset();
                                    this.loadOrder();
                                }
                            }).catch( ({ response }) => {
                                this.isSubmitting  =   false;
                                NexoAPI.Toast()( response.data.message );
                            })
                        }
                    })
                }
            },  
            computed: {
                gateways() {
                    const keys  =   Object.keys( this.rawGateways );
                    return Object.values( this.rawGateways ).map( ( label, index ) => {
                        /**
                         * We won't be using the unknow 
                         * coupon and the payment gateway
                         */
                        if( [ 'unknow', 'coupon' ].indexOf( keys[ index ] ) === -1 ) {
                            return {
                                label,
                                value :  keys[ index ]
                            }
                        }
                        return false;
                    }).filter( a => a !== false ); // remove false entry
                },

                /**
                 * Check wether an amount is input
                 * @return boolean
                 */
                amountIsValid() {
                    return /\d*/g.test( this.amount );
                },

                /**
                 * can proceed to payment
                 * @return boolean
                 */
                canProceedToPayment() {
                    return this.amountIsValid && Object.keys( this.rawGateways ).indexOf( this.gateway ) !== -1 && parseFloat( this.amount ) > 0;
                }
            }
        });
    });   
})