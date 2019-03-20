<div id="online-ordering" class="container-fluid h-100">
    <div class="row p-2 h-100" style="background: #e2e9ff">
        <div class="flex-column d-flex px-0 pr-1 col-md-6">
            <div class="button-wrapper mb-2 column-1">
                <button @click="button.click()" v-for="button in topLeftButtons" v-hide="button.hide" v-html="button.label" type="button" class="mr-1" :class="button.class"></button>
            </div>
            <div class="card flex-fill cart-container">
                <div class="card-header p-2 border-bottom-0">
                    <button @click="button.click()" v-for="button in cartHeaderButtons" v-hide="button.hide" v-html="button.label" type="button" class="mr-1" :class="button.class"></button>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <div class="cart-table">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" style="width: 350px" scope="col"><?php echo __( 'Nom', 'nexo' );?></th>
                                    <th class="border-bottom-0" class="text-left" style="width: 100px" scope="col"><?php echo __( 'Prix', 'nexo' );?></th>
                                    <th class="border-bottom-0" style="width: 100px"  scope="col"><?php echo __( 'Quantité', 'nexo' );?></th>
                                    <th class="border-bottom-0" class="text-left" style="width: 100px" scope="col"><?php echo __( 'Remise', 'nexo' );?></th>
                                    <th class="border-bottom-0" class="text-right" scope="col" style="width: 100px"><?php echo __( 'Total', 'nexo' );?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="cart-table flex-fill" style="flex: 1 1 100%;overflow-y: scroll;">
                        <table class="table mb-0">
                            <tbody>
                                <tr v-for="( item, index ) in cartItems">
                                    <th style="width: 350px" scope="row">
                                        <span>{{ item.DESIGN }}</span>
                                        <ul style="padding: 0px">
                                            <li v-for="meta in item.metas">&mdash; {{ meta.name }} : {{ meta.price | currency }}</li>
                                        </ul>
                                    </th>
                                    <td class="text-left" style="width: 100px">{{ getSingleItemPrice( item ) | currency }}</td>
                                    <td style="width: 150px">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <button @click="decreaseQuantity( item, 1, index )" class="btn btn-outline-secondary" type="button" id="button-addon1">-</button>
                                            </div>
                                            <input @change="refreshCart()" type="text" disabled v-model="item.quantity" class="form-control" placeholder="" aria-describedby="button-addon1">
                                            <div class="input-group-append">
                                                <button @click="increaseQuantity( item, 1, index )" class="btn btn-outline-secondary" type="button" id="button-addon1">+</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right" style="width: 150px">{{ getTotalItemPrice( item ) | currency }}</td>
                                </tr>
                                <tr v-if="cartItems.length === 0">
                                    <th colspan="4" scope="row"><?php echo __( 'Aucun produit n\'a été ajouté au panier...', 'nexo' );?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="cart-table">
                        <table class="table mb-0">
                            <thead>
                                <tr class="d-flex flex-row">
                                    <td scope="col" class="d-flex col-6 border-bottom-0 flex-row flex-fill justify-content-between">
                                        <div><?php echo __( 'Tous les produits', 'nexo' );?></div>
                                        <div>{{ totalItems }}</div>
                                    </td>
                                    <td scope="col" class="d-flex col-6 border-bottom-0 flex-row flex-fill justify-content-between">
                                        <span><?php echo __( 'Sous Total', 'nexo' );?></span>
                                        <span>{{ totalPrice | currency }}</span>
                                    </td>
                                </tr>
                                <tr class="d-flex flex-row">
                                    <td scope="col" class="d-flex col-6 border-bottom-0 flex-row flex-fill justify-content-between">
                                        <span><?php echo __( 'Remise', 'nexo' );?></span>
                                        <span>{{ totalItems }}</span>
                                    </td>
                                    <td scope="col" class="d-flex col-6 border-bottom-0 flex-row flex-fill justify-content-between">
                                        <span><?php echo __( 'Taxes', 'nexo' );?></span>
                                        <span>{{ totalPrice | currency }}</span>
                                    </td>
                                </tr>
                                <tr class="d-flex flex-row">
                                    <td scope="col" class="d-flex col-6 flex-row flex-fill justify-content-between">
                                        <span><?php echo __( 'Livraison', 'nexo' );?></span>
                                        <span>{{ totalItems }}</span>
                                    </td>
                                    <td scope="col" class="d-flex col-6 flex-row flex-fill justify-content-between">
                                        <span><?php echo __( 'Total', 'nexo' );?></span>
                                        <span>{{ totalPrice | currency }}</span>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="btn-group btn-group-lg p-2 border-top" role="group" aria-label="Basic example">
                        <button v-for="button in cartFooterButtons" type="button" @click="button.click()" v-html="button.label" :class="button.class" class="flex-fill btn"></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-0 pl-1 col-md-6 d-flex flex-column">
            <div class="button-wrapper mb-2 column-1">
                <button @click="button.click()" class="mr-1" v-html="button.label"  v-for="button in topRightButtons" v-hide="button.hide" type="button" :class="button.class"></button>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2" style="background: #FFF">
                    <li @click="setBreadIndexTo( index, bread )" v-for="( bread, index ) in breadcrumbs" class="breadcrumb-item"><a href="javascript:void(0)">{{ bread.name }}</a></li>
                </ol>
            </nav>
            <div class="flex-fill d-flex flex-column container-fluid" style="overflow-y: auto;background: #FFF;">
                <div class="row">
                    <div @click="goBackTo( returnTo )" v-if="returnTo !== 0" class="col-sm-6 col-md-4 col-lg-2 p-1 product-grid-item d-flex flex-row justify-content-center align-items-center">
                        <i style="font-size: 10em" class="fa fa-arrow-circle-left fa-6" aria-hidden="true"></i>
                    </div>
                    <div @click="loadCategories( category.ID )" v-if="loadType === 'categories'" v-for="category in rawCategories" class="col-sm-6 col-md-4 col-lg-2 p-1 product-grid-item">
                        <img class="flex-fill img-grid" :src="getThumb( category )" alt="">
                        <div class="product-item-details">
                            <p class="text-center mb-1">{{ category.NOM }}</p>
                        </div>
                    </div>
                    <div @click="addToCart( item )" v-if="loadType === 'items'" v-for="item in rawItems" class="col-sm-6 col-md-4 col-lg-2 p-1 product-grid-item">
                        <img class="flex-fill img-grid" :src="getThumb( item, { source : imageUploadPath, param : 'APERCU' })" alt="">
                        <div class="product-item-details">
                            <p class="text-center mb-1">{{ item.DESIGN }}</p>
                            <p class="text-center">
                                <strong class="">{{ item.PRIX_DE_VENTE_TTC | currency }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once( dirname( __FILE__ ) . '/style.php' );?>
<?php include_once( dirname( __FILE__ ) . '/text-domain.php' );?>
<script>
    const csrf                  =   <?php echo json_encode( array(
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
    ) );?>;
    const storeOptions          =   <?php echo json_encode([
        'url'                       =>  base_url(),
        'author'                    =>  store_option( 'so_default_author', 2 ), // who has posted the orders. A user should be created for that.
        'timerBeforeClosingPopup'   =>  store_option( 'so_timeout_before_closing_popup', 5000 ),
        'default_table'             =>  store_option( 'so_default_table', 1 ),
        'printer'               =>  [
            'name'              =>  store_option( 'so_nps_printer', false ),
            'url'               =>  store_option( 'so_nps_url', false )
        ]
    ]);?>;
    const user                  =   <?php echo json_encode( User::get() );?>;
    const currencyConfig        =   {
        symbol      :   '<?php echo store_option( 'nexo_currency' );?>',
        position    :   '<?php echo store_option( 'nexo_currency_position' );?>'
    };
    const BreakPointCSS     =   
        `<style id="modal-vue-style-{namespace}">
        /* 
        ##Device = Desktops
        ##Screen = 1281px to higher resolution desktops
        */

        @media (min-width: 1281px) {
        
            {elementSelector} .modal-dialog {
                /*xlheight*/
                /*xlwidth*/
            }
        
        }

        /* 
        ##Device = Laptops, Desktops
        ##Screen = B/w 1025px to 1280px
        */

        @media (min-width: 1025px) and (max-width: 1280px) {
        
            {elementSelector} .modal-dialog {
                /*lgheight*/
                /*lgwidth*/
            }
        
        }

        /* 
        ##Device = Tablets, Ipads (portrait)
        ##Screen = B/w 768px to 1024px
        */

        @media (min-width: 768px) and (max-width: 1024px) {
        
            {elementSelector} .modal-dialog {
                /*mdheight*/
                /*mdwidth*/
            }
        
        }

        /* 
        ##Device = Tablets, Ipads (landscape)
        ##Screen = B/w 768px to 1024px
        */

        @media (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
        
            {elementSelector} .modal-dialog {
                /*smheight*/
                /*smwidth*/
            }
        
        }

        /* 
        ##Device = Low Resolution Tablets, Mobiles (Landscape)
        ##Screen = B/w 481px to 767px
        */

        @media (min-width: 481px) and (max-width: 767px) {
        
            {elementSelector} .modal-dialog {
                /*xsheight*/
                /*xswidth*/
            }
        
        }
    </style>`;
</script>
<script src="<?php echo module_url( 'nexo' ) . 'js/pos.num-keyboard.js';?>"></script>
<script src="<?php echo module_url( 'nexo' ) . 'js/modal.vue.js';?>"></script>
<script src="<?php echo module_url( 'nexo' ) . 'js/pos.customers.js';?>"></script>
<script src="<?php echo module_url( 'nexo' ) . 'js/pos.calculator.js';?>"></script>
<script src="<?php echo module_url( 'nexo' ) . 'js/pos.item-quantity.js';?>"></script>
<script src="<?php echo module_url( 'nexo' ) . 'js/pos-v3.vue.js';?>"></script>
