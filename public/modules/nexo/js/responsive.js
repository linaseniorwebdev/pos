var Responsive 			=  function(){
	this.screenIs 		=   '';
	this.detect 		=	function(){
		if ( window.innerWidth < 544 ) {
			this.screenIs         =   'xs';
		} else if ( window.innerWidth >= 544 && window.innerWidth < 768 ) {
			this.screenIs         =   'sm';
		} else if ( window.innerWidth >= 768 && window.innerWidth < 992 ) {
			this.screenIs         =   'md';
		} else if ( window.innerWidth >= 992 && window.innerWidth < 1200 ) {
			this.screenIs         =   'lg';
		} else if ( window.innerWidth >= 1200 ) {
			this.screenIs         =   'xg';
		}
	}

	this.is 			=   function( value ) {
		if ( value === undefined ) {
			return this.screenIs;
		} else {
			return this.screenIs === value;
		}
	}

	$( window ).resize( () => {
		this.detect();
	});

	this.detect();
}