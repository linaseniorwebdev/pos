<?php include_once( dirname( __FILE__ ) . '/import-parser.php' );?>
<script>
var customerData    =   {
    textDomain  :   {
        warning                 :   '<?php echo _s( 'Attention', 'nexo' );?>',
        name                    :   '<?php echo __( 'Nom', 'nexo' );?>',
        surname                 :   '<?php echo __( 'Prénom', 'nexo' );?>',
        email                   :   '<?php echo __( 'Email', 'nexo' );?>',
        address                 :   '<?php echo __( 'Addresse', 'nexo' );?>',
        state                   :   '<?php echo __( 'Etat', 'nexo' );?>',
        city                    :   '<?php echo __( 'Ville', 'nexo' );?>',
        country                 :   '<?php echo __( 'Pays', 'nexo' );?>',
        post_code               :   '<?php echo __( 'Code Postale', 'nexo' );?>',
        phone                   :   '<?php echo __( 'Téléphone', 'nexo' );?>',
        gender                  :   '<?php echo __( 'Genre', 'nexo' );?>',
        importSuccessTitle      :   '<?php echo __( 'Opération effectuée', 'nexo' );?>',
        importSuccessMessage    :   '<?php echo __( 'Les clients ont correctement été importée.', 'nexo' );?>',
        fileExtensionWarning    :   '<?php echo _s( 'Le fichier que vous essayez d\'utiliser n\'est pas pris en charge.', 'nexo' );?>',
        alreadySelected         :   '<?php echo _s( 'Cette colonne a déjà été utilisé. Veuillez affecter une colonne différente.', 'nexo' );?>',
        notEnoughData           :   '<?php echo _s( 'Le fichier CSV que vous essayez d\'utiliser n\'a pas suffisamment d\'entrées.', 'nexo' );?>',
        anErrorOccurredTitle    :   '<?php echo _s( 'Une erreur s\'est produite', 'nexo' );?>',
        anErrorOccurredMessage  :   '<?php echo _s( 'Une erreur s\'est produite durant l\'importation des clients.', 'nexo' );?>'
    },
    url     :   {
        postCSV     :   '<?php echo site_url([ 'api', 'nexopos', 'import', 'customers' ]);?>'
    },
    headers     :   {
        '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo get_option( 'rest_key' );?>'
    }
}
</script>
<script>
tendooApp.controller( 'customersImportCTRL', [
    '$scope',
    '$http',
    '$compile',
    '$timeout',
    function( $scope, $http, $compile, $timeout ) {

        $scope.fileColumns  =   [];
        $scope.csvContent       =   [];

        $scope.firstRawModel    =   [];
        $scope.usedColumns      =   {};

        $scope.supportedColumns     =   {
            name        : {
                value   :   'NOM',
                label   :   customerData.textDomain.name
            }, 
            surname     :   {
                value   :   'PRENOM',
                label   :   customerData.textDomain.surname
            },             
            email :     {
                value   :   'EMAIL',
                label   :   customerData.textDomain.email
            }, 
            address :     {
                value   :   'ADDRESSE',
                label   :   customerData.textDomain.address
            }, 
            state       :     {
                value   :   'STATE',
                label   :   customerData.textDomain.state
            }, 
            city       :     {
                value   :   'CITY',
                label   :   customerData.textDomain.city
            }, 
            country       :     {
                value   :   'COUNTRY',
                label   :   customerData.textDomain.country
            }, 
            post_code       :     {
                value   :   'STATE',
                label   :   customerData.textDomain.post_code
            }, 
            phone       :   {
                value   :   'TEL',
                label   :   customerData.textDomain.phone
            }, 
            gender      :   {
                value   :   'SEX',
                label   :   customerData.textDomain.gender
            }
        };

        $scope.supportedColumnsArray    =   Object.values( $scope.supportedColumns );

        $scope.detectFile       =   function( e ){
            let file            =   e.target.files[0];
            let fileExtension   =   file.name.split('.').pop();

            if ( [ 'csv' ].indexOf( fileExtension ) === -1 ) {
                return NexoAPI.Notify().warning(
                    customerData.textDomain.warning,
                    customerData.textDomain.fileExtensionWarning
                );
            }

            let fileReader      =   new FileReader();
            fileReader.readAsText( file, 'UTF-8' );
            fileReader.onload   =   function( data ) {
                let lines       =   CSVToArray( data.target.result, ',' );
                if ( lines.length < 2 ) {
                    return NexoAPI.Notify().warning(
                        customerData.textDomain.warning,
                        customerData.textDomain.notEnoughData
                    );
                }

                $timeout( () => {
                    $scope.fileColumns  =   lines[0];
                    $scope.csvContent   =   lines.filter( ( line, index ) => index !== 0 );
                });
            }
        }

        $scope.watchChange      =   function( models, index ) {
            let value           =   Object.assign({}, models );
            let similarMatches  =   models.filter( ( _model, _index ) => ( _model === value[ index ] && _index !== index && _model !== '' ) );
            /// if that column has yet been selected
            if ( similarMatches.length > 0 ) {
                models[ index ]     =   '';
                return NexoAPI.Notify().warning(
                    customerData.textDomain.warning,
                    customerData.textDomain.alreadySelected
                );
            }

            $scope.updateUsedColumns();
        }

        $scope.updateUsedColumns    =   function(){
            $scope.supportedColumnsArray.map( column => {
                $scope.usedColumns[ column.value ]  = false;
                if ( $scope.firstRawModel.indexOf( column.value ) !== -1 ) {
                    $scope.usedColumns[ column.value ]  = true;
                }
            });
        }

        $scope.import           =   function(){

            $scope.isPosting    =   true;

            $http.post( customerData.url.postCSV, {
                model       :   $scope.firstRawModel,
                csv         :   $scope.csvContent,
                overwrite   :   $scope.overwrite,
                empty       :   $scope.empty
            }, {
                headers     :   customerData.headers
            }).then( result => {

                $scope.isPosting    =   false;

                if ( result.data.status === 'success' ) {
                    NexoAPI.Notify().success(
                        customerData.textDomain.importSuccessTitle,
                        customerData.textDomain.importSuccessMessage
                    );
                }
            }, error => {
                $scope.isPosting    =   false;
                NexoAPI.Notify().warning(
                    customerData.textDomain.anErrorOccurredTitle,
                    customerData.textDomain.anErrorOccurredMessage
                );
            })
            // console.log( $scope.firstRawModel, $scope.fileColumns, $scope.csvContent );
        }

        jQuery( '[type="file"]' ).bind( 'change', function( e ){
            $scope.detectFile( e );
        });
    }
])
</script>