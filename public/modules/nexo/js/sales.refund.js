Vue.component( 'app-orders-refund', ( resolve, reject ) => {
    HttpRequest( `/dashboard/nexo/templates/orders/refund` ).then( ({data}) => {
        resolve({
            template: data,
            props: [ 'orderId' ],
            data() {
                return Object.assign({
                    refundMethod: '',
                    order: {},
                    refunds: [],
                    products: [],
                    refundShippingFees: false,
                    refundCartItems: [],
                    product: '',
                    isLoading: false,
                    canProceed: false,
                    amountToRefund: 0,
                    shippingFees : 0,
                    addItemStatus: '',
                    isLoadingFullRefund: false,
                    isLoadingPartialRefund: false,
                    payment_type: 'cash',
                }, salesRefundData );
            },  
            mounted() {
                this.loadOrder();
            },
            methods: {
                /**
                 * Decreate a stock of a specific product
                 * @param {oject} item the product 
                 */
                decrease( item ) {
                    if ( item.refund_quantity === 1 ) {
                        let index   =   this.refundCartItems.indexOf( item );
                        this.refundCartItems.splice( index, 1 );
                    } else {
                        item.refund_quantity--;
                    }
                    this.$forceUpdate();
                },

                /**
                 * Attempt to increase the quantity of the refunded item
                 * @param {object} item the item to treat
                 */
                increase( item ) {
                    const quantity  =   this.getAddedQuantity( item );
                    if ( quantity === parseFloat( item.QUANTITE ) ) {
                        return NexoAPI.Toast()( textDomain.notEnoughStock );
                    }
                    item.refund_quantity    =   parseFloat( item.refund_quantity ) + 1;
                    this.$forceUpdate();
                },

                /**
                 * Get Item State and return the 
                 * text localization version
                 * @param {string} name 
                 */
                getItemStateHumanName( name ) {
                    return textDomain[ name ] || name;
                },

                /**
                 * Return the total price if a refunded
                 * item
                 * @param {object} item 
                 */
                getTotalRefundItemPrice( item ) {
                    return parseFloat( item.PRIX ) * parseFloat( item.refund_quantity );
                },

                /**
                 * once clicked, the item selected
                 * is added to the refund cart
                 * @return void
                 */
                addToRefundCart() {
                    if ( this.product === '' ) {
                        return NexoAPI.Toast()( textDomain.mustSelectProduct );
                    }

                    if ( this.addItemStatus === '' ) {
                        return NexoAPI.Toast()( textDomain.mustSelectProductStatus );
                    }

                    this.refundCartItems.push( Object.assign({}, this.product, {
                        refund_quantity: 1,
                        refund_state: this.addItemStatus
                    }));

                    this.product    =   '';
                },

                toggleShippingFees() {
                    if ( this.refundShippingFees ) {
                        this.shippingFees   =   parseFloat( this.order.SHIPPING_AMOUNT );
                    } else {
                        this.shippingFees   =   0;
                    }
                },
                
                /**
                 * reset the current screen
                 * @return void
                 */
                reset() {
                    this.amountToRefund             = 0;
                    this.shippingFees               = 0;
                    this.addItemStatus              = '';
                    this.refundCartItems            = [];
                    this.canProceed                 = false;
                    this.isLoading                  = false;
                    this.isLoadingFullRefund        = false;
                    this.isLoadingPartialRefund     = false;
                    this.payment_type               = 'cash'; // set defalt to cash
                    this.order                      = {};
                    this.products                   = [];
                    this.product                    = ''; // single item to be added to the refund cart
                    this.refunds                    = [];
                    this.refundMethod               = '';
                    this.refundShippingFees         = false;
                    this.loadOrder();
                },
                
                /**
                 * Proceed to a partial refund
                 * @return void
                 */
                proceedToPartialRefund() {
                    /**
                     * if the refund amount is not valid
                     */
                    if ( parseFloat( this.amountToRefund ) <= 0 || this.amountToRefund === '' ) {
                        return swal({
                            title: textDomain.cantProceed,
                            text: textDomain.refundRequireValidAmount,
                            type: 'error'
                        });
                    }

                    swal({
                        input: 'textarea',
                        title: textDomain.confirm,
                        text: textDomain.confirmPartialRefund,
                        showCancelButton: true,
                    }).then( result => {
                        if ( result.dismiss === undefined ) {
                            this.isLoading  =   true;
                            this.isLoadingPartialRefund     =   true;
                            HttpRequest.post( this.url.refund.replace( '#', this.orderId ), {
                                total           :   this.amountToRefund,
                                sub_total       :   this.amountToRefund,
                                shipping_fees   :   0,
                                type            :   'stockless',
                                description     :   result.value,
                                payment_type    :   this.payment_type
                            }).then( result => {
                                NexoAPI.Toast()( result.data.message );
                                this.reset();
                            }).catch( error => {
                                this.isLoading  =   false;
                                this.isLoadingPartialRefund     =   false;
                                NexoAPI.Toast()( error.response.data.message );    
                            })
                        }
                    })
                },

                /**
                 * Proceed to full refund according
                 * to the type of order selected
                 * @return void
                 */
                proceedToFullRefund() {
                    swal({
                        input: 'textarea',
                        title: textDomain.confirm,
                        text: textDomain.confirmRefund,
                        showCancelButton: true,
                    }).then( result => {
                        if ( result.value ) {
                            this.isLoading  =   true;
                            this.isLoadingFullRefund    =   true;
                            HttpRequest.post( this.url.refund.replace( '#', this.orderId ), {
                                total           :   this.rightTotalAmount,
                                sub_total       :   this.rightTotalAmount,
                                shipping_fees   :   0,
                                type            :   'stockless',
                                description     :   result.value,
                                payment_type    :   this.payment_type
                            }).then( result => {
                                NexoAPI.Toast()( result.data.message );
                                this.reset();
                            }).catch( error => {
                                this.isLoading  =   false;
                                this.isLoadingPartialRefund     =   false;
                                NexoAPI.Toast()( error.response.data.message );    
                            });
                        }
                    })
                },

                proceedRefundWithStock() {
                    if ( this.refundCartItems.length === 0 ) {
                        return swal({
                            type: 'error',
                            title: textDomain.cantProceed,
                            text: textDomain.refundCartEmpty
                        });
                    }
                    
                    swal({
                        input: 'textarea',
                        title: textDomain.confirm,
                        text: textDomain.confirmRefund,
                        showCancelButton: true,
                    }).then( result => {
                        if ( result.value ) {
                            this.isLoading  =   true;
                            this.isLoadingFullRefund    =   true;
                            HttpRequest.post( this.url.refund.replace( '#', this.orderId ), {
                                total                   :   this.getOverallRefundAmount(),
                                sub_total               :   this.getTotalOf( this.refundCartItems ),
                                shipping_fees           :   parseFloat( this.shippingFees ),
                                type                    :   'withstock',
                                description             :   result.value,
                                payment_type            :   this.payment_type,
                                products                :   this.refundCartItems,
                                refund_shipping_fees    :   this.refundShippingFees
                            }).then( result => {
                                NexoAPI.Toast()( result.data.message );
                                this.reset();
                            }).catch( error => {
                                this.isLoading  =   false;
                                this.isLoadingPartialRefund     =   false;
                                NexoAPI.Toast()( error.response.data.message );    
                            });
                        }
                    })
                },

                /**
                 * Display an error if the refund 
                 * exceed the total value of the order
                 * @return void
                 */
                checkAmountToRefund() {
                    this.amountToRefund     =   parseFloat( this.amountToRefund ) <= 0 ? 0 : parseFloat( this.amountToRefund );
                    if ( parseFloat( this.amountToRefund ) > parseFloat( this.rightTotalAmount ) ) {
                        this.amountToRefund     =   this.rightTotalAmount;
                        NexoAPI.Toast()( textDomain.cantExceedTotal );
                    }
                },  
                /**
                 * Select the type of the refund
                 * @param {string} type refund type
                 */
                selectRefundType( type ) {
                    this.refundMethod   =   type;
                },

                /**
                 * Load order as it's saved on the db
                 * @return void
                 */
                loadOrder() {
                    this.isLoading      =   true;

                    Promise.all([
                        HttpRequest.get( SalesListUrls.order.replace( '#', this.orderId ) )
                    ]).then( results => {

                        this.order              =   results[0].data.order;
                        this.products           =   results[0].data.products;
                        this.refunds            =   results[0].data.refunds;
                        this.isLoading          =   false;

                        switch( this.order.TYPE ) {
                            case 'nexo_order_comptant': 
                            case 'nexo_order_advance': 
                            case 'nexo_order_partially_refunded':
                                this.canProceed     =   true;
                            break;
                            case 'nexo_order_devis':
                                this.canProceed     =   false;
                            break;
                        }
                    });
                },

                /**
                 * Get the total quantity added to the cart for an item
                 * @param {object} product product to compare
                 */
                getAddedQuantity( product ) {
                    const allAddedItems  =   this.refundCartItems.filter( _product => _product.ID === product.ID )
                            .map( item => item.refund_quantity );
                    const quantity      =   allAddedItems.length === 0 ? 0 : allAddedItems.reduce( ( total, next ) => total + next );
                    return quantity;
                },

                /**
                 * Get total of refunded items
                 * available on the cart
                 * @return json
                 */
                getTotalOf( items ) {
                    if ( items.length > 0 ) {
                        return parseFloat( items.map( item => {
                            return this.getTotalRefundItemPrice( item );
                        }).reduce( ( total, next ) => total + next ).toFixed(2) );
                    }
                    return 0;
                },

                /**
                 * Get Overall refund amount
                 * including or not shipping fees
                 * @return int
                 */
                getOverallRefundAmount() {
                    return this.getTotalOf( this.refundCartItems ) + parseFloat( this.shippingFees );
                },

                /**
                 * Detect refund overflow
                 * @return json
                 */
                detectRefundOverflow() {
                    if ( parseFloat( this.amountToRefund ) > parseFloat( this.rightTotalAmount ) ) {
                        this.amountToRefund     =   this.rightTotalAmount;
                        NexoAPI.Toast()( textDomain.cantExceedTotal );
                    }
                }
            },
            computed: {
                greatherThanAvailable() {
                    return parseFloat( this.amountToRefund ) > parseFloat( this.rightTotalAmount );
                },

                /**
                 * wether a refund can be proceeded
                 * @return boolean
                 */
                cantProceedStockRefund() {
                    return this.order.TYPE === 'nexo_order_comptant';
                },

                /**
                 * return the order for the 
                 * current order
                 * @return object
                 */
                orderProducts() {
                    return this.products.filter( product => {
                        const quantity  =   this.getAddedQuantity( product );                        
                        if ( parseFloat( product.QUANTITE ) > quantity ) {
                            return product;
                        }
                    })
                },

                /**
                 * determine wether the order
                 * has received a refund
                 * @return boolean
                 */
                hasRefund() {
                    return Object.values( this.refunds ).length > 0;
                },

                /**
                 * return boolean wether the 
                 * order yas yet received a stockless refund
                 * @return boolean
                 */
                isStockLessRefund() {
                    return this.hasRefund && this.refunds.filter( refund => refund.TYPE === 'stockless' ).length > 0;
                },

                /**
                 * return boolean wether the 
                 * order yas yet received a with stock refund
                 * @return boolean
                 */
                isStockWithRefund() {
                    return this.hasRefund && this.refunds.filter( refund => refund.TYPE === 'withstock' ).length > 0;
                },

                /**
                 * Filter the payment gateway
                 * @return array
                 */
                paymentsGateway() {
                    const keys    =   Object.keys( this.rawPaymentsGateway );
                    return Object.values( this.rawPaymentsGateway ).map( ( payment, index ) => {
                        return {
                            label: payment,
                            namespace : keys[ index ]
                        }
                    }).filter( payment => [ 'unknow', 'coupon' ].indexOf( payment.namespace ) === -1 );
                },

                /**
                 * return the right amount which need to be 
                 * refunded
                 * @return {int}
                 */
                rightTotalAmount() {
                    switch( this.order.TYPE ) {
                        case 'nexo_order_comptant':
                        case 'nexo_order_partially_refunded':
                            return this.order.TOTAL;
                        break;
                        case 'nexo_order_advance':
                            return this.order.SOMME_PERCU;
                        break;
                        default: 
                            return 0;
                    }
                },

                /**
                 * Returns boolean wether a payment is 
                 * complete or partial
                 * @return {boolean} boolean
                 */
                isFullOrder() {
                    return this.order.TYPE === 'nexo_order_comptant';
                },

                /**
                 * return wether the current order is incomplete
                 */
                isPartialOrder() {
                    return this.order.TYPE === 'nexo_order_advance';
                },

                /**
                 * Determine if the refund types should be 
                 * shown
                 * @return boolean
                 */
                shouldShowRefundType() {
                    return this.refundMethod === '';
                },

                /**
                 * Determine if the stock refund 
                 * is made with a stock return or not
                 * @return boolean
                 */
                isWithoutStockReturn() {
                    return this.refundMethod === 'no_stock_return';
                },

                /**
                 * Determine if the stock refund 
                 * is made with a stock return or not
                 * @return boolean
                 */
                isWithStockReturn() {
                    return this.refundMethod === 'with_stock_return';
                }
            }
        });
    });   
})