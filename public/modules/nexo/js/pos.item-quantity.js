/**
 * Popup Class for Item Quantity
 * on POS v3
 * @since 3.13.11
 */
class itemQuantityModal {
    constructor( item ) {
            this.popup  =   new ModalVue({
            namespace: 'quantity-popup',
            title: textDomain.selectQuantity,
            modalBodyClass: 'p-0 pt-2 d-flex',
            body: `
            <div id="quantity-wrapper" class="flex-fill w-100 d-flex flex-column">
                <h5 class="text-center">{{ textDomain.chooseTheUnit }}</h5>
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Unité de base</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Pack de 6</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Pack de 12</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#">Pack de 24</a>
                    </li>
                </ul>
                <div class="calculator-wrapper flex-fill w-100 d-flex flex-column">
                    <num-keyboard></num-keyboard>
                </div>
            </div>
            `,
            height: {
                xl: '70%',
                lg: '70%',
                md: '80%',
                sm: '80%',
                xs: '90%',
            },
            width: {
                xl: '40%',
                lg: '40%',
                md: '60%',
                sm: '80%',
                xs: '90%',
            }
        });
    }
}