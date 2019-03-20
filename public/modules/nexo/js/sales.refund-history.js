Vue.component( 'app-orders-refund-history', ( resolve, reject ) => {
    HttpRequest( `/dashboard/nexo/templates/orders/refund-history` ).then( ({data}) => {
        resolve({
            template: data,
            props: [ 'orderId' ],
            data() {
                return Object.assign({}, {
                    histories       :   [],
                    printOptions    :   [],
                    printerStatus   :   'initializing'
                }, RefundHistoryData )
            },
            mounted() {
                Promise.all([ this.loadRefundHistory(), this.loadPrinters() ]).then( responses => {
                    console.log( responses );
                    this.histories    =   responses[0].data;
                }).catch( errors => {
                    console.log( errors );
                })
            },
            methods: {
                /**
                 * load refund history
                 * @return void
                 */
                loadRefundHistory() {
                    return HttpRequest.get( this.url.refundHistory.replace( '#', this.orderId ) );
                },

                /**
                 * load printer has configured with NPS
                 * @return {promise} async response
                 */
                loadPrinters() {
                    return new Promise( ( resolve, reject ) => {
                        this.printersStatus     =   'initializing';
                        HttpRequest.get( SalesListUrls.printer ).then( result => {
                            
                            this.printersStatus     =   'success';
                            
                            this.printOptions   =   result.data.map( printer => {
                                return {
                                    label: printer.name,
                                    value: printer.name
                                }
                            });

                            resolve({
                                status: 'success',
                                message: textDomain.successfullyInitializedPrinters
                            });

                        }).catch( error => {
                            
                            this.printersStatus     =   'failed';

                            resolve({
                                status: 'failed',
                                message: textDomain.failedToInitializePrinters
                            })
                        })
                    })
                },

                /**
                 * Submit Print Job
                 * @param {object} option 
                 */
                submitPrintJob( printer, history ) {
                    this.printersStatus    =   'processing';
                    $.ajax( this.url.refundReceipt.replace( '#', history.ID ), {
                        success 	:	( printResult ) => {
                            $.ajax( SalesListUrls.nps + '/api/print', {
                                type  	:	'POST',
                                data 	:	{
                                    'content' 	:	printResult,
                                    'printer'	:	printer.value
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
                 * return refund human name type
                 * @param {string} string return type
                 * @return string
                 */
                getRefundType( string ) {
                    return textDomain[ string ] || textDomain.notSet;
                },

            }
        })
    });
})