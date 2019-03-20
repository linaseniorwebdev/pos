/**
 * to get rid of the error.
 */
const tendoo =  {
    loader: {
        show(){

        },
        hide() {

        }
    }
}
jQuery( document ).ready( function() {
    Vue.filter( 'currency', ( value ) => {
        if ( currencyConfig.position === 'before' ) {
            return `${currencyConfig.symbol} ${value}`;
        }
        return `${value} ${currencyConfig.symbol}`;
    });
    const FrontEndVue   =   new Vue({
        el : '#online-ordering',
        data: {
            cartItems           :   [],
            rawItems            :   [],
            rawCategories       :   [],
            payModal            :   null,
            loginModal          :   null,
            topLeftButtons      :   [],
            topRightButtons     :   [],
            cartHeaderButtons   :   [],
            cartFooterButtons   :   [],
            returnTo            :   0,
            loadType            :   'categories',
            breadcrumbs         :   [],
            itemsQueue          :   [],
            checkoutQueue       :   [],
            seatsSelected       :   0, // specific for gastro
            user : false,
            textDomain,
            currencyConfig,
            ...storeOptions,
            isLoading           :   false,
        },
        watch: {
            user() {
                this.$forceUpdate();
            }
        },
        computed: {
            totalItems: function() {
                if( this.cartItems.length === 0 ) {
                    return this.cartItems.length;
                }

                return this.cartItems.map( item => {
                    return item.quantity;
                }).reduce( ( before, after ) => before + after )
            },
            totalPrice: function() {
                if( this.cartItems.length === 0 ) {
                    return this.cartItems.length;
                }

                return this.cartItems.map( item => {
                    return parseFloat( item.PRIX_DE_VENTE_TTC ) * item.quantity;
                }).reduce( ( before, after ) => before + after ).toFixed(2);
            },
            imageUploadPath: function() {
                return this.url + '/public/upload/items-images';
            }
        },
        mounted() {
            this.buildButtons();
            this.buildAddItemQueue();
            this.buildToCheckoutQueue();
            this.loadCategories();
        },
        methods: {

            /**
             * Get category Thumb
             * @return void
             */
            getThumb( resource, config = {} ) {
                const { source = this.url + '/public/upload/categories', param = 'THUMB' } = config;
                if( resource[ param ] === '' || resource[ param ] === undefined ) {
                    return this.url + '/public/modules/nexo/images/default.png';
                }
                return `${source}/` + resource[ param ];
            },

            /**
             * Build To Checkout Queue
             * @return void
             */
            buildToCheckoutQueue() {

                /**
                 * Check if the cart is empty
                 */
                this.checkoutQueue.push( () => {
                    return new Promise( ( resolve, reject ) => {
                        if ( this.cartItems.length === 0 ) {
                            return reject({
                                status: 'failed',
                                message: this.textDomain.emptyCart
                            }); 
                        }

                        return resolve({
                            status: 'success',
                            message: 'can proceed'
                        })
                    });
                });
            },

            /**
             * Build Add Item Queue
             * @return void
             */
            buildAddItemQueue() {
                this.itemsQueue.push( ( item ) => {
                    return this.canIncreaseStock({ item, increaseBy: 1 });
                });

                /**
                 * Define the initial quantity
                 */
                this.itemsQueue.push( ( item ) => {
                    return new Promise( ( resolve, reject ) => {
                        const quantityModal     =   new itemQuantityModal( item );
                        // item.quantity   =   1;
                        // resolve({ mutables: { item }, status: 'success', message: 'quantity set' });
                    })
                });

                /**
                 * Define Metas
                 */
                this.itemsQueue.push( ( item ) => {
                    return new Promise( ( resolve, reject ) => {
                        item.metas   =   [];
                        resolve({ mutables: { item }, status: 'success', message: 'meta array defined' });
                    })
                });
            },

            /**
             * Reset cart
             * @return void
             */
            resetCart() {
                this.cartItems          =   [];
                this.seatsSelected      =   0;
                this.setBreadIndexTo(0, this.breadcrumbs[0]);
                this.buildButtons();
                this.$forceUpdate();
            },

            /**
             * Logout user
             * @return Promise
             */
            logout() {
                this.loader().show();
                return  HttpRequest.get( 'so/logout' ).then( result => {
                    this.user               =   false;
                    this.loader().hide();
                });
            },

            /**
             * Confirm Cart reset
             * @return void
             */
            confirmReset() {
                swal({
                    title: this.textDomain.confirm,
                    html: `<p>${this.textDomain.resetCartConfirmMessage}`,
                    showCancelButton: true
                }).then( result => {
                    if( result.value ) {
                        this.resetCart();
                    }
                })
            },

            /**
             * Send order to the cashier
             * basically save it as pending
             */
            toCheckout() {
                this.queuePromises({ promises: this.checkoutQueue }).then( result => {
                    swal({
                        title: this.textDomain.confirm,
                        html: `<p>${this.textDomain.confirmOrderMessage}</p>`,
                        showCancelButton: true,
                    }).then( result => {
                        console.log( 'should proceed' );
                    })
                }).catch( ({ message, status }) => {
                    $.notify({
                        message
                    }, {
                        type: [ 'failed' ].indexOf( status ) !== -1 ? 'danger' : 'info',
                        z_index: 9999
                    });
                    return false;
                });
            },
            
            /**
             * Cancel
             * @retrun void
             */
            cancel() {
                this.resetCart();
                this.loadCategories(0);
            },

            /**
             * OverLayClass
             * @return void
             */
            loader() {
                return new function() {
                    /**
                     * Show OverLay Loader
                     * @return void
                     */
                    this.show     =   () => {
                        $( 'body' ).append( `
                        <div class="overlay-loader" style="display:none">
                            <div class="lds-roller" style="margin: auto"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                        </div>
                        ` );

                        $( '.overlay-loader' ).fadeIn(500);
                    }

                    /**
                     * Hide OverLay Loader
                     * @return void
                     */
                    this.hide           =   () => {
                        $( '.overlay-loader' ).fadeOut(500, function() {
                            $( this ).remove();
                        })
                    }
                }
            },

            /**
             * Build Top Buttons
             * @return void
             */
            buildButtons() {
                this.topLeftButtons     =   [
                    {
                        label: 'Dashboard',
                        namespace: 'dashboard',
                        class: 'btn btn-light',
                        click : () => {
                        }
                    }, {
                        label: 'Hold',
                        namespace: 'hold_orders',
                        class: 'btn btn-light',
                        click : () => {
                        }
                    }, {
                        label: 'Customer',
                        namespace: 'hold_orders',
                        class: 'btn btn-light',
                        click : () => {
                        }
                    }
                ];

                this.cartFooterButtons  =   [
                    {
                        label: 'Pay',
                        class: 'btn-outline-primary',
                        namespace: 'payment',
                        click: () => {
                            alert( 'ok' );
                        }
                    }, {
                        label: 'Discount',
                        class: 'btn-outline-success',
                        namespace: 'discount',
                        click: () => {
                            alert( 'ok' );
                        }
                    }, {
                        label: 'Hold',
                        class: 'btn-outline-dark',
                        namespace: 'hold',
                        click: () => {
                            alert( 'ok' );
                        }
                    },  {
                        label: 'Cancel',
                        class: 'btn-outline-danger',
                        namespace: 'cancel',
                        click: () => {
                            swal({
                                title: textDomain.confirmCancelCart,
                                text: textDomain.confirmCartText,
                                showCancelButton: true
                            }).then( result => {
                                if ( result.value ) {
                                    this.cancel()
                                }   
                            })
                        }
                    }
                ]

                this.topRightButtons    =  [
                    {
                        label: '<i class="fa fa-window-maximize"></i>',
                        namespace: 'full_screen',
                        class: 'btn btn-light',
                        click : () => {
                            var element = document.body;

                            if (event instanceof HTMLElement) {
                                element = event;
                            }
                        
                            var isFullscreen = document.webkitIsFullScreen || document.mozFullScreen || false;
                        
                            element.requestFullScreen = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || function () { return false; };
                            document.cancelFullScreen = document.cancelFullScreen || document.webkitCancelFullScreen || document.mozCancelFullScreen || function () { return false; };
                        
                            isFullscreen ? document.cancelFullScreen() : element.requestFullScreen();
                        }
                    }, {
                        label: 'Calculator',
                        namespace: 'calculator',
                        class: 'btn btn-light',
                        click : () => {
                            this.openCalculatorPopup();
                        }
                    }
                ];

                /**
                 * Build Cart Header button
                 * @var array {Button}
                 */
                this.cartHeaderButtons  =   [
                    {
                        label: '<i class="fa fa-user"></i> Choose a customer',
                        namespace: 'customers',
                        class: 'btn btn-light',
                        click: () => {
                            this.openCustomerPopup();
                        }
                    }, {
                        label: '<i class="fa fa-truck"></i> Shipping',
                        namespace: 'shipping',
                        class: 'btn btn-light',
                        click: () => {
                            alert( 'customer' )
                        }
                    }, {
                        label: '<i class="fa fa-pencil"></i> Note',
                        namespace: 'note',
                        class: 'btn btn-light',
                        click: () => {
                            alert( 'customer' )
                        }
                    }, {
                        label: '<i class="fa fa-plus"></i> Item',
                        namespace: 'items',
                        class: 'btn btn-light',
                        click: () => {
                            alert( 'customer' )
                        }
                    }
                ]
            },

            /**
             * Open Calculator Popup
             * @return void
             */
            openCalculatorPopup() {
                const calculator    =   new CalculatorModal();                
            },
            
            /**
             * Open a customer popup
             */
            openCustomerPopup() {
                const customerPopup     =   new CustomerPopupModal();
            },

            /**
             * Load Top Categories
             * @return void
             */
            loadCategories( id = 0, callback = null ) {
                return new Promise( ( resolve, reject ) => {
                    if ( ! this.isLoading ) {
                        this.isLoading  =   true;
                        HttpRequest.get( `api/nexopos/pos-v3/categories/${id}` ).then( result => {

                            if ( typeof callback === 'function' ) {
                                callback({ data : result.data, position: 'after' });
                            }
                        
                            this.breadcrumbs.push({
                                id,
                                name: id === 0 ? this.textDomain.home : result.data.category.NOM
                            });
        
                            this.loadType       =   result.data.type;
                            this.returnTo       =   result.data.return_to;
                            if ( this.loadType === 'categories' ) {
                                this.rawCategories  =   result.data.categories;
                            } else {
                                this.rawItems       =   result.data.items;
                            }
    
                            resolve({ data : result.data, position: 'after' });

                            this.isLoading  =   false;
                        }).catch( error => {
                            this.isLoading  =   false;
                        })
                    } else {
                        resolve( false );
                    }
                })
            },

            /**
             * set bread index to
             * @param int
             * @return void
             */
            setBreadIndexTo( index, bread ) {
                /**
                 * Delete bread right before it's updated
                 */
                this.loadCategories( bread.id, ({ data, position }) => {
                    this.breadcrumbs.splice( index, this.breadcrumbs.length );                    
                }).then( ({ data, position }) => {
                })
            },

            /**
             * Go back to return to
             * @param int return to
             * @return void
             */
            goBackTo( index ) {
                if ( index !== 0 ) {
                    this.setBreadIndexTo( index, this.breadcrumbs[ index ])
                } else {
                    this.loadCategories( bread.id );
                }
            },

            /**
             * Add Item to cart
             * @return void
             */
            addToCart( cartItem ) {
                let item    =  Object.assign({}, cartItem );

                this.queuePromises({ promises : this.itemsQueue, param: item }).then( ({ item }) => {
                    this.cartItems.unshift( item );
                }).catch( ({ item, status, message }) => {
                    if( status === 'failed' ) {
                        return $.notify({ message }, {
                            type: 'danger'
                        });
                    }
                })
            },

            /**
             * Promise Resolver
             * @return any
             */
            queuePromises({ promises, param, index = 0, total = 0, results = [], mutables = {} }) {
                // promisesArray, _item 
                if( total === 0 ) {
                    total   =   promises.length;
                }

                return new Promise( ( resolve, reject ) => {
                    if( promises[ index ] !== undefined ) {
                        promises[ index ]( param ).then( response => {
                            
                            results.push( response );

                            if( response.mutables !== undefined ) {
                                mutables     =   { ...response.mutables };
                            }

                            this.queuePromises({
                                promises,
                                param,
                                index   : index + 1,
                                results,
                                total,
                                mutables
                            }).then( result => {
                                resolve( result );
                            }).catch( error => {
                                reject( error );
                            });

                        }).catch( error => {
                            reject( error );
                        })
                    } else {
                        resolve({
                            status: 'success',
                            message: 'promise queue successful',
                            results,
                            ...mutables // merge the mutable within the result object
                        })
                    }
                });
            },
              
            /**
             * get Single Item price
             * @return int
             */
            getSingleItemPrice( item ) {
                if( Object.values( item.metas ).length > 0 ) {
                    return ( parseFloat( item.PRIX_DE_VENTE_TTC ) + Object.values( item.metas )
                        .map( meta => meta.price ).reduce( ( prev, current ) => {
                            console.log( prev, current );
                        return parseFloat( prev ) + parseFloat( current );
                    } ) ).toFixed(2);
                }
                return ( parseFloat( item.PRIX_DE_VENTE_TTC ) ).toFixed(2);
            },

            /**
             * get total item price
             * @return int
             */
            getTotalItemPrice( item ) {
                if( Object.values( item.metas ).length > 0 ) {
                    return ( ( parseFloat( item.PRIX_DE_VENTE_TTC ) + Object.values( item.metas )
                        .map( meta => meta.price ).reduce( ( prev, current ) => {
                            return parseFloat( prev ) + parseFloat( current );
                    }) ) * item.quantity ).toFixed(2);
                }
                return ( parseFloat( item.PRIX_DE_VENTE_TTC ) * item.quantity ).toFixed(2);
            },

            /**
             * Refresh Cart
             * @return void
             */
            refreshCart() {
                this.$forceUpdate();
            },

            /**
             * Can Increase item Stock
             * @return void
             */
            canIncreaseStock({ item, increaseBy }) {
                return new Promise( ( resolve, reject ) => {

                    if( item.STOCK_ENABLED !== '0' ) {
                        
                        let totalQuantityOnCart     =   0;
                        
                        this.cartItems.forEach( cartItem => {
                            if( cartItem.CODEBAR === item.CODEBAR ) {
                                totalQuantityOnCart     +=   cartItem.quantity;
                            }
                        });

                        if( ( totalQuantityOnCart ) - parseFloat( item.QUANTITE_RESTANTE ) === 0 ) {
                            return reject({
                                status: 'failed',
                                message: this.textDomain.stockExausted,
                                mutables: { item }
                            });
                        }                        
                    } 

                    return resolve({
                        status: 'success',
                        message: this.textDomain.itemAdded,
                        mutables: { item }
                    });
                })
            },

            /**
             *  increase quantity 
             */
            increaseQuantity( item, increaseBy = 1 ) {      
                /**
                 * if the stock management is enabled
                 */
                this.canIncreaseStock({ item, increaseBy }).then( success => {
                    item.quantity   +=  increaseBy;

                    /**
                     * Notify if the item has been added
                     */
                    $.notify({
                        message: textDomain.itemAdded
                    }, {
                        type: 'success',
                        delay: 2000                    
                    });
                }).catch( error => {
                    $.notify({
                        message: textDomain.stockExausted
                    }, {
                        type: 'danger'
                    });
                })
            },

            /**
             * Decrease Quantity
             * @return void
             */
            decreaseQuantity( item, decreaseBy = 1, index = null ) {
                if( item.quantity - decreaseBy >= 1 ) {
                    item.quantity--;
                } else {
                    /**
                     * Remove item from cart
                     */
                    this.cartItems.splice( index, 1 );
                }
            }
        }
    })
})