class SalesCoreModal {
    /**
     * Construct the class
     * @param {number} orderId 
     */
    constructor( orderId ) {
        this.orderId    =   orderId;
        this.modal      =   new ModalVue({
            namespace: 'sales',
            body: textDomain.orderDetails,
            title: textDomain.orderOptions,
            width: '95%',
            data: {
                paymentTabs             :   NexoAPI.events.applyFilters( 'order_options_tabs', defaultOrderOptionsTabs ),
                template                :   textDomain.loading,
                activeTabNamespace      :   '',
            },
            modalBodyClass: 'flex-fill d-flex p-0',
            height: '95%',
            body: `
            <div class="order-details-container w-100 flex-fill">
                <div class="row h-100 flex-fill">
                    <div class="col-lg-2 col-md-4 hidden-sm hidden-xs section-nav pr-md-0 pr-lg-0 pr-xl-0">
                        <ul class="list-group">
                            <li @click="setActive( tab )" v-for="tab in paymentTabs" :class="{ 'active' : tab.active }" class="rounded-0 list-group-item">{{ tab.title }}</li>
                        </ul>
                    </div>
                    <div class="col-sm-12 col-xs-12 hidden-md hidden-lg hidden-xl">
                        <div class="p-3">
                            <select @change="setActiveTab()" v-model="activeTabNamespace"  class="form-control">
                                <option v-for="tab in paymentTabs" :value="tab.namespace">{{ tab.title }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12 pl-lg-0 pl-xl-0">
                        <component v-bind:is="selectedTab" order-id="${this.orderId}"></component>
                    </div>
                </div>
            </div>
            `,
            methods: {
                /**
                 * Set a tab as active
                 * @param {object} tab 
                 */
                setActive( tab ) {
                    this.paymentTabs.forEach( _tab => _tab.active = false );
                    tab.active  =   true;
                    this.$emit( 'loading_component' );
                },
                

                /**
                 * Select the active tab as it's selected
                 * from the select control field
                 * @return void
                 */
                setActiveTab() {
                    const tab   =   this.paymentTabs.filter( tab => tab.namespace === this.activeTabNamespace );
                    if( tab.length > 0 ) {
                        this.setActive( tab[0] );
                    }
                }
            },
            computed: {
                selectedTab() {
                    const activeTab     =   this.paymentTabs.filter( tab => tab.active );
                    if( activeTab.length > 0 ) {
                        return 'app-orders-' + activeTab[0].namespace;
                    } 
                    return false;
                }
            },
            mounted() {
                if( this.paymentTabs.length > 0 ) {
                    const hasAnActive   =   this.paymentTabs.filter( tab => tab.active ).length > 0;
                    if( ! hasAnActive ) {
                        this.paymentTabs[0].active  =   true;
                    }
                }
            }
        });
    }
}