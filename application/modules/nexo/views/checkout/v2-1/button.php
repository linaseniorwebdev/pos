<?php
if ( @$menu[ 'type' ] == 'dropdown' ) {
    ?>
    <div class="dropdown" style="display:inline-block">
        <button class="btn btn-sm btn-default dropdown-toggle" type="button" id="<?php echo @$menu[ 'icon' ];?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            <i class="fa fa-<?php echo @$menu[ 'icon' ];?>"></i> <?php echo $menu[ 'text' ];?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu shadowed-dropdown" aria-labelledby="<?php echo @$menu[ 'icon' ];?>">
        <?php foreach( ( array ) @$menu[ 'options' ] as $option ):?>
            <?php
            $_attrs      =   '';
            if( is_array( @$option[ 'attrs' ] ) ) {
                foreach( @$option[ 'attrs' ] as $name => $value ) {
                    $_attrs  .= $name . '="' . $value . '" ';
                }
            }
            ?>
            <li>
                <a <?php echo @$option[ 'class' ];?> href="<?php echo @$option[ 'url' ] ? $option[ 'url' ] : 'javascript:void(0)';?>" <?php echo $_attrs;?>>
                    <?php echo @$option[ 'text' ] ? $option[ 'text' ] : __( 'Sans Nom', 'nexo' );?>
                </a>
            </li>
        <?php endforeach;?>
        </ul>
    </div>
    <?php
} else {
    ?>
    <button <?php echo $attrs;?> class="btn btn-sm btn-<?php echo @$menu[ 'class' ] == null ? 'default' : $menu[ 'class' ];?>">
        <i class="fa fa-<?php echo @$menu[ 'icon' ];?>"></i> <?php echo @$menu[ 'text' ];?>
    </button>
    <?php
}