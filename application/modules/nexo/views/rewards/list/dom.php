<div id="reward-system-vue">
    <div class="input-group">
        <span class="input-group-btn" v-if="search.length > 0">
            <button @click="search = ''; getRewardSystem()" type="button" class="btn btn-danger">
                <i class="fa fa-remove"></i>
                <?php echo __( 'Annuler', 'nexo' );?></button>
        </span>
        <input v-on:keyup.enter="searchTerm()" type="text" v-model="search" class="form-control" id="exampleInputAmount" placeholder="Search">
        <span class="input-group-btn">
            <button @click="searchTerm()" type="button" class="btn btn-default">
                <?php echo __( 'Rechercher', 'nexo' );?></button>
            <button @click="create()" type="button" class="btn btn-default">
                <?php echo __( 'Créer', 'nexo' );?></button>
        </span>
    </div>
    <br>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">
                <?php echo __( 'Liste des systèmes de récompenses', 'nexo' );?>
            </h3>

            <div class="box-tools">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <li v-if="page_numbers.length > 0"><a @click="getRewardSystem( 1 )" href="javascript:void(0)">«</a></li>
                    <li v-if="
                        ( page >= result.current_page - 4 && page <= result.current_page ) ||
                        ( page === result.current_page ) || 
                        ( page <= result.current_page + 4 && page >= result.current_page )
                    " 
                        :class="{ 'active' : page === result.current_page }"
                        v-for="page in page_numbers">
                        <a  href="javascript:void(0)" @click="getRewardSystem( page )">{{ page }}</a>
                    </li>
                    <li v-if="page_numbers.length > 0"><a @click="getRewardSystem( result.total_pages )" href="javascript:void(0)">»</a></li>
                </ul>
                
                <div class="btn-group btn-group-sm">
                    <button @click="deleteSelected()" type="button" class="btn btn-danger"><?php echo __( 'Supprimer', 'nexo' );?></button>
                </div>
                
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body no-padding">
            <table class="table">
                <tbody>
                    <tr>
                        <th width="15px">
                            <input type="checkbox" class="icheck bulk-check" name="iCheck">
                        </th>
                        <th>
                            <?php echo __( 'Titre', 'nexo' );?>
                        </th>
                        <th>
                            <?php echo __( 'Coupon', 'nexo' );?>
                        </th>
                        <th>
                            <?php echo __( 'Auteur', 'nexo' );?>
                        </th>
                        <th>
                            <?php echo __( 'Date', 'nexo' );?>
                        </th>
                        <th style="width: 40px">
                            <?php echo __( 'Actions', 'nexo' );?>
                        </th>
                    </tr>
                    <tr v-if="entries.length === 0">
                        <td colspan="6"><?php echo __( 'Aucune entrée à afficher', 'nexo' );?></td>
                    </tr>
                    <tr v-for="(entry, index) in entries">
                        <td><input :data-id="index" @change="updateField( entry, index )" :checked="entry.selected" type="checkbox" class="icheck" name="iCheck"></td>
                        <td>{{ entry.NAME }}</td>
                        <td>{{ entry.coupon.CODE }}</td>
                        <td>{{ entry.author.name }}</td>
                        <td>{{ entry.DATE_CREATION }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <?php echo __( 'Options', 'nexo' );?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" aria-labelledby="dropdownMenu1">
                                    <li><a :href="'<?php echo dashboard_url([ 'rewards-system', 'edit' ]);?>/' + entry.ID"><?php echo __( 'Modifier', 'nexo' );?></a></li>
                                    <li @click="deleteEntry( entry, index )"><a href="#"><?php echo __( 'Supprimer', 'nexo' );?></a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>