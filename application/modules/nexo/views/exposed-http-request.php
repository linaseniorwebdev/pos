<script>
const HttpRequest   =   axios.create({
    baseURL: '<?php echo base_url();?>',
    headers: { 
        'X-API-KEY': '<?php echo get_option( 'rest_key' );?>',
        'X-Requested-With': 'XMLHttpRequest'
    }
});
HttpRequest.interceptors.request.use(( config ) => {
    tendoo.loader.show();
    return config;
});
HttpRequest.interceptors.response.use((config) => {
    tendoo.loader.hide();
    return config;
}, ( error ) => {
    tendoo.loader.hide();
    return Promise.reject( error );
});
</script>