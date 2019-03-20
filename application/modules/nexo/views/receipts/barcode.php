<div class="text-center">
    <?php
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode( $order[ 'order' ][0][ 'CODE' ], $generator::TYPE_CODE_128)) . '">';
    ?>
    <br>
    <h3 style="margin:5px 0"><?php echo $order[ 'order' ][0][ 'CODE' ];?></h3>
    <!-- 
        display: inline-block;
    margin-top: -20px;
    background: #FFF;
    position: relative;
    width: auto;
    /* float: left; */
    top: -18px;
    padding: 0px 5px;
    -->
</div>