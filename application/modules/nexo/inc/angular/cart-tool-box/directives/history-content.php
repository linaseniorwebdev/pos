<script>
/***
 * Order History Content Wrapper
**/

tendooApp.directive( 'historyContent', function(){

	const domHTML 	=	`
	<div class="row row-container">
		<div class="col-lg-2  col-md-2 col-sm-4 col-xs-4 order-status bootstrap-tab-menu">
			<div class="list-group"><a class="list-group-item" ng-repeat="(key, val) in orderStatusObject" ng-class="{ active : val.active }"
					href="javascript:void(0)" ng-click="selectHistoryTab( key )" style="margin: 0px; border-radius: 0px; border-width: 0px 0px 1px 1px; border-style: solid; border-bottom-color: rgb(222, 222, 222); border-left-color: rgb(222, 222, 222); border-image: initial; border-top-color: initial; border-right-color: initial; padding-left: 30px;">{{
					val.title }}</a></div>
		</div>
		<div class="col-lg-4 col-md-4 col-sm-8 col-xs-8 middle-content" style="padding-left: 0px; padding-right: 0px;">
			<div class="input-group" style="padding: 5px; border-bottom: 1px solid rgb(238, 238, 238);">
				<span class="input-group-addon">Search</span>
				<input class="form-control" ng-model="search_order">
				<span class="input-group-btn search-buttons">
					<button class="btn btn-default proceed-search"
						ng-click="searchOrder()"><i class="fa fa-search"></i>
					</button>
					<button class="btn btn-default cancel-search"
					ng-click="cancelSearch()">
						<i class="fa fa-remove"></i></button>
				</span>
			</div>
			<div class="history-content-wrapper" ng-repeat="(key, val) in orderStatusObject" ng-show="orderStatusObject[ key ].active">
				<history-order-list object="loadedOrders[ key ]" open-order-details="openOrderDetails" namespace="{{ key }}"></history-order-list>
			</div>
			<the-spinner namespace="mspinner" spinner-obj="theSpinner"></the-spinner>
		</div>
		<div class="col-lg-6 hidden-sm hidden-xs order-details" style="border-left: 1px solid rgb(222, 222, 222); overflow-y: scroll;">
			<div class="order-details-wrapper row" ng-hide="theSpinner[ &quot;rspinner&quot; ]">
				<h3 class="text-center">Order Details</h3>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-6 col-md-6 text-left">
							<p class="details"><?php echo __( 'Auteur', 'nexo' );?> :<span class="details pull-right">{{ orderDetails.order.AUTHOR_NAME }}</span></p>
						</div>
						<div class="col-lg-6 col-md-6 text-left">
							<p class="details"><?php echo __( 'Date', 'nexo' );?> :<span class="details pull-right">{{ orderDetails.order.DATE_CREATION }}</span></p>
						</div>
						<div class="col-lg-6 col-md-6 text-left">
							<p class="details"><?php echo __( 'Client', 'nexo' );?> :<span class="details pull-right">{{ orderDetails.order.customer_name }}</span></p>
						</div>
						<div class="col-lg-6 col-md-6 text-left">
							<p class="details"><?php echo __( 'Code', 'nexo' );?> :<span class="details pull-right">{{ orderDetails.order.CODE }}</span></p>
						</div>
						<div class="col-lg-6 col-md-6 text-left">
							<p class="details"><?php echo __( 'Somme Due', 'nexo' );?> :<span class="details pull-right">{{ orderDetails.order.TOTAL -
									orderDetails.order.SOMME_PERCU | moneyFormat }}</span></p>
						</div>
					</div>
				</div>
				<h3 class="text-center">Product list</h3>
				<table class="table table-bordered table-striped order-details-table">
					<thead>
						<tr>
							<td>Item Name</td>
							<td>Unit Price</td>
							<td>Quantity</td>
							<td>Discount</td>
							<td>Sub Total</td>
						</tr>
					</thead>
					<tbody>
						<tr class="item-row" ng-repeat="item in orderDetails.items">
							<td class="text-left">{{ item.DESIGN || item.NAME }}</td>
							<td class="text-left">{{ item.PRIX | moneyFormat }}</td>
							<td class="text-left">{{ item.QUANTITE }}</td>
							<td class="text-left">{{ item.DISCOUNT_TYPE == "percentage" ? item.DISCOUNT_PERCENT :
								item.DISCOUNT_AMOUNT | moneyFormat }}</td>
							<td class="text-left">{{ item.PRIX * item.QUANTITE | moneyFormat }}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<the-spinner namespace="rspinner"></the-spinner>
		</div>
	</div>
	`

	return {
		restrict	: 	'E',
		template	:	domHTML
	};

});
</script>
