<?php $this->load->module_include( 'nexo', 'angular/order-list/filters/money-format' );?>
<script>
    tendooApp.controller( 'newSupplyUIController', [ '$scope', '$compile', '$filter', '$timeout', '$http', function(
        $scope, $compile, $filter, $timeout, $http
    ) {
        $scope.cart         =   [];
        $scope.columns      =   7;
        $scope.providers    =   <?php echo json_encode( $this->Nexo_Shipping->get_providers() );?>;
        $scope.deliveries   =   <?php echo json_encode( $this->Nexo_Shipping->get_shipping() );?>;
        $scope.deliveries.unshift({
            TITRE           :   '<?php echo __( 'Ajouter un approvisionnement', 'nexo' );?>',
            ID              :   0
        });
        $scope.notifyStatus     =   'notSelected';
        $scope.deliveryControls     =   false;

        $scope.selectedDelivery     =   { ID : 0 };
        $scope.selectedProvider     =   null;

        $scope.npAutocompleteOptions = {
            url: '<?php echo site_url( array( 'rest', 'nexo', 'search_item' ) );?>' +  '?<?php echo store_get_param( null );?>',
            headers		:	{
                '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
            },
            queryMode           :   true,
            callback            :   function( data, option ) {
                if( data.length == 1 ) {
                    $scope.addItem( data[0] );
                    angular.element( '.search-input' ).val('');
                    angular.element( '.search-input' ).select();
                    option.close();
                    return false;
                }
                return true;
            },
            nameAttr            :   'DESIGN',
            clearOnSelect       :   true,
            onSelect            :   function( item ) {
                $scope.addItem( item );
            }, 
            onError             :   function(){
                angular.element( '.search-input' ).val('');
                angular.element( '.search-input' ).select();
            },
            delay               :   500
        };

        /**
         * For Delivery
         */
        $scope.npDeliveryAutocompleteOptions = {
            url: '<?php echo site_url( array( 'rest', 'nexo', 'search_delivery' ) );?>' +  '?<?php echo store_get_param( null );?>',
            headers		:	{
                '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
            },
            queryMode           :   true,
            callback            :   function( data, option ) {
                return true;
            },
            nameAttr            :   'TITRE',
            clearOnSelect       :   true,
            onSelect            :   function( item ) {
                $scope.selectDelivery( item );
                $scope.notifyStatus     =   'selectedDelivery';
            }, 
            onError             :   function(){
                NexoAPI.Notify().warning( 
                    '<?php echo _s( 'Arrivage introuvable.', 'nexo' );?>', 
                    '<?php echo _s( 'L\'arrivage recherché est introuvable. Pour créer un nouvel arrivage, cliquez sur "Créer".', 'nexo' );?>' 
                );
                angular.element( '.search-delivery-input' ).val('');
                angular.element( '.search-delivery-input' ).select();
            },
            delay               :   500
        };

        /**
         * Toggle delivery controls
         */
        $scope.toggleDeliveryControls   =   function() {
            $scope.deliveryControls     =   ! $scope.deliveryControls;

            /**
             * The delivery control is set to "search" and let's cancel the newDelivery name.
             */
            if ( ! $scope.deliveryControls ) {
                $scope.newDelivery  =   '';
            }
        }

        /**
         * Watch newDelivery Changes
         */
        $scope.$watch( 'newDelivery', function( newValue, oldValue ) {
            if ( $scope.deliveryControls ) {
                if ( newValue == null || newValue.toString().length == 0 ) {
                    $scope.notifyStatus     =   'wrongDeliveryName';
                } else {
                    $scope.notifyStatus     =   'newDelivery';
                }
            }
        });

        /**
         * Select Delivery
         * @param object delivery object
         * @return void
         */
        $scope.selectDelivery   =   function( delivery ) {
            $scope.selectedDelivery     =   delivery;
        }

        /**
         * Create Delivery Immédiately
         * @param void
         * @return void
         */
        $scope.createShipping   =   function() {
            $http.post( '<?php echo site_url([ 'api', 'nexopos', 'supplies', store_get_param('?')]);?>', {
                title   :   $scope.newDelivery
            }, {
                headers     :   {
                    [ tendoo.rest.key ] : tendoo.rest.value
                }
            }).then( result => {
                NexoAPI.Toast()( result.data.message );
                $scope.newDelivery      =   '';
            });
        }

        /**
         * Total
         * @param object cart items
         * @param string first key
         * @param string second key
         * @return number
        **/

        $scope.total            =   function( cart, key, key2 ) {
            let total       =   0;
            if( typeof key2 == 'undefined' ) {   
                _.each( cart, function( item ){
                    total       +=  parseFloat( item[ key ] );
                });
            } else {
                _.each( cart, function( item ){
                    total       +=  ( parseFloat( item[ key ] ) * parseFloat( item[ key2 ] ) );
                });
            }
            return total;
        }

        /**
         * Search Item
         * @param string item value
         * @return void
        **/

        $scope.addItem      =   function( item ) {
            let hasFound    =   false;
            _.each( $scope.cart, function( _item ) {
                if( _item.CODEBAR == item.CODEBAR ) {
                    hasFound    =      true;
                    _item.SUPPLY_QUANTITY++;
                }
            });  

            // if the item hasn't been found. Let's add it for the first time.
            if( ! hasFound ) {
                item.SUPPLY_QUANTITY        =   1;
                $scope.cart.push( item );
            }
        }

        /**
         * Get Delivery Invoice
         * @param int delivery id
         * @return void
        **/

        $scope.getDeliveryInvoice           =   function( deliveryId ) {
            $http.get( '<?php echo dashboard_url([ 'supplies', 'invoice' ]);?>/' + deliveryId + '?exclude_header=true', {
                headers			:	{
                    '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
                }
            }).then(function( returned ){
                NexoAPI.Bootbox().confirm({
                    title       :   '<?php echo _s( 'Reçu d\'approvisionnement', 'nexo' );?>',
                    message     :   '<div class="to-print">' + returned.data + '</div>',
                    callback    :   function( action ) {
                        if( action ) {
                            NexoAPI.Popup( returned.data );
                        }
                    },
                    buttons: {
                        confirm: {
                            label: '<?php echo _s( 'Imprimer', 'nexo' );?>',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<?php echo _s( 'Annuler', 'nexo' );?>',
                            className: 'btn-danger'
                        }
                    },
                });

                $scope.deliveries.unshift({
                    TITRE           :   '<?php echo __( 'Ajouter un approvisionnement', 'nexo' );?>',
                    ID              :   0
                });
            });
        }

        /**
         * Submit Supplying
         * @return void
        **/

        $scope.canSubmit        =   true;

        $scope.submitSupplying      =   function(){
            if( $scope.canSubmit   ==  true ) {

                if( $scope.cart.length == 0 ) {
                    return NexoAPI.Bootbox().alert( '<?php echo _s( 'Vous devez avoir au moins un produit dans la liste d\'approvisionnement', 'nexo' );?>' );
                }

                if( typeof $scope.selectedProvider.ID == 'undefined' || $scope.selectedProvider.ID == '0' ) {
                    return NexoAPI.Bootbox().alert( '<?php echo _s( 'Vous devez choisir un fournisseur', 'nexo' );?>' );
                }
                
                if( 
                    ( 
                        typeof $scope.selectedDelivery.ID == 'undefined' || 
                        $scope.selectedDelivery.ID == '0'
                    )   && $scope.deliveryControls == false // which means we're searching a delivery
                ) {
                    return NexoAPI.Notify().warning( 
                        '<?php echo _s( 'Attention', 'nexo' );?>', 
                        '<?php echo _s( 'Vous devez choisir un arrivage.', 'nexo' );?>' 
                    );
                }
                
                if ( ( $scope.newDelivery == '' || $scope.newDelivery == undefined ) && $scope.deliveryControls ) { // which means we're adding a new delivery
                    return NexoAPI.Notify().warning( 
                        '<?php echo _s( 'Attention', 'nexo' );?>', 
                        '<?php echo _s( 'Vous devez définir un nom valide pour l\'arrivage.', 'nexo' );?>' 
                    );
                }
                
                NexoAPI.Bootbox().confirm( '<?php echo _s( 'Souhaitez-vous valider l\'opération ?', 'nexo' );?>', function( action ) {
                    if( action ) {
                        $scope.canSubmit           =   false;
                        var items            =   [];
                        // Format data to send
                        _.each( $scope.cart, function( item ) {
                            items.push({
                                'item_barcode'  :      item.CODEBAR,
                                'item_qte'      :      item.SUPPLY_QUANTITY,
                                'type'          :      'supply',
                                'unit_price'    :      item.PRIX_DACHAT,
                                'ref_provider'  :      $scope.selectedProvider.ID
                            });
                        });

                        var data     =  { items };
                        data.selected_delivery  =   $scope.selectedDelivery;
                        data.new_delivery       =   $scope.newDelivery;
                        data.ref_provider       =   $scope.selectedProvider;

                        $http.post( '<?php echo site_url( array( 'rest', 'nexo', 'bulk_supply' ) );?>' + '<?php echo store_get_param( '?' );?>', data, {
                            headers			:	{
                                '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
                            }
                        }).then(function( returned ){
                            let data                =   returned.data;
                            $scope.getDeliveryInvoice( data.shipping_id );
                            $scope.canSubmit       =   true;
                            $scope.cart             =   [];
                            NexoAPI.Toast()( '<?php echo _s( 'Approvisionnement effectuée.', 'nexo' );?>' );
                        }, function(){
                            $scope.canSubmit       =   true;
                        });
                    } 
                });
            }
        }

        /**
         *  Remove item from cart
         * @param int index
         * @return void
        **/

        $scope.removeItem           =   function( $index ) {
            $scope.cart.splice( $index, 1 );
        }
        
        // Add autofocus on field
        var counter         =   0;
        setInterval( function(){
            console.log(  );
            if( _.indexOf([ 'TEXTAREA', 'INPUT', 'SELECT'], $( ':focus' ).prop( 'tagName' ) ) == -1 || $( ':focus' ).prop( 'tagName' ) == undefined ) {                
                if( counter == 1 ) {
                    $( '[np-input-model="searchValue"]' ).focus();
                    counter     =   0;
                }
                counter++;
            } 
        }, 1000 );
    }]);

    tendooApp.directive('numberMask', function () {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, elem, attrs, ctrl) {   
                var oldValue = null;
                scope.$watch(attrs.ngModel, function (newVal, oldVal) {
                    var min = parseInt(attrs.min) || 0;
                    var max = parseInt(attrs.max) || 10;
                    if (!between(newVal, min, max)) {
                        if (newVal > max)
                            ctrl.$setViewValue(max);
                        else if (newVal < min)
                            ctrl.$setViewValue(min);
                        else
                            ctrl.$setViewValue(oldValue);
                        ctrl.$render();
                    }else{
                        oldValue = newVal;
                    }
                }, true);

                function between(n, min, max) { return n >= min && n <= max; }
            }
        };
    });
</script>