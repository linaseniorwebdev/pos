<div  ng-controller="invoiceCTRL">
	<div style="background:#FFF;padding:15px;box-shadow:0 0 1px #333" class="invoice-container">
		<!-- title row -->
		<div class="row">
			<div class="col-xs-12">
			</div>
			<!-- /.col -->
		</div>
		<!-- info row -->
		<div class="row invoice-info">
			<div class="col-lg-12 col-xs-12 col-sm-12 col-md-12">
				<?php if( store_option( 'url_to_logo' ) != null ):?>
					<div class="text-center">
						<img src="<?php echo store_option( 'url_to_logo' );?>" 
						style="display:inline-block;<?php echo store_option( 'logo_height' ) != null ? 'height:' . store_option( 'logo_height' ) . 'px' : '';?>
						;<?php echo store_option( 'logo_width' ) != null ? 'width:' . store_option( 'logo_width' ) . 'px' : '';?>"/>
					</div>
				<?php else:?>
					<h2 class="text-center"><?php echo store_option( 'site_name' );?></h2>
				<?php endif;?>
			</div>
			<div class="col-sm-4 col-xs-4">
				<strong><?php echo __( 'Addresse de facturation', 'nexo' );?></strong><br>
				<address>
					<p style="margin:0"><strong><?php echo __( 'Client :', 'nexo' );?></strong> {{ data.order[0].customer_name }}</p>
					<p style="margin:0"><strong><?php echo __( 'Téléphone', 'nexo' );?>:</strong> {{ billing.phone }}</p>
					<p style="margin:0"> <strong><?php echo __( 'Email', 'nexo' );?>:</strong> {{ billing.email || '<?php echo __( 'Non Disponible' );?>' }}</p>
					<p style="margin:0" ng-if="billing.address_1 != null && billing.address_1 != ''"><strong><?php echo __( 'Address 1 :', 'nexo' );?></strong> {{ billing.address_1 ? billing.address_1 + ',' : '' }}</p>
					<p style="margin:0" ng-if="billing.address_2 != null && billing.address_2 != ''"><strong><?php echo __( 'Address 2', 'nexo' );?></strong> {{ billing.address_2 ? billing.address_2 + ',' : '' }}</p>
					<p style="margin:0" ng-if="billing.city != null && billing.city != ''"><strong><?php echo __( 'Ville :', 'nexo' );?></strong> {{ billing.city ? billing.city + ',' : '' }}</p>
					<p style="margin:0" ng-if="billing.pobox != null && billing.pobox != ''"><strong><?php echo __( 'Boite Postale :', 'nexo' );?></strong> {{ billing.pobox ? billing.pobox + ',' : '' }}</p>
				</address>
			</div>

			<div class="col-sm-4 col-xs-4">
				<b>
					<?php echo __( 'Facture N°:', 'nexo' );?> </b>{{ ( "00000" + data.order[0].ID ).slice(-6) }}
				<br/>
				<b>
					<?php echo __( 'Code :', 'nexo' );?>
				</b> {{ data.order[0].CODE }}
				<br/>
				<b><?php echo __( 'Date', 'nexo' );?>:</b> {{ data.order[0].DATE_CREATION | date }}
			</div>

			<div class="col-sm-4 col-xs-4">
				<strong><?php echo __( 'Addresse de livraison', 'nexo' );?></strong><br>
				<address>
					<p style="margin:0" ><strong><?php echo __( 'Client :', 'nexo' );?></strong> {{ data.order[0].customer_name }}</p>
					<p style="margin:0" ><strong><?php echo __( 'Téléphone', 'nexo' );?>:</strong> {{ shipping.phone }}</p>
					<p style="margin:0" ><strong><?php echo __( 'Email', 'nexo' );?>:</strong> {{ shipping.email }}</p>
					<p style="margin:0"  ng-if="shipping.address_1 != null && shipping.address_1 != ''"><strong><?php echo __( 'Address 1 :', 'nexo' );?></strong> {{ shipping.address_1 ? shipping.address_1 + ',' : '' }}</p>
					<p style="margin:0"  ng-if="shipping.address_2 != null && shipping.address_2 != ''"><strong><?php echo __( 'Address 2', 'nexo' );?></strong> {{ shipping.address_2 ? shipping.address_2 + ',' : '' }}</p>
					<p style="margin:0"  ng-if="shipping.city != null && shipping.city != ''"><strong><?php echo __( 'Ville :', 'nexo' );?></strong> {{ shipping.city ? shipping.city + ',' : '' }}</p>
					<p style="margin:0"  ng-if="shipping.pobox != null && shipping.pobox != ''"><strong><?php echo __( 'Boite Postale :', 'nexo' );?></strong> {{ shipping.pobox ? shipping.pobox + ',' : '' }}</p>
				</address>
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->

		<!-- Table row -->
		<div class="row">
			<div class="col-xs-12 table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>
								<?php echo __( 'Produit', 'nexo' );?>
							</th>
							<th>
								<?php echo __( 'Prix', 'nexo' );?>
							</th>
							<th>
								<?php echo __( 'Remise', 'nexo' );?>
							</th>
							<th>
								<?php echo __( 'Quantité', 'nexo' );?>
							</th>
							<th>
								<?php echo __( 'Total', 'nexo' );?>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat="item in data.products">
							<td>{{ item.DESIGN }}</td>
							<td>{{ item.PRIX_BRUT | moneyFormat }}</td>
							<td>{{ '-' + ( item.PRIX_BRUT - item.PRIX ) | moneyFormat }}</td>
							<td>{{ item.QUANTITE }}</td>
							<td>{{ item.PRIX * item.QUANTITE | moneyFormat }}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->

		<div class="row">
			<!-- accepted payments column -->
			<div class="col-xs-6">
				<div class="table-responsive">
					<table class="table">
						<tr>
							<th><?php echo __( 'Sous-Total', 'nexo' );?></th>
							<th class="text-right">{{ subTotal( data.products ) | moneyFormat }}</th>
						</tr>
						<?php if ( in_array( store_option( 'nexo_vat_type' ), [ 'fixed', 'variable' ] ) ):?>
							<?php if( store_option( 'nexo_vat_type' ) == 'fixed' ):?>
								<th><?php echo sprintf( __( 'TVA (%s%%)', 'nexo' ), store_option( 'nexo_vat_percent' ) );?></th>
								<td class="text-right">{{ data.order[0].TVA | moneyFormat }}</td>
							<?php else:?>
								<?php if ( @$tax[0][ 'NAME' ] != null ):?>
								<th><?php echo sprintf( __( '%s (%s%%)', 'nexo' ), @$tax[0][ 'NAME' ], @$tax[0][ 'RATE' ] );?></th>
								<td class="text-right">{{ data.order[0].TVA | moneyFormat }}</td>
								<?php endif;?>
							<?php endif;?>
						<?php endif;?>
						<tr ng-show="getDiscount() != 0">
							<th><?php echo __( 'Remise', 'nexo' );?>:</th>
							<td class="text-right">{{ getDiscount() | moneyFormat }}</td>
						</tr>
						<tr ng-show="data.order[0].TVA != '0'">
							<th><?php echo __( 'TVA', 'nexo' );?></th>
							<th class="text-right">{{ data.order[0].TVA | moneyFormat }}</th>
						</tr>
						<tr>
							<th><?php echo __( 'Livraison', 'nexo' );?>:</th>
							<td class="text-right">{{ data.order[0].SHIPPING_AMOUNT | moneyFormat }}</td>
						</tr>
						<tr>
							<th><?php echo __( 'Total', 'nexo' );?></th>
							<td class="text-right">{{ total() | moneyFormat }}</td>
						</tr>
						<tr ng-show="totalRefund > 0">
							<th><?php echo __( 'Remboursement', 'nexo' );?></th>
							<th class="text-right">{{ - totalRefund | moneyFormat }}</th>
						</tr>
						<tr>
							<th><?php echo __( 'Somme Perçu', 'nexo' );?></th>
							<th class="text-right">{{ data.order[0].SOMME_PERCU - totalRefund | moneyFormat }}</th>
						</tr>
						<tr>
							<th><?php echo __( 'A rendre', 'nexo' );?></th>
							<th class="text-right">{{ toRepay() | moneyFormat }}</th>
						</tr>
					</table>
				</div>
			</div>
			<div class="col-xs-6">
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->

		<!-- this row will not appear when printing -->
		<div class="row no-print hidden-print">
			<div class="col-xs-12">
				<a href="javascript:void(0)" print-item=".invoice-container" class="btn btn-default">
					<i class="fa fa-print"></i> <?php echo __( 'Imprimer', 'nexo' );?></a>
			</div>
		</div>
	</div>
</div>