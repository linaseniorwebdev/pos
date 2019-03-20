class CalculatorModal {
    constructor() {
        this.popup  =   ModalVue({
            namespace: 'calculator-popup',
            title: 'Calculator',
            modalBodyClass: 'modal-body d-flex p-0',
            body: `
            <div class="calculator-wrapper flex-fill w-100 d-flex flex-column">
                <num-keyboard></num-keyboard>
            </div>
            `,
            height: {
                xl: '60%',
                lg: '60%',
                md: '60%',
                sm: '70%',
                xs: '80%',
            },
            width: {
                xl: '30%',
                lg: '30%',
                md: '60%',
                sm: '70%',
                xs: '80%',
            }
        });
    }
}