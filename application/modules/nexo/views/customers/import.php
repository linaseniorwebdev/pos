<div ng-controller="customersImportCTRL">
    <div class="row">
        <div class="col-md-3">
            <h3><?php echo __( 'Colonnes disponibles', 'nexo' );?></h3>
            <p ng-show="fileColumns.length === 0"><?php echo __( 'Aucune colonne disponible. Veuillez choisir un fichier à importer', 'nexo' );?></p>
            <div class="form-group" ng-repeat="( index, fileColumn ) in fileColumns">
                <label for="exampleInputEmail1">{{ fileColumn }}</label>
                <select ng-change="watchChange( firstRawModel, index )" ng-model="firstRawModel[ index ]" type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
                    <option value=""><?php echo __( 'Pas assigné', 'nexo' );?></option>
                    <option value="{{ column.value }}" ng-repeat="column in supportedColumnsArray">{{ column.label }} &mdash; {{ usedColumns[ column.value ] ? '<?php echo _s( 'Utilisé', 'nexo' );?>' : '<?php echo _s( 'Pas Utilisé', 'nexo' );?>' }}</option>
                <select>
            </div>
        </div>
        <div class="col-md-9">
            <h3><?php echo __( 'Comment importer des clients', 'nexo' );?></h3>
            <p><?php echo __( 'Il n\'existe pas de template à suivre pour importer des clients. 
            Tout ce dont vous avez besoin, c\'est d\'importer un fichier compatible. 
            NexoPOS se chargera d\'afficher les colonnes disponibles pour vous. 
            Il ne vous restera plus qu\'a faire des correspondances.', 'nexo' )
            ?></p>
            <div class="form-group">
                <label for="exampleInputEmail1"><?php echo __( 'Fichier à importer', 'nexo' );?></label>
                <input type="file" class="form-control" id="exampleInputEmail1" placeholder="Email">
            </div>
            <div class="row">
                <div class="col-md-4">
                <!-- <div class="checkbox">
                    <label>
                        <input type="checkbox" value="false" ng-model="overwrite"> <?php echo __( 'Conserver les clients avec la même adresse email', 'nexo' );?>
                    </label>
                </div> -->
                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="true" ng-model="empty"> <?php echo __( 'Vider la table des clients avant l\'importation', 'nexo' );?>
                    </label>
                </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4"></div>
            </div>
            <button ng-show="isPosting" class="btn btn-primary" disabled><?php echo __( 'En cours...', 'nexo' );?></button>
            <button ng-show="! isPosting" class="btn btn-primary" ng-click="import()"><?php echo __( 'Importer les clients', 'nexo' );?></button>
        </div>
    </div>
</div>
