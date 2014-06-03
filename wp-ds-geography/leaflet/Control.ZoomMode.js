var ControlZoomMode = L.Control.extend( {
	options: {
		position: 'topleft',
		title: 'Scroll Wheel Zoom on/off'
	},

	onAdd: function( map ) {
		
		container = L.DomUtil.create('div', 'leaflet-control-zoom-mode-bar leaflet-bar');
		var className = 'leaflet-control-zoom-mode', container;
		
		this._createButton( this.options.title, className, container, this.toogleZoomMode, map );

		return container;
	},

	_createButton: function ( title, className, container, fn, context ) {
		var link = L.DomUtil.create('a', className, container);
		link.href = '#';
		link.title = title;
		
		L.DomEvent
			.addListener(link, 'click', L.DomEvent.stopPropagation)
			.addListener(link, 'click', L.DomEvent.preventDefault)
			.addListener(link, 'click', fn, context);

		return link;
	},

	toogleZoomMode: function () { this.scrollWheelZoom._enabled ? ( this.scrollWheelZoom.disable() ) : ( this.scrollWheelZoom.enable()  ) }
});
