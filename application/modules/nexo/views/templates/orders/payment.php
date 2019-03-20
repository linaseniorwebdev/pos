<div id="payment-wrapper" class="p-3 h-100 d-flex flex-row">
    <i v-if="isLoading" class="fa fa-refresh fa-spin nexo-refresh-icon" style="color: rgb(0, 0, 0); font-size: 50px; position: absolute; top: 50%; left: 50%; margin-top: -25px; margin-left: -25px; width: 44px; height: 50px;"></i>
    <div v-if="! isLoading" class="row m-0 p-0 d-flex flex-row w-100">
        <div class="col-lg-6">
            <div  v-if="isLoading">
                <h4 class="text-center mb-3"><?php echo __( 'Veuillez choisir le moyen de paiement', 'nexo' );?></h4>
                <div class="alert alert-info">
                    <?php echo __( 'Chargement...', 'nexo' );?>
                </div>
            </div>
            <div v-if="! isLoading && ! canProceed">
                <h4 class="text-center mb-3"><?php echo __( 'Veuillez choisir le moyen de paiement', 'nexo' );?></h4>
                <div class="alert alert-warning">
                    <?php echo __( 'Cette commande n\'a plus besoin de recevoir de paiement', 'nexo' );?>
                </div>
            </div>
            <div v-if="! isLoading && canProceed">
                <h4 class="text-center mb-3"><?php echo __( 'Veuillez choisir le moyen de paiement', 'nexo' );?></h4>
                <div class="form-group">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01"><?php echo __( 'Moyen de paiement', 'nexo' );?></label>
                        </div>
                        <select v-model="gateway" class="custom-select" id="inputGroupSelect01">
                            <option v-for="gateway in gateways" :value="gateway.value">{{ gateway.label }}</option>
                        </select>                
                    </div>
                    <small class="form-text text-muted"><?php echo __( 'Veuillez choisir le moyen de paiement utilisé', 'nexo' );?></small>
                </div>
                <div class="form-group">
                    <label for="amount"><?php echo __( 'Montant', 'nexo' );?></label>
                    <div class="input-group mb-3">
                        <input type="text" v-model="amount" class="amount-field form-control" placeholder="<?php echo __( 'Veuillez spécifier le montant', 'nexo' );?>">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon1">{{ order.TOTAL - order.SOMME_PERCU | moneyFormat }}</span>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?php echo __( 'Le montant sera utilisé comment paiement', 'nexo' );?></small>
                </div>
                <button @click="proceedPayment()" :disabled="! amountIsValid || isLoading" type="button" class="btn btn-primary">
                    <div v-if="isSubmitting" class="loader">Loading...</div>
                    <?php echo __( 'Effectuer le paiement', 'nexo' );?>
                </button>
            </div>
            <hr>
            <!-- <button v-if="! isLoading" class="btn btn-primary"><?php echo __( 'Imprimer la facture', 'nexo' );?></button> -->
        </div>
        <div class="col-md-6" style="overflow-y: scroll;">
            <h4 class="text-center mb-3"><?php echo __( 'Historique des paiements', 'nexo' );?></h4>
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo __( 'Montant', 'nexo' );?></th>
                        <th><?php echo __( 'Operation', 'nexo' );?></th>
                        <th><?php echo __( 'Date', 'nexo' );?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="payment in paymentsHistory">
                        <td scope="row">{{ payment.MONTANT | moneyFormat }} <br> <small>{{ getPaymentHumanName( payment.PAYMENT_TYPE ) }}</small></td>
                        <td>{{ getOperationHumanName( payment.OPERATION ) }}</td>
                        <td>{{ payment.DATE_CREATION }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>{{ getTotal( paymentsHistory ) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>