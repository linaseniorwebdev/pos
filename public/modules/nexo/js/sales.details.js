Vue.component( 'app-orders-details', ( resolve, reject ) => {
    HttpRequest( `/dashboard/nexo/templates/orders/details` ).then( ({data}) => {
        resolve({
            template: data,
            props: [ 'orderId' ],
            data() { 
                return {
                    tabs                :   SalesListTabs,
                    order               :   {},
                    products            :   [],
                    parent              :   salesCoreModal.modal.VueInstance,
                    modal               :   salesCoreModal.modal,
                    rawOptions          :   SalesListOptions,
                    hasLoaded           :   false,
                    status              :   '',
                    proceeding          :   false,
                    textDomain,
                    printOptions        :   [],
                    printersStatus      :   'not-set',
                    addressInputs       :   [ 
                        'name', 
                        'surname',
                        'email', 
                        'address_1', 
                        'address_2', 
                        'phone', 
                        'country', 
                        'state', 
                        'city', 
                        'enterprise', 
                        'pobox', 
                    ]
                }
            },
            mounted() {
                this.loadDetails();
                this.loadPrinters();

                this.modal.confirm( result => {
                    // console.log( 'from something' );
                })

                this.parent.$on( 'loading_component', () => {
                    // console.log( this.parent.selectedTab );
                })
            },  
            methods: {
                getShippingLabelName( label ) {
                    return textDomain[ label ];
                },

                /**
                 * Submit Print Job
                 * @param {object} option 
                 */
                submitPrintJob( option ) {
                    this.printersStatus    =   'processing';
                    $.ajax( SalesListUrls.printJob.replace( '#', this.orderId ), {
                        success 	:	( printResult ) => {
                            $.ajax( SalesListUrls.nps + '/api/print', {
                                type  	:	'POST',
                                data 	:	{
                                    'content' 	:	printResult,
                                    'printer'	:	option.value
                                },
                                dataType 	:	'json',
                                success 	:	( result ) => {
                                    setTimeout(() => {
                                        this.printersStatus    =   'success';
                                    }, 1000 );
                                }
                            });
                        }
                    });
                },

                /**
                 * Load the printers as available on Nexo
                 * Print Server and make it available for printing
                 * @return void
                 */
                loadPrinters() {
                    this.printersStatus     =   'initializing';
                    HttpRequest.get( SalesListUrls.printer ).then( result => {
                        this.printersStatus     =   'success';
                        this.printOptions   =   result.data.map( printer => {
                            return {
                                label: printer.name,
                                value: printer.name
                            }
                        })
                    }).catch( error => {
                        this.printersStatus     =   'failed';
                    })
                },

                /**
                 * Load the order details
                 * and display it on the detais tab
                 * @return void
                 */
                loadDetails() {
                    this.hasLoaded      =   false;
                    SalesListUrls.order.replace( '#', this.order );
                    HttpRequest.get( SalesListUrls.order.replace( '#', this.orderId ) ).then( result => {
                        this.hasLoaded  =   true;
                        this.order      =   result.data.order;
                        this.products   =   result.data.products;
                        this.address    =   result.data.address;
                    });
                },

                /**
                 * Return a human name for order status
                 * @param {string} namespace 
                 */
                getStatusText( namespace ) {
                    return textDomain[ namespace ] || textDomain.undefined
                },

                /**
                 * Set a tab as active
                 * @param {object} tab 
                 */
                selectTab( tab ) {
                    this.tabs.forEach( _tab => _tab.active = false );
                    tab.active  =   true;
                },

                /**
                 * Order Payment Status
                 * @return {string} order
                 */
                orderPaymentStatus( status ) {
                    switch( status ) {
                        case 'nexo_order_partially_refunded' : return textDomain.orderPartiallyRefunded; break;
                        case 'nexo_order_refunded' : return textDomain.orderRefunded; break;
                        case 'nexo_order_comptant' : return textDomain.orderComplete; break;
                        case 'nexo_order_advance' : return textDomain.orderPartial; break;
                        case 'nexo_order_devis' : return textDomain.orderUnpaid; break;
                    }
                },

                changeState() {
                    if( this.status === '' ) {
                        return NexoAPI.Toast()( textDomain.statusRequired );
                    }

                    this.proceeding    =    true;
                    HttpRequest.post( SalesListUrls.orderState.replace( '#', this.orderId ), {
                        status  :   this.status
                    }).then( result => {
                        this.loadDetails();
                        this.proceeding    =    false;
                    }).catch( error => {
                        this.proceeding     =   false;
                    })
                }
            },  
            computed: {
                activeTab() {
                    return this.tabs.filter( tab => tab.active )[0] || { namespace : false };
                },

                options() {
                    return this.rawOptions.filter( option => [ 'pending', 'error' ].indexOf( option.value ) === -1 );
                }
            }
        });
    });    
})