Vue.component( 'bs4-button-toggle', {
    props: [ 'options', 'label' ],
    data() {
        return {
            isOpen  :   false
        }
    },
    template: `
    <div class="dropdown" :class="{ 'open' : isOpen }">
        <button @click="isOpen = ! isOpen" class="btn btn-secondary dropdown-toggle" type="button" id="triggerId" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
                {{ label }}
            </button>
        <div v-if="options.length > 0" class="dropdown-menu" :class="{ 'bs4-button-toggle-is-open' : isOpen }" aria-labelledby="triggerId">
            <button v-for="option in options" @click="$emit( 'clicked', option ); isOpen = false" class="dropdown-item" href="#">{{ option.label }}</button>
        </div>
    </div>
    `,
    mounted() {
        /**
         * close dropdown on blur 
         */
        // $( '.dropdown-toggle' ).bind( 'blur', (e) => {
        //     setTimeout(() => {
        //         this.isOpen     =   false;
        //     }, 100 );
        // });

        if( $( '#bs4-button-toggle-style' ).length === 0 ) {
            $( 'body' ).append( `
                <style id="bs4-button-toggle-style">
                .bootstrapiso .dropdown-menu.bs4-button-toggle-is-open {
                    display: block;
                }
                </style>
            `);
        }
    }
})