Vue.filter( 'currency', function (value) {
    return NexoAPI.DisplayMoney( value );
});