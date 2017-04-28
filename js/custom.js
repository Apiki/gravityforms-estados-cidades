(function($, win) {
	var gravityForms = {
		init: function() {
			this.addEventListener();
		},
		addEventListener: function() {
			$( 'body' ).on( 'change', '.estado select', this._onChangeState.bind( this ) );
		},
		_onChangeState: function(e) {
			var select = $( '.cidade select' )
			  , cities = this.getCities( e.currentTarget.value )
			;

			select.empty();
			select.append( $( '<option>', { 'value': '', 'text': 'Selecione' } ) );

			cities.forEach(function(data) {
				select.append( $( '<option>', { 'value': data, 'text': data } ) );
			});
		},
		getCities: function(sigla) {
			var estadosCidades = win.gfec.estadosCidades
			  , cidades        = []
			;

			estadosCidades.forEach(function(data) {
				if ( data.sigla == sigla ) {
					cidades = data.cidades;
				}
			});

			return cidades;
		}
	};

	gravityForms.init();
})(jQuery, window);
