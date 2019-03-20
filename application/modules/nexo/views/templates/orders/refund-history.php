<div id="refund-history-wrapper" class="d-flex flex-column h-100 p-4" style="overflow-y: auto;">
    <div class="row">
        <div class="col-md-4 mb-4 col-lg-4 col-xl-2" v-for="history in histories">
            <div class="card">
                <div class="card-body" style="height: 150px">
                    <h4 class="card-title mb-0"><?php echo sprintf( __( 'Remboursement : %s' ), '{{ history.DATE_CREATION }}' );?></h4>
                    <small class="card-subtitle mb-2 text-muted">{{ getRefundType( history.TYPE ) }} &mdash; {{ history.author.name }}</small>
                    <p class="card-text"><?php echo sprintf( __( 'Raison : %s', 'nexo' ), '{{ history.DESCRIPTION }}' );?></p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><?php echo __( 'Sous Total', 'nexo' );?> <span class="pull-right">{{ history.SUB_TOTAL | moneyFormat }}</span></li>
                    <li class="list-group-item"><?php echo __( 'Livraison', 'nexo' );?> <span class="pull-right">{{ history.SHIPPING | moneyFormat }}</span></li>
                    <li class="list-group-item"><?php echo __( 'Total', 'nexo' );?> <span class="pull-right">{{ history.TOTAL | moneyFormat }}</span></li>
                    <li class="list-group-item p-2">
                        <bs4-button-toggle @clicked="submitPrintJob( $event, history )" label="<?php echo __( 'Imprimer le ticket', 'nexo' );?>" :options="printOptions"></bs4-button-toggle>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-12" v-if="histories.length === 0">
            <div class="alert alert-info"><?php echo __( 'Aucun remboursement n\'a été éffectué pour cette commande', 'nexo' );?></div>
        </div>
    </div>
</div>