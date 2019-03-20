<?php
$table_total            =   $this->events->apply_filters( 'np_profit_lost_report_tfoot_row', array(
    'total'             =>    array(
      'text'          =>    __( 'Total', 'nexo_premium' ),
      'class'         =>    '',
      'attr'          =>    '',
      'colspan'       =>    4
    ),
    'total_qte'         =>    array(
      'text'          =>    '{{ totalQuantity }}',
      'class'         =>    'text-right',
      'attr'          =>    '',
      'colspan'       =>    0
    ),
    'total_gsle'        =>    array(
      'text'          =>    '{{ totalGrossSalePrice | moneyFormat }}',
      'class'         =>    'text-right',
      'attr'          =>    '',
      'colspan'       =>    0
    ),
    'total_pdiscount'   =>    array(
      'text'          =>    '{{ totalPercentDiscount | moneyFormat }}',
      'class'         =>    'text-right',
      'attr'          =>    '',
      'colspan'       =>    0
    ),
    'total_fdiscount'   =>    array(
      'text'          =>    '{{ totalFixedDiscount | moneyFormat }}',
      'class'         =>    'text-right',
      'attr'          =>    '',
      'colspan'       =>    0
    ),
    'total_sales'       =>    array(
      'text'          =>    '{{ totalSales | moneyFormat }}',
      'class'         =>    'text-right',
      'attr'          =>    '',
      'colspan'       =>    0
    ),
    'total_purchase'    =>    array(
      'text'          =>    '{{ totalPurchasePrice | moneyFormat }}',
      'class'         =>    'text-right',
      'attr'          =>    '',
      'colspan'       =>    0
    ),
    'total_income'      =>    array(
      'text'          =>    '{{ totalIncome | moneyFormat }}',
      'class'         =>    'text-right',
      'attr'          =>    'ng-class="{ warning : totalIncome < 0, success : totalIncome > 0, default : totalIncome == 0}"',
      'colspan'       =>    0
    )
) );

$table_head             =   $this->events->apply_filters( 'np_profit_lost_report_thead_row', array(
    'date'              =>  array(
      'text'          =>  _s( 'Date', 'nexo_premium' ),
      'class'         =>  '',
      'width'         =>  220
    ),
    'order_code'            =>  array(
      'text'          =>  _s( 'Commande', 'nexo_premium' ),
      'class'         =>  '',
      'width'         =>  100
    ),
    'design'            =>  array(
      'text'          =>  _s( 'Nom du produit', 'nexo_premium' ),
      'class'         =>  '',
      'width'         =>  300
    ),
    'sku'            =>  array(
      'text'          =>  _s( 'UGS', 'nexo_premium' ),
      'class'         =>  '',
      'width'         =>  100
    ),
    'qte'               =>  array(
      'text'          =>  _s( 'Quantité', 'nexo_premium' ),
      'class'         =>  'text-right',
      'width'         =>  70
    ),
    'gros_sale'         =>  array(
      'text'            =>  _s( 'Prix de vente brut', 'nexo_premium' ),
      'class'           =>  'text-right',
      'width'           =>  180
    ),
    'discount_p'        =>  array(
      'text'          =>  _s( 'Remise (%)', 'nexo_premium' ),
      'class'         =>  'text-right',
      'width'         =>  180
    ),
    'discount_f'        =>  array(
      'text'          =>  _s( 'Remise', 'nexo_premium' ),
      'class'         =>  'text-right',
      'width'         =>  120
    ),
    'net_sale'          =>  array(
      'text'          =>  _s( 'Prix de vente net', 'nexo_premium' ),
      'class'         =>  'text-right',
      'width'         =>  180
    ),
    'buy_price'         =>  array(
      'text'          =>  _s( 'Prix d\'achat', 'nexo_premium' ),
      'class'         =>  'text-right',
      'width'         =>  200
    ),
    'profit'            =>  array(
      'text'          =>  _s( 'Bénéfice', 'nexo_premium' ),
      'class'         =>  'text-right',
      'width'         =>  150
    )
) );

$table_column           =   $this->events->apply_filters( 'np_profit_lost_report_tbody_row', array(
    'date'              =>  array(
      'text'          =>  '{{ item.DATE_CREATION | date : "' . store_option( 'nexo_js_datetime_format', 'medium' ) . '" }}',
      'class'         =>  '',
      'attr'          =>  '',
      'csv_field'       =>  'item.DATE_CREATION'
    ),
    'order_code'            =>  array(
      'text'          =>  '{{ item.CODE }}',
      'class'         =>  '',
      'attr'          =>  '',
      'csv_field'       =>  'item.CODE'
    ),
    'design'            =>  array(
      'text'          =>  '{{ item.DESIGN }}',
      'class'         =>  '',
      'attr'          =>  '',
      'csv_field'       =>  'item.DESIGN'
    ),
    'sku'            =>  array(
      'text'          =>  '{{ item.SKU }}',
      'class'         =>  '',
      'attr'          =>  '',
      'csv_field'       =>  'item.SKU'
    ),
    'qte'               =>  array(
      'text'          =>  '{{ item.QUANTITE }}',
      'class'         =>  'text-right',
      'attr'          =>  '',
      'csv_field'       =>  'item.QUANTITE'
    ),
    'gros_sale'         =>  array(
      'text'          =>  '{{ item.PRIX * item.QUANTITE | moneyFormat }}',
      'class'         =>  'text-right info',
      'attr'          =>  '',
      'csv_field'       =>  'item.PRIX * item.QUANTITE'
    ),
    'discount_p'        =>  array(
      'text'          =>  '{{ showPercentage( cartPercentage( item ) ) }} {{ showPercentage( item.DISCOUNT_PERCENT, "+" ) }} {{ ( calculateCartPercentage( item ) + calculateItemPercentage( item ) ) * item.QUANTITE | moneyFormat }}',
      'class'         =>  'text-right warning',
      'attr'          =>  '',
      'csv_field'       =>  '( $scope.calculateCartPercentage( item ) + $scope.calculateItemPercentage( item ) ) * item.QUANTITE'
    ),
    'discount_f'        =>  array(
      'text'          =>  '{{ ( showFixedItemUniqueDiscount( item ) + showFixedCartUniqueDiscount( item ) ) * item.QUANTITE | moneyFormat }}',
      'class'         =>  'text-right warning',
      'attr'          =>  '',
      'csv_field'       =>  '( $scope.showFixedItemUniqueDiscount( item ) + $scope.showFixedCartUniqueDiscount( item ) ) * item.QUANTITE'
    ),
    'net_sale'          =>  array(
      'text'          =>  '{{ calculateNetSellingPrice( item ) | moneyFormat }}',
      'class'         =>  'text-right info',
      'attr'          =>  '',
      'csv_field'       =>  '$scope.calculateNetSellingPrice( item )'
    ),
    'buy_price'         =>  array(
      'text'          =>  '{{ item.PRIX_DACHAT * item.QUANTITE | moneyFormat }}',
      'class'         =>  'text-right info',
      'attr'          =>  '',
      'csv_field'       =>  'item.PRIX_DACHAT * item.QUANTITE'
    ),
    'profit'            =>  array(
      'text'          =>  '{{ calculateProfit( item )| moneyFormat }}',
      'class'         =>  'text-right info',
      'attr'          =>  'ng-class="{ \'danger\' : calculateProfit( item ) < 0, \'success\' : calculateProfit( item ) > 0, \'default\' : calculateProfit( item ) == 0}"',
      'csv_field'       =>  '$scope.calculateProfit( item )'
    )
) );
