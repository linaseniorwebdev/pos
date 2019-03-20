
<form action="" method="POST" ng-controller="StockTransferCTRL" name="stockForm">
    <div class="row" >
        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon"><?php echo __( 'Title', 'stock-manager' );?></div>
                    <input required name="order_title" type="text" ng-model="order.title" class="form-control"/>
                </div>
                <p class="help-block"><?php echo __( 'This will help you identifiy the transfert.', 'stock-manager' );?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">
                        <?php if( isset( $_GET[ 'request' ] ) && $_GET[ 'request' ] === 'true' ):?>
                            <?php echo __( 'Request To', 'stock-manager' );?>
                        <?php else:?>
                            <?php echo __( 'Send To', 'stock-manager' );?>
                        <?php endif;?>
                    </div>
                    <select required name="store_id" ng-options="store as store.NAME for store in stores track by store.ID" ng-model="order.store" type="text" class="form-control">
                    </select>
                </div>
                <?php if( isset( $_GET[ 'request' ] ) && $_GET[ 'request' ] === 'true' ):?>
                <p class="help-block"><?php echo __( 'Select from where the stock transfert should be requested.', 'stock-manager' );?></p>
                <?php else:?>
                <p class="help-block"><?php echo __( 'Select where you would like to send the transfert.', 'stock-manager' );?></p>
                <?php endif;?>
            </div>        
        </div>
        <div class="col-md-12">

            <div class="form-group">
                <div np-autocomplete="npAutocompleteOptions">
                    <input np-input-model="searchValue" ng-model-options="{ debounce : 500 }" type="text" class="search-input form-control input-lg barcode-field" placeholder="<?php echo __( 'Search a product using his name, barcode or SKU', 'stock-manager' );?>">
                </div>
            </div>            
            <div class="box">
                <div class="box-header"><?php echo __( 'Item List', 'stock-manager' );?></div>
                <div class="box-body no-padding">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <td><?php echo __( 'Item Name', 'stock-manager' );?></td>
                                <td><?php echo __( 'Price', 'stock-manager' );?></td>
                                <td width="150"><?php echo __( 'Quantity', 'stock-manager' );?></td>
                                <td width="200"><?php echo __( 'Total', 'stock-manager' );?></td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="item in order.items track by $index">
                                <td style="line-height: 35px;">{{ item.DESIGN }}</td>
                                <td style="line-height: 35px;">{{ item.PRIX_DACHAT | moneyFormat }}</td>
                                <td>
                                    <div class="input-group inpuut-group-sm">
                                        <span class="input-group-btn">
                                            <button ng-click="quantity( item, 'decrease' )" type="button" class="btn btn-default">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </span>
                                        <!--  -->
                                        <input ng-focus="watchItem( item )" ng-model-options="{ updateOn: 'blur' }" ng-change="checkChange( item )" ng-model="item.QTE_ADDED" type="text" class="form-control" id="exampleInputAmount" placeholder="Search">
                                        <span class="input-group-btn">
                                            <button ng-click="quantity( item, 'increase' )" type="button" class="btn btn-default">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </span>
                                    </div>
                                </td>
                                <td style="line-height: 35px;">{{ item.PRIX_DACHAT * item.QTE_ADDED | moneyFormat }}</td>
                                <td width="50">
                                    <button ng-click="remove( $index )" type="button" class="btn btn-sm btn-warning">
                                        <i class="fa fa-remove"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr ng-show="order.items.length == 0">
                                <td colspan="5" class="text-center"><?php echo __( 'No item has been added', 'stock-manager' );?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">
                    <button type="button" ng-show="isRequesting" ng-click="submitStock()" ng-class="{ 'disabled' : order.items.length == 0 }" class="btn btn-primary"><?php echo __( 'Request The Stock', 'stock-manager' );?></button>
                    <button type="button" ng-show="! isRequesting" ng-click="submitStock()" ng-class="{ 'disabled' : order.items.length == 0 }" class="btn btn-primary"><?php echo __( 'Send The Stock', 'stock-manager' );?></button>
                </div>
            </div>
            
        </div>
    </div>
</form>
<style>
.list-class {
    z-index:999;
}
</style>
