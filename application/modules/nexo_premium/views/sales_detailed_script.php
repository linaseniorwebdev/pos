<?php
global $Options;
?><!-- entry.ITEMS -->
<?php include_once( MODULESPATH . '/nexo/inc/angular/order-list/filters/money-format.php' );?>
<script type="text/javascript">
tendooApp.controller( 'advancedSalesReportController', [ '$scope', '$http', function( $scope, $http ) {

    $scope.startDate            =   '<?php echo $start_date;?>';
    $scope.endDate              =   '<?php echo $end_date;?>';
    $scope.entries              =   new Object;
    $scope.rawOrders            =   [];
    $scope.isEmpty              =   true;
    $scope.paymentType          =   <?php echo json_encode( $this->config->item( 'nexo_all_payment_types' ) );?>;
    $scope.paymentTypeDetails   =   new Object;
    $scope.rawData              =   new Array;
    $scope.reportType           =   'full';

    $scope.$watch( 'entries', function(){
        $scope.isEmpty  =   false;
        if( _.keys( $scope.entries ).length == 0 ) {
            $scope.isEmpty  =   true;
        }
    });

    /**
     *  Build Entries
     *  @param object entried returned by server
     *  @return void
    **/

    $scope.buildEntries             =   function( data ){
        $scope.entries              =   new Object;
        $scope.rawData              =   data;
        $scope.authors              =   new Object;
        _.each( $scope.rawData, function( value ) {
            $scope.entries          =   _.extend( $scope.entries, _.object( [ value.CODE ], [{
                TOTAL               :   value.TOTAL,
                TOTAL_REFUND        :   value.TOTAL_REFUND,
                CODE                :   value.CODE,
                DATE                :   value.DATE,
                TYPE                :   value.TYPE,
                REMISE_PERCENT      :   value.REMISE_PERCENT,
                REMISE              :   value.REMISE,
                REMISE_TYPE         :   value.REMISE_TYPE,
                PAYMENT_TYPE        :   value.PAYMENT_TYPE,
                AUTHOR_NAME         :   value.AUTHOR_NAME,
                AUTHOR_ID           :   value.AUTHOR_ID,
                ID                  :   value.ID,
                items               :   [],
                payments            :   value.payments,
                refunds             :   value.refunds,
            }]))
        });

        _.each( $scope.rawData, function( _value ) {
            _.propertyOf( $scope.entries )( _value.CODE ).items.push({
                DESIGN          :   _value.DESIGN,
                QUANTITE        :   _value.QUANTITE,
                PRIX            :   _value.PRIX
            })
        });

        _.each( $scope.entries, function( value ) {

            // if payment_type is empty, just skip it.
            if( typeof $scope.paymentTypeDetails[ value.PAYMENT_TYPE ] === 'undefined' ) {
                return false;
            }

            $scope.paymentTypeDetails[ value.PAYMENT_TYPE ].nbr++;
            $scope.paymentTypeDetails[ value.PAYMENT_TYPE ].total   +=  parseFloat( value.TOTAL );
            // value.payments.forEach( payment => {
            // });

            if( angular.isUndefined( _.propertyOf( $scope.authors )( value.AUTHOR_ID ) ) ) {
                $scope.authors[ value.AUTHOR_ID ]   =   {
                    nbr     :   1,
                    name    :   value.AUTHOR_NAME,
                    total   :   parseFloat( value.TOTAL )
                };
            } else {
                _.propertyOf( $scope.authors )( value.AUTHOR_ID ).nbr++;
                _.propertyOf( $scope.authors )( value.AUTHOR_ID ).total    +=  parseFloat( value.TOTAL );
            }
        });
    }

    /**
     *  Calculate Total
     *  @param  object items
     *  @return int/float
    **/

    $scope.calculateTotal           =   function( items ) {
        var total       =   0;
        _.each( items, function( value ) {
            total       +=  parseFloat( value.PRIX );
        });

        return total;
    }

    /**
     *  Check Paid order
     *  @param  object orders
     *  @return object paid order details
    **/

    $scope.checkPaidOrders          =   function( entries ) {
        var paidOrders              =   0;
        var totalPaidAmount         =   0;

        _.each( entries, function( value ) {
            if( value.TYPE === 'nexo_order_comptant' ) {
                paidOrders++;
                totalPaidAmount     +=  parseFloat( value.TOTAL );
            }
        });

        return {
            nbr     :   paidOrders,
            total   :   totalPaidAmount
        }
    }

    /**
     * Check refundded orders 
     * @param object orders
     * @return object refunded orders
     */
    $scope.filtersOrders  =   ( entries, type, prop = 'TOTAL' ) => {
        const orders    =   entries.filter( order => order.TYPE === type );
        let total         =   0;
        let count       =   0;

        if ( orders.length > 0 ) {
            total       =   orders
                .map( order => {
                    return parseFloat( order[ prop ] );
                })
                .reduce( ( before, after ) => before + after );
            count       =   orders.length;
        }
        console.log( total, count );
        return { total, count };
    }

    /**
     *  Count Orders
     *  @param entries
     *  @return int/float
    **/

    $scope.countOrders              =   function( entries ){
        return _.keys( entries ).length
    }

    /**
     *  Fetch Detailed Report
     *  @param
     *  @return
    **/

    $scope.fetchDetailedReport      =   function() {
        $http.post( '<?php echo site_url( array( 'rest', 'nexo', 'sales_detailed' ) );?>' + '?<?php echo store_get_param( null );?>', {
            'start_date'    : angular.element( '[ng-model="startDate"]').val(),
            'end_date'      : angular.element( '[ng-model="endDate"]').val()
        },{
            headers			:	{
                '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo @$Options[ 'rest_key' ];?>'
            }
        }).then(function( returned ){
            $scope.resetPaymentTypeStats();
            let temp            =   [];
            $scope.rawOrders    =   returned.data.filter( order => {
                if( temp.indexOf( order.ID ) === -1 ) {
                    temp.push( order.ID );
                    return order;
                }
            })
            $scope.buildEntries( returned.data );
        });
    }

    /**
     *  Refresh Value
     *  @param
     *  @return
    **/

    $scope.refreshValue         =   function(){
        // console.log( 'ok' );
    }

    /**
     *  Reset Payment Type Stats
     *  @return
    **/

    $scope.resetPaymentTypeStats    =   function(){
        _.each( $scope.paymentType, function( value, key ) {
            $scope.paymentTypeDetails[ key ]        =   {
                name        :   value,
                nbr         :   0,
                total       :   0
            }
        });
    }

    /**
     *  Total order
     *  @param
     *  @return
    **/

    $scope.totalOrder           =   function( entries ) {
        var total       =   0;
        _.each( entries, function( value ) {
            total       +=   parseFloat( value.TOTAL );
        });

        return total;
    }

    /**
     * Total Payments
     * @return total payments
     */
    $scope.totalPayments        =   function() {
        let total   =   0;
        for( let namespace in $scope.paymentTypeDetails ) {
            total   +=  $scope.paymentTypeDetails[ namespace ].total;
        }
        return total;
    }
}]);


// Date Picker
$(function () {
	$('#datetimepicker6').datetimepicker({
		format	:	'YYYY-MM-DD HH:mm'
	});
	$('#datetimepicker7').datetimepicker({
		useCurrent: false, //Important! See issue #1075
		format	:	'YYYY-MM-DD HH:mm'
	});
	$("#datetimepicker6").on("dp.change", function (e) {
		$('#datetimepicker7').data("DateTimePicker").minDate(e.date);
	});
	$("#datetimepicker7").on("dp.change", function (e) {
		$('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
	});
});
</script>
