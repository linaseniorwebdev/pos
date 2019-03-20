/**
 * Popup Class for Customers
 * on POS v3
 * @since 3.13.11
 */
class CustomerPopupModal {
    constructor() {
        this.popup  =   new ModalVue({
            namespace: 'customer-popup',
            title: textDomain.selectACustomer,
            body: `
                
            `,
            height: {
                xl: '70%',
                lg: '70%',
                md: '80%',
                sm: '80%',
                xs: '90%',
            },
            width: {
                xl: '50%',
                lg: '50%',
                md: '60%',
                sm: '70%',
                xs: '80%',
            }
        });
    }
}