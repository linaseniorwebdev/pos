<?php
    // $this->set_css($this->default_theme_path.'/bootstrap/css/flexigrid.css');
    $this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);

    $this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.noty.js');
    $this->set_js_lib($this->default_javascript_path.'/jquery_plugins/config/jquery.noty.config.js');
    $this->set_js_lib($this->default_javascript_path.'/common/lazyload-min.js');

    if (!$this->is_IE7()) {
        $this->set_js_lib($this->default_javascript_path.'/common/list.js');
    }

    $this->set_js($this->default_theme_path.'/bootstrap/js/cookies.js');
    $this->set_js($this->default_theme_path.'/bootstrap/js/flexigrid.js');

    $this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.form.min.js');

    $this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.numeric.min.js');
    $this->set_js($this->default_theme_path.'/bootstrap/js/jquery.printElement.min.js');

    /** Fancybox */
    $this->set_css($this->default_css_path.'/jquery_plugins/fancybox/jquery.fancybox.css');
    $this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.fancybox-1.3.4.js');
    $this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.easing-1.3.pack.js');

    /** Jquery UI */
    $this->load_js_jqueryui();
?>

<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';

	var subject = '<?php echo addslashes($subject); ?>';
	var ajax_list_info_url = '<?php echo $ajax_list_info_url; ?>';
	var unique_hash = '<?php echo $unique_hash; ?>';
    var ajax_list_url   =   '<?php echo $ajax_list_url;?>';
    var bulk_delete_url   =   '<?php echo $bulk_delete_url;?>';
    var base_url        =   '<?php echo substr( $ajax_list_url, 0, strlen( $ajax_list_url ) - 9 );?>';

	var message_alert_delete = "<?php echo $this->l('alert_delete'); ?>";

</script>

<div id='list-report-error' class='report-div error'></div>
<div id='list-report-success' class='report-div success report-list' <?php if ($success_message !== null) { ?>style="display:block"<?php 
    }?>>
    <?php
    if ($success_message !== null) {
        ?>
        <p><?php echo tendoo_success($success_message);
        ?></p>
        <?php 
    }
    ?>
</div>
<div class="flexigrid" style='width: 100%;' data-unique-hash="<?php echo $unique_hash; ?>">
    <div id="hidden-operations" class="hidden-operations"></div>
    <!--<div class="mDiv">
        <div class="ftitle"> &nbsp; </div>
        <div title="<?php echo $this->l('minimize_maximize');?>" class="ptogtitle"> <span></span> </div>
    </div>--> 
    <?php echo form_open($ajax_list_url, 'method="post" id="filtering_form" class="filtering_form" autocomplete = "off" data-ajax-list-info-url="'.$ajax_list_info_url.'"'); ?>
    <div id='main-table-box' class="box">
        <div class="box-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-12">
                    <?php if (!$unset_add || !$unset_export || !$unset_print):?>
                    <div class="btn-group btn-group-sm" role="group" aria-label="...">
                        <?php if (!$unset_add):?>
                        <a href='<?php echo get_instance()->events->apply_filters('grocery_add_url', $add_url);
                        ?>' title='<?php echo get_instance()->events->apply_filters('grocery_add_text', $this->l('list_add'));
                        ?> <?php echo $subject?>' class='btn btn-default add-anchor add_button'> <?php echo $this->l('list_add');
                        ?> <?php echo $subject?> </a>
                        <?php endif;?>
                        <?php if (!$unset_export):?>
                        <a class="export-anchor btn btn-default" data-url="<?php echo $export_url;?>" target="_blank">
                            <div class="fbutton">
                                <div> <span class="export"><?php echo $this->l('list_export');
                    ?></span> </div>
                            </div>
                        </a>
                        <a class="export-anchor btn btn-default" data-url="<?php echo $export_csv_url;?>" target="_blank">
                            <span class="export"><?php echo __( 'Export as CSV', 'grocerycrud' );?></span>
                        </a>
                        <?php endif;?>
                        <a class="pReload btn btn-default ajax_refresh_and_loading" id='ajax_refresh_and_loading' title	="<?php _e( 'Refresh', 'grocerycrud' );?>"> <span class="fa fa-refresh"></span>
                        </a>
                        <?php if (!$unset_print):?>
                        <a class="print-anchor btn btn-default" data-url="<?php echo $print_url;?>">
                        <div class="fbutton">
                            <div> <span class="print"><?php echo $this->l('list_print');?></span> </div>
                        </div>
                        </a>
                        <?php foreach( get_instance()->events->apply_filters( 'grocery_header_buttons', [] ) as $button ):?>
                            <a class="btn btn-default" href="<?php echo @$button[ 'url' ];?>">
                                <?php echo @$button[ 'text' ];?>
                            </a>
                        <?php endforeach;?>
                        <?php endif;?>
                    </div>
                    <?php endif;?>
                    <a href="javascript:void(0)" class="btn btn-danger btn-sm delete_selected"><?php echo __( 'Delete Selected', 'grocerycrud' );?></a>
                </div>
                <div class="col-lg-3 col-md-3 col-xs-12">
                    <div class="input-group input-group-sm">
                        <div class="input-group-addon"> <?php echo $this->l('list_search');?>: </div>
                        <input type="text" class="form-control qsbsearch_fieldox search_text" id="search_text" name="search_text" placeholder="<?php echo $this->l('list_search');
    ?>">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary crud_search" id='crud_search'><?php echo $this->l('list_search');?></button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-xs-12">
                    <div class="input-group input-group-sm">
                        <select class="form-control" name="search_field" id="search_field">
                            <option value=""><?php echo $this->l('list_search_all');?></option>
                            <?php foreach ($columns as $column) {?>
                            <option value="<?php echo $column->field_name?>"><?php echo $column->display_as?>&nbsp;&nbsp;</option>
                            <?php  }  ?>
                        </select>
                        <span class="input-group-btn">
                        <button type="button" class="btn btn-primary search_clear" id="search_clear"><?php echo $this->l('list_clear_filtering');?></button>
                        </span> 
                    </div>
                </div>
            </div>
        </div>
        <div id='ajax_list' class="ajax_list box-body no-padding table-responsive" style="border-top:solid 1px #EEE;">
            <?php echo $list_view?> 
        </div>
        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-md-4">
                    <div class="btn-group btn-group-justified">
                        <div class="pFirst btn btn-default first-button" title="<?php echo $this->l( 'list_paging_first' );?>"> <span>
                        <?php _e( 'First Page', 'grocerycrud');?>
                        </span> </div>
                        <div class="pPrev btn btn-default prev-button" title="<?php echo $this->l( 'list_paging_previous' );?>"> <span>
                            <?php echo $this->l( 'list_paging_previous' );?>
                            </span> 
                        </div>
                        <div class="pNext btn btn-default next-button" title="<?php echo $this->l( 'list_paging_next' );?>"> <span>
                            <?php echo $this->l( 'list_paging_next' );?>
                            </span> 
                        </div>
                        <div class="pLast btn btn-default last-button" title="<?php echo $this->l( 'list_paging_last' );?>" > <span>
                            <?php _e('Last Page', 'grocerycrud' );?>
                            </span> 
                        </div>
                    </div>

                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo $this->l('list_page'); ?> </div>
                        <input name='page' type="text" value="1" size="4" id='crud_page' class="crud_page form-control">
                        <div class="input-group-addon"><?php echo $this->l('list_paging_of'); ?> <span id='last-page-number' class="last-page-number"><?php echo ceil($total_results / $default_per_page)?></span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <?php list($show_lang_string, $entries_lang_string) = explode('{paging}', $this->l('list_show_entries')); ?>
                            <?php echo $show_lang_string; ?></div>
                            <select name="per_page" id='per_page' class="per_page form-control">
                                <?php foreach ($paging_options as $option) :?>
                                    <option value="<?php echo $option;?>" 
                                        <?php if ($option == $default_per_page):?>
                                        selected="selected"
                                        <?php endif;?>
                                    >
                                        <?php echo $option;?>
                                    </option>
                                <?php endforeach;?>
                            </select>
                        <div class="input-group-addon"><?php echo $entries_lang_string; ?></div>
                    </div>
                    <input type="hidden" name="order_by[0]" id="hidden-sorting" class="hidden-sorting" value="">
                    <input type="hidden" name="order_by[1]" id="hidden-ordering" class="hidden-ordering" value="">
                </div>
                <div class="col-md-3">
                    <span class="pPageStat" style="line-height: 34px;">
						<?php $paging_starts_from = "<span id='page-starts-from' class='page-starts-from'>1</span>"; ?>
                        <?php $paging_ends_to = "<span id='page-ends-to' class='page-ends-to'>". ($total_results < $default_per_page ? $total_results : $default_per_page) ."</span>"; ?>
                        <?php $paging_total_results = "<span id='total_items' class='total_items'>$total_results</span>"?>
                        <?php echo str_replace(array('{start}', '{end}', '{results}'),
                        array($paging_starts_from, $paging_ends_to, $paging_total_results),
                        $this->l('list_displaying')
                       ); ?> 
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?> 
</div>
<script type="text/javascript">
var Responsive 			=  function(){
    this.screenIs 		=   '';
    this.detect 		=	function(){
        if ( window.innerWidth < 544 ) {
            this.screenIs         =   'xs';
        } else if ( window.innerWidth >= 544 && window.innerWidth < 768 ) {
            this.screenIs         =   'sm';
        } else if ( window.innerWidth >= 768 && window.innerWidth < 992 ) {
            this.screenIs         =   'md';
        } else if ( window.innerWidth >= 992 && window.innerWidth < 1200 ) {
            this.screenIs         =   'lg';
        } else if ( window.innerWidth >= 1200 ) {
            this.screenIs         =   'xg';
        }
        console.log( 'Screen is : ' + this.screenIs );
    }

    this.is 			=   function( value ) {
        if ( value === undefined ) {
            return this.screenIs;
        } else {
            return this.screenIs === value;
        }
    }

    $( window ).resize( () => {
        this.detect();
    });

    this.detect();
}
var layout 			=	new Responsive();

if( ! layout.is( 'xs' ) ) {
    $('.table-responsive').on('show.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "inherit" );
    });
    $('.table-responsive').on('hide.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "auto" );
    })
}

</script>