Vue.filter( 'moneyFormat', ( value ) => {
    if ( isNaN( value ) ) {
        return value;
    }

    switch( tendooOptions.nexo_currency_position ) {
        case 'before':
            return tendooOptions.nexo_currency + ' ' + value;
        break;
        case 'after':
            return value + ' ' + tendooOptions.nexo_currency;
        break;
    }

    return value;
})