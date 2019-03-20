<div id="daily-report">
    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">
                        <?php echo __( 'Rapport sur les ventes', 'nexo_premium' );?>
                    </h3>

                    <div class="box-tools">
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <div class="input-group date sale_report_field" data-provide="datepicker" style="margin: 5px">
                        <input type="text" class="form-control">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>

                    <table class="table">
                        <tbody>
                            <tr>
                                <th>
                                    <?php echo __( 'Type', 'nexo_premium' );?>
                                </th>
                                <th>
                                    <?php echo __( 'Montant', 'nexo_premium' );?>
                                </th>
                                <th>
                                    <?php echo __( 'Indice', 'nexo_premium' );?>
                                </th>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Ventes Réalisées', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar progress-bar-danger" style="width: 55%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-red">55%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Ventes Impayées', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-success" style="width: 90%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-green">90%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Ventes Partielles', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-success" style="width: 90%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-green">90%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Remboursements', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-success" style="width: 90%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-green">90%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Remises Effectuées', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar progress-bar-yellow" style="width: 70%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-yellow">70%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Taxes Journalières', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-primary" style="width: 30%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light-blue">30%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">
                        <?php echo __( 'Autres Rapport', 'nexo_premium' );?>
                    </h3>

                    <div class="box-tools">
                        <ul class="pagination pagination-sm no-margin pull-right">
                            <li><a href="#">«</a></li>
                            <li><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li><a href="#">»</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>
                                    <?php echo __( 'Type', 'nexo_premium' );?>
                                </th>
                                <th>
                                    <?php echo __( 'Montant', 'nexo_premium' );?>
                                </th>
                                <th>
                                    <?php echo __( 'Indice', 'nexo_premium' );?>
                                </th>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Nouveaux Clients', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar progress-bar-danger" style="width: 55%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-red">55%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Caissier du jour', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar progress-bar-yellow" style="width: 70%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-yellow">70%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Client du jour', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-primary" style="width: 30%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light-blue">30%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Produit du jour', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-success" style="width: 90%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-green">90%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __( 'Dépenses Journalières', 'nexo_premium' );?>
                                </td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-success" style="width: 90%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-green">90%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>
</div>