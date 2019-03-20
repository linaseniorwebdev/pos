<div id="history-vue" style="display: none">
    <div class="box">
        <div class="box-header with-border">
            <?php echo __( 'Historique', 'nexo' );?>
        </div>
        <div class="box-body no-padding">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input v-model="check" type="checkbox" name="checked"></th>
                        <th><?php echo __( 'Titre', 'nexo' );?></th>
                        <th><?php echo __( 'Action', 'nexo' );?></th>
                        <th width="200"><?php echo __( 'Effectué le', 'nexo' );?></th>
                        <th width="100"><?php echo __( 'Action', 'nexo' );?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr @click="entry.checked = ! entry.checked" v-for="entry in result.entries">
                        <td>
                            <input :checked="entry.checked" type="checkbox" name="" id="">
                        </td>
                        <td v-html="entry.TITRE"></td>
                        <td v-html="entry.DETAILS"></td>
                        <td>{{ entry.DATE_CREATION }}</td>
                        <td><a href="javascript:void(0)" @click="deleteSingle( entry )" class="btn btn-xs btn-primary"><?php echo __( 'Supprimer', 'nexo' );?> <i class="fa fa-remove"></i></a></td>
                    </tr>
                    <tr v-if="result.entries.length === 0">
                        <td colspan="5"><?php echo __( 'Aucune entrer à afficher', 'nexo' );?></td>
                    </tr>
                </tbody>
                <tfoot v-if="result.entries.length !== 0 && canDeleteAll">
                    <tr>
                        <td colspan="4">
                            <a v-if="canDeleteAll" class="btn btn-xs btn-danger" href="javascript:void(0)" @click="deleteSelected()"><?php echo __( 'Supprimer les séléctionnés', 'nexo' );?></a>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination" style="margin:0">
            <li  @click="selectPage( 1 )">
                <a href="javascript:void(0)" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li v-show="Math.abs( page - currentPage) < 3 || page == result.total_pages - 1 || page == 0" v-for="page in totalPages" @click="selectPage( page + 1 )"><a href="javascript:void(0)">{{ page + 1 }}</a></li>
            <li  @click="selectPage( result.total_pages )">
                <a href="javascript:void(0)" aria-label="Previous">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
<script>
const HistoryVueData = {
    url : {
        getAll: '<?php echo site_url([ 'api', 'nexopos', 'history' ]);?>',
        deleteSelected: '<?php echo site_url([ 'api', 'nexopos', 'delete_history' ]);?>'
    },
    textDomain: {
        deleteSelectedTitle: '<?php echo _s( 'Confirmez votre action', 'nexo' );?>',
        deleteSelectedText: `<?php echo _s( 'Souhaitez-vous supprimer les différents éléments qui sont sélectionnés ?', 'nexo' );?>`,
        deleteSingleTitle: '<?php echo _s( 'Confirmez votre action', 'nexo' );?>',
        deleteSingleText: `<?php echo _s( 'Souhaitez-vous supprimer cette entrée ? Cette action est irreversible.', 'nexo' );?>`
    }
}
</script>
<script src="<?php echo module_url( 'nexo' ) . '/js/history.vue.js';?>"></script>