<div id="details-wrapper" class="d-flex flex-column h-100">
    <div class="card text-center d-flex h-100 border-0">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item" v-for="tab in tabs">
                    <a @click="selectTab( tab )" :class="{ 'active' : tab.active }" class="nav-link" href="#">{{ tab.title }}</a>
                </li>
            </ul>
        </div>
        <div class="card-body flex-fill d-flex flex-column" style="overflow-y: scroll;overflow-x: hidden;" v-if="activeTab.namespace === 'orders'">
            <div class="row" style="flex-shrink: 0;">
                <div class="col-md-6 text-left">
                    <div class="form-group">
                        <label><?php echo __( 'Etat', 'nexo' );?></label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <button :disabled="proceeding" @click="changeState()" class="btn btn-primary" type="button" id="button-addon1">
                                    <div v-if="proceeding" class="loader">Loading...</div>
                                    <?php echo __( 'Modifier l\'état', 'nexo' );?>
                                </button>
                            </div>
                            <select :disabled="proceeding" class="form-control" v-model="status">
                                <option v-for="option in options" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>                        
                        <small id="emailHelp" class="form-text text-muted"><?php echo __( 'Modifier l\'état actuel de la commande', 'nexo' );?></small>
                    </div>
                    <h4><?php echo __( 'Détails de la commande', 'nexo' );?></h4>
                    <ul class="list-group text-left">
                        <li class="list-group-item"><strong><?php echo __( 'Code', 'nexo' );?> :</strong> <span class="pull-right">{{ order.CODE }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Par', 'nexo' );?> :</strong> <span class="pull-right">{{ order.AUTHOR_NAME }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Status', 'nexo' );?> :</strong> <span class="pull-right">{{ getStatusText( order.STATUS ) }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Frais de Livraison', 'nexo' );?> :</strong> <span class="pull-right">{{ order.SHIPPING_AMOUNT | moneyFormat }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'TVA', 'nexo' );?> :</strong> <span class="pull-right">{{ order.TVA | moneyFormat }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Total', 'nexo' );?> :</strong> <span class="pull-right">{{ order.TOTAL | moneyFormat }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Somme Perçu', 'nexo' );?> :</strong> <span class="pull-right">{{ order.SOMME_PERCU | moneyFormat }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Statut du paiement', 'nexo' );?> :</strong> <span class="pull-right">{{ orderPaymentStatus( order.TYPE ) }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Crée le', 'nexo' );?> :</strong> <span class="pull-right">{{ order.DATE_CREATION }}</span></li>
                        <li class="list-group-item"><strong><?php echo __( 'Modifié le', 'nexo' );?> :</strong> <span class="pull-right">{{ order.DATE_MOD }}</span></li>
                    </ul>
                </div>
                <div class="col-md-6 text-left">
                    <div class="alert alert-danger" v-if="printersStatus === 'failed'"><?php echo __( 'Impossible de charger les imprimantes. Assurez-vous que Nexo Print Server soit correctement connecté', 'nexo' );?> &mdash; <a style="color: #ccc5ff;" @click="loadPrinters()" href="javascript:void()"><?php echo __( 'Reéssayer', 'nexo' );?></a></div>
                    <div class="alert alert-info" v-if="printersStatus === 'processing'"><?php echo __( 'Interaction avec Nexo Print Server en cours.', 'nexo' );?></div>
                    <div class="card">
                        <div class="card-header"><?php echo __( 'Produits', 'nexo' );?></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th><?php echo __( 'Produit', 'nexo' );?></th>
                                        <th class="text-right"><?php echo __( 'Prix Unitaire', 'nexo' );?></th>
                                        <th class="text-right"><?php echo __( 'Quantité', 'nexo' );?></th>
                                        <th class="text-right"><?php echo __( 'Remise', 'nexo' );?></th>
                                        <th class="text-right"><?php echo __( 'Total', 'nexo' );?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="hasLoaded && products.length == 0">
                                        <td scope="row" colspan="5"><?php echo __( 'Aucun produit disponible dans cette commande.', 'nexo' );?></td>
                                    </tr>
                                    <tr v-if="! hasLoaded">
                                        <td scope="row" colspan="5"><?php echo __( 'Chargement...', 'nexo' );?></td>
                                    </tr>
                                    <tr v-for="product in products">
                                        <td scope="row">{{ product.NAME || product.DESIGN }}</td>
                                        <td class="text-right">{{ product.PRIX | moneyFormat }}</td>
                                        <td class="text-right">{{ product.QUANTITE }}</td>
                                        <td class="text-right">{{ product.REMISE | moneyFormat }}</td>
                                        <td class="text-right">{{ product.PRIX_TOTAL | moneyFormat }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body border-top">
                            <bs4-button-toggle @clicked="submitPrintJob" :label="textDomain.print" :options="printOptions"></bs4-button-toggle>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body flex-fill d-flex flex-column" style="overflow-y: scroll;overflow-x: hidden;" v-if="activeTab.namespace === 'address'">
            <div class="row" style="flex-shrink: 0;">
                <div class="col-md-6 text-left">
                    <h3><?php echo __( 'Addresse de livraison', 'nexo' );?></h3>
                    <ul class="list-group" v-if="order.shipping">
                        <li v-for="address in addressInputs" class="list-group-item"><strong>{{ getShippingLabelName( address ) }} :</strong> {{ order.shipping[ address ] || textDomain.notSet }}</li>
                    </ul>
                    <div v-if="! order.shipping" class="alert alert-info"><?php echo __( 'Aucune information de livraison disponible pour cette commande', 'nexo' );?></div>
                </div>
                <div class="col-md-6"></div>
            </div>      
        </div>
        <div class="card-body" v-if="activeTab.namespace === false">
            <h3><?php echo __( 'Aucun onglet n\'a été sélectionné', 'nexo' );?></h3>
        </div>
    </div>
</div>