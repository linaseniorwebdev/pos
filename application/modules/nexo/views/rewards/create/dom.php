<div id="reward-create-vue">
    <div class="row">
        <div class="col-md-6">
            <form action="" method="POST" role="form">    
                <div class="form-group">
                    <label for=""><?php echo __( 'Nom du système', 'nexo' );?></label>
                    <input v-model="reward_name" type="text" class="form-control" id="" placeholder="<?php echo __( 'Exemple : Recompense Clients Habituels', 'nexo' );?>">
                </div>   
                
                <div class="form-group">
                    <label><?php echo __( 'Assigner à un coupon', 'nexo' );?></label>
                    <select v-model="reward_coupon" type="text" class="form-control" placeholder="Amount">
                        <option :value="coupon.ID" v-for="coupon in coupons">{{ coupon.CODE }}</option>
                    </select>
                    <p class="help-block"><?php echo __( 'Une fois le nombre de point atteint, le coupon selectionné sera généré.', 'nexo' );?></p>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo __( 'Objectif en points', 'nexo' );?></div>
                        <input v-model="reward_target" type="number" class="form-control" placeholder="<?php echo __( 'Exemple : 300', 'nexo' );?>">
                    </div>
                    <p class="help-block"><?php echo __( 'Il faut déterminer ici le nombre de point à atteindre pour générer un coupon.', 'nexo' );?></p>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo __( 'Validité du coupon (en jours)', 'nexo' );?></div>
                        <input v-model="reward_expiration" type="number" class="form-control" placeholder="<?php echo __( 'Exemple : 10', 'nexo' );?>">
                    </div>
                    <p class="help-block"><?php echo __( 'Il faut déterminer ici après combien de jour le coupon généré expirera.', 'nexo' );?></p>
                </div>

                <div class="form-group" v-if="rules.length > 0" v-for="(rule, index) in rules">
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1"><?php echo __( 'Dépense Requise', 'nexo' );?></span>
                        <input v-model="rule.required_purchases" type="text" class="form-control" placeholder="<?php echo __( 'Achats Requis', 'nexo' );?>" aria-describedby="sizing-addon1">
                        <span class="input-group-addon" id="sizing-addon1"><?php echo __( 'Points Gagnés', 'nexo' );?></span>
                        <input v-model="rule.reward_points" type="text" class="form-control" placeholder="<?php echo __( 'Points Gagnés', 'nexo' );?>" aria-describedby="sizing-addon1">
                        <span class="input-group-btn">
                            <button @click="deleteRewardRule( index, rule )" class="btn btn-danger" type="button"><?php echo __( 'Supprimer', 'nexo' );?></button>
                        </span>
                    </div>
                </div>
                     
                <div class="btn-toolbar" role="toolbar" aria-label="...">
                    <div class="btn-group" role="group" aria-label="...">
                        <button @click="addRule()" type="button" class="btn btn-default"><?php echo __( 'Ajouter une règle', 'nexo' );?></button>
                    </div>
                    <div class="btn-group">
                        <button :disabled="isSubmitting" type="button" @click="saveReward()" class="btn btn-primary"><?php echo __( 'Enregistrer', 'nexo' );?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>