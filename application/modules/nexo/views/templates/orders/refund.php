<div id="refund-wrapper" class="p-3 h-100 d-flex flex-column" style="overflow-y: scroll;overflow-x: hidden;">
    <div class="row px-3 pt-3" style="position: absolute;">
        <div class="col-md-6">
            <button @click="refundMethod = ''" class="btn btn-secondary" v-if="! shouldShowRefundType">
                <i class="fa fa-arrow-left"></i>
                <?php echo __( 'Revenir en arrière', 'nexo' );?>
            </button>
        </div>
    </div>
    <div class="row h-100 justify-content-center">
        <div class="col-md-6 d-flex flex-row align-items-center">
            <div v-if="shouldShowRefundType">
                <div v-if="canProceed">
                    <h4 class="text-center display-4">
                        <?php echo __( 'Effectuer un remboursement ?', 'nexo' );?>
                    </h4>
                    <p class="lead text-center mb-4">
                        <?php echo sprintf( __( 'Veuillez choisir le type de remboursement que vous souhaitez effectuer. <a href="%s" target="_blank">En savoir plus</a>.', 'nexo' ), 'https://nexopos.com/divi-nexopos/' );?>
                    </p>
                </div>
                <div v-if="! canProceed && ! isLoading">
                    <h4 class="text-center display-4">
                        <?php echo __( 'Remboursement Impossible', 'nexo' );?>
                    </h4>
                    <p class="lead text-center mb-4">
                        <?php echo sprintf( __( 'Le remboursement n\'est possible que pour les commandes ayant reçu un paiement ou n\'ayant jamais été remboursées. <a href="%s" target="_blank">En savoir plus</a>.', 'nexo' ), 'https://nexopos.com/divi-nexopos/' );?>
                    </p>
                </div>
                <i v-if="isLoading" class="fa fa-refresh fa-spin nexo-refresh-icon" style="color: rgb(0, 0, 0); font-size: 50px; position: absolute; top: 50%; left: 50%; margin-top: -25px; margin-left: -25px; width: 44px; height: 50px;"></i>
                <div class="d-flex flex-row justify-content-around" v-if="canProceed">
                    <button :disabled="isStockWithRefund && hasRefund" @click="selectRefundType( 'no_stock_return' )" class="btn btn-lg btn-primary">
                        <i class="fa fa-money"></i>
                        <span>
                            <?php echo __( 'Sans retour de stock', 'nexo' );?></span>
                    </button>
                    <button :disabled="isStockLessRefund && hasRefund || isPartialOrder" @click="selectRefundType( 'with_stock_return' )" class="btn btn-lg btn-primary">
                        <i class="fa fa-truck"></i>
                        <span>
                            <?php echo __( 'Avec retour de stock', 'nexo' );?>
                        </span>
                    </button>
                </div>
                <br><br>
                <div class="alert alert-info" v-if="isStockLessRefund && canProceed">
                    <?php echo __( 'Cette commande a déjà reçu un remboursement sans retour de stock. Elle ne peut plus recevoir un remboursement avec retour de stock', 'nexo' );?>
                </div>
                <div class="alert alert-info" v-if="isStockWithRefund && canProceed">
                    <?php echo __( 'Cette commande a déjà reçu un remboursement avec retour de stock. Elle ne peut plus recevoir un remboursement sans retour de stock', 'nexo' );?>
                </div>
                <div class="alert alert-info" v-if="isPartialOrder">
                    <?php echo __( 'Impossible d\'effectuer une retour de stock pour une commande incomplete. Veuillez utiliser le remboursement sans retour de stock', 'nexo' );?>
                </div>
            </div>
            <div v-if="isWithoutStockReturn">
                <h4 class="text-center display-4">
                    <?php echo __( 'Définir le montant ?', 'nexo' );?>
                </h4>
                <p class="lead text-center mb-4">
                    <?php echo __( 'Vous pouvez choisir de rembourser entièrement la commande ou définir un montant fixe.', 'nexo' );?>
                </p>
                <div class="d-flex flex-row justify-content-around">
                    <div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">
                                <?php echo __( 'Type de Paiement', 'nexo' );?></label>
                            <div class="input-group">
                                <select v-model="payment_type" class="form-control">
                                    <option v-for="payment in paymentsGateway" :value="payment.namespace">{{ payment.label }}</option>
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                <?php echo __( 'Définir par quel passerelle de paiement le remboursement sera effectué.', 'nexo' );?></small>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">
                                <?php echo __( 'Remboursement Intégrale', 'nexo' );?></label>
                            <div class="input-group">
                                <input :value="rightTotalAmount" readonly type="text" class="form-control" name="name" id="name" placeholder="" aria-label="">
                            </div>
                            <small class="form-text text-muted">
                                <?php echo __( 'Le remboursement sera égale à la valeur de la commande. Si la commande est partiellement payée, le remboursement sera égale à la somme déjà perçu.', 'nexo' );?></small>
                        </div>
                        <div>
                            <button :disabled="isLoading" @click="proceedToFullRefund()" class="btn btn-primary" type="button" aria-label="">
                                <div v-if="isLoadingFullRefund" class="loader">Loading...</div>
                                <?php echo __( 'Remboursement Intégrale', 'nexo' );?></button>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label for="exampleInputEmail1">
                                <?php echo __( 'Remboursement Manuel', 'nexo' );?></label>
                            <div class="input-group">
                                <input @change="checkAmountToRefund()" type="text" class="form-control" v-model="amountToRefund" placeholder="" aria-label="">
                            </div>
                            <small class="form-text text-muted">
                                <?php echo __( 'Le montant ne peut pas être supérieure à la valeur de la commande', 'nexo' );?></small>
                        </div>
                        <div>
                            <button :disabled="isLoading" @click="proceedToPartialRefund()" class="btn btn-primary" type="button" aria-label="">
                                <div v-if="isLoadingPartialRefund" class="loader">Loading...</div>
                                <?php echo __( 'Remboursement Manuel', 'nexo' );?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="isWithStockReturn">
                <h4 class="text-center display-4">
                    <?php echo __( 'Remboursement avec retour de stock', 'nexo' );?>
                </h4>
                <p class="lead text-center mb-4">
                    <?php echo __( 'Veuillez choisir les produits qui feront un retour de stock. Vous pouvez définir si le produit est endommagé ou en bonne condition.', 'nexo' );?>
                </p>
                <div class="form-group">
                    <label for="exampleInputEmail1">
                        <?php echo __( 'Type de Paiement', 'nexo' );?></label>
                    <div class="input-group">
                        <select v-model="payment_type" class="form-control">
                            <option v-for="payment in paymentsGateway" :value="payment.namespace">{{ payment.label }}</option>
                        </select>
                    </div>
                    <small class="form-text text-muted">
                        <?php echo __( 'Définir par quel passerelle de paiement le remboursement sera effectué.', 'nexo' );?></small>
                </div>
                <div class="input-group mb-3">
                    <select v-model="product" name="" id="" class="form-control">
                        <option v-if="orderProducts.length > 0" value=""><?php echo __( 'Veuillez choisir un produit', 'nexo' );?></option>
                        <option v-if="orderProducts.length === 0"><?php echo __( 'Aucun Produit Disponible', 'nexo' );?></option>
                        <option v-for="product in orderProducts" :value="product">{{ product.NAME }}</option>
                    </select>
                    <select v-model="addItemStatus" name="" id="" class="form-control">
                        <option value=""><?php echo __( 'Veuillez choisir l\'état du produit', 'nexo' );?></option>
                        <option value="defective"><?php echo __( 'Etat défectueux', 'nexo' );?></option>
                        <option value="inGoodCondition"><?php echo __( 'En bon état', 'nexo' );?></option>
                    </select>
                    <div class="input-group-prepend" id="button-addon3">
                        <button @click="addToRefundCart()" class="btn btn-outline-secondary" type="button"><?php echo __( 'Ajouter le produit', 'nexo' );?></button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered m-0">
                                <thead class="thead-default">
                                    <tr>
                                        <th ><?php echo __( 'Produits', 'nexo' );?></th>
                                        <th width="120"><?php echo __( 'Etat', 'nexo' );?></th>
                                        <th width="120"><?php echo __( 'Prix', 'nexo' );?></th>
                                        <th width="130"><?php echo __( 'Quantité', 'nexo' );?></th>
                                        <th width="120"><?php echo __( 'Prix Total', 'nexo' );?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in refundCartItems">
                                            <td scope="row">{{ item.NAME }}</td>
                                            <td scope="row">{{ getItemStateHumanName( item.refund_state ) }}</td>
                                            <td>{{ item.PRIX }}</td>
                                            <td>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <button @click="decrease( item )" class="btn btn-outline-secondary" type="button" id="button-addon1"><i class="fa fa-minus"></i></button>
                                                    </div>
                                                    <input disabled type="text" :value="item.refund_quantity" class="form-control" placeholder="" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                                    <div class="input-group-append">
                                                        <button @click="increase( item )" class="btn btn-outline-secondary" type="button" id="button-addon1"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ getTotalRefundItemPrice( item ) | moneyFormat }}</td>
                                        </tr>
                                        <tr v-if="refundCartItems.length === 0">
                                            <td colspan="5"><?php echo __( 'Aucune produit ajouté', 'nexo' );?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"><?php echo __( 'Rembourser les frais de livraison', 'nexo' );?> <input @change="toggleShippingFees()" v-model="refundShippingFees" type="checkbox" name="" id=""></td>
                                            <td>{{ shippingFees | moneyFormat }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td><?php echo __( 'Total', 'nexo' );?></td>
                                            <td>{{ getOverallRefundAmount() | moneyFormat }}</td>
                                        </tr>
                                    </tfoot>
                            </table>
                        </div>
                        <button @click="proceedRefundWithStock()" class="btn btn-primary m-2"><?php echo __( 'Effectuer le remboursement', 'nexo' );?></button>
                    </div>
                </div>
                <br>
                <div class="alert alert-info" v-if="greatherThanAvailable"><?php echo __( 'Le remboursement ne peut pas excéder le montant payé par le client. Dans une telle situation, la valeur du remboursement sera égale à celle payée par le client. <a href="%s" target="_blank">En savoir plus</a>', 'nexo' );?></div>
            </div>
        </div>
    </div>
</div>