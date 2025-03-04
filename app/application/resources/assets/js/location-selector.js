class LocationSelector {

	inputs = {
		// output: center coordinate for location
		'location'			: 'input[name=\'location\']',
		'locationX'			: 'input[name=\'locationX\']',
		'locationY'			: 'input[name=\'locationY\']',

		// output: size for location
		'size'				: 'input[name=\'size\']',
		'sizeX'				: 'input[name=\'sizeX\']',
		'sizeY'				: 'input[name=\'sizeY\']',

		// output: additional area to to send over (derived from coordinate and size, or polygon)
		'areaOfInterest'		: 'input[name=\'areaOfInterest\']',
		'areaOfInterestAttributes'	: 'input[name=\'areaOfInterestAttributes\']',

		// crs for output of selected data
		'crs'				: 'input[name=\'crs\']',

		// input only: the polygon of interest
		'polygon'			: 'input[name=\'polygon\']',

		// inpout only: the zoom level
		'zoomLevel'			: 'input[name=\'zoomLevel\']',
	}

	dom = {
		'mapContainer'			: '.locationSelection',
		'inputsParent'			: '.locationSelectorInputs',
	}

	config = {
		'backgroundLayers'		: [ {
			'layer' 				: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			'config'				: { 'attribution' : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }
		} ],
		'selectableLayer'		: null,
		'styles'			: {
			'polygon'				: { 'color' : 'orange', 'fillColor' : 'orange' },
			'selectables'				: { 'color' : 'black', 'fillColor' : 'darkgrey' },
		},

		'cameraPadding'			: [ 100, 100 ],

		'generateAOI'			: true,

		'squareSize'			: false, //TODO
		'minSize'			: 500, //TODO
		'maxSize'			: 5000, //TODO
		'shrinkForAOI'			: true, //TODO
		'growForAOI'			: true, //TODO
	}

	selectables = {

	}

	defaults = {
		crs : '3857',
		coordinate : { crs : '3857', x: 479967, y: 6814662 },
		size : 500,
		zoomLevel :  13,
	}

	selection = {
		coordinate: null,
		polygon: null, //Uploaded/selected AOI

		areaOfInterest : null,
		selectionBounds : null,

		size: null,

		zoomLevel : null
	}

	visualization= {

	}


	//* CONSTRUCTOR *//

	createLocationSelector( settings ) {
		this.setupSettings( settings );
		this.setupCreateMissingInputs();
		this.setupCreateLeaflet();
		this.setupData();
		this.setupLeafletBackground();
		this.setupStartLeaflet();
		console.log(this);
		console.log(this.selection);
	}

	setupSettings( settings ) {
		for ( let type of Object.keys(settings) ) {
			if ( !['inputs','dom','defaults','selectables','config'].includes(type) ) {
				continue;
			}
			let provided = settings[type];
			for ( let key of Object.keys(provided) ) {
				this[type][key] = provided[key];
			}
		}
	}



	//* CONTROL *//

	setCameraToCoordinate( coordinate, zoom ) {
		this.leaflet.setView( coordinate, zoom );
	}
	setCameraToBounds( bounds ) {
		this.leaflet.fitBounds( bounds, {'padding': this.config['cameraPadding'] });
	}
	setCameraToSelection() {
		this.setCameraToBounds( this.computeSelectionBounds() );
	}
	setSelectable( key ) {
		if ( this.selectables[key] == null ) {
			this.config['selectableLayer'] = null;
		} else {
			this.config['selectableLayer'] = key;
		}
		this.reloadSelectablesLayer();
	}

	//* SELECTION *//
	setCoordinate(coordinate) {
		this.selection['coordinate'] = coordinate;
		this.createAndSetSelectionFromCoordinate();
		this.redrawVisualization();
	}
	setPolygon(polygon) {
		this.selection['polygon'] = polygon;
		this.createAndSetSelectionFromPolygon();
		this.redrawVisualization();
	}


	createAndSetSelectionData() {
		if ( this.selection['polygon'] != null ) {
			this.createAndSetSelectionFromPolygon();
		} else {
			this.createAndSetSelectionFromCoordinate();
		}
	}

	createAndSetSelectionFromCoordinate() {
		let bounds = this.computeBoundsFromCoordinate(this.selection['coordinate'],this.selection['size']);
		this.selection['selectionBounds'] = bounds;

		if ( (this.selection['polygon'] == null) && (this.config['generateAOI'] == true) ) {
			let areaOfInterest = this.computeGeometryFromBounds(bounds);
			this.selection['areaOfInterest'] = areaOfInterest;
		}
	}

	createAndSetSelectionFromPolygon() {
		if (this.selection['polygon'] != null) {
			this.selection['coordinate'] = this.computeCenterFromGeometry(this.selection['polygon']);
			this.createAndSetSizeFromPolygon();

			this.selection['areaOfInterest'] = this.selection['polygon'];
		}
		this.createAndSetSelectionFromCoordinate();
	}
	createAndSetSelectionClearPolygon() {
		this.selection['polygon'] = null;
		this.createAndSetSelectionFromCoordinate();
	}

	createAndSetSizeFromPolygon() {
		console.log('TODO');
		this.computeSizeFromGeometry(this.selection['polygon']);
		// Compute bounds
		// Compute height
		// Compute width
		// if config, clamp up
		// if config, clamp down
		// Store result
	}

	createAndSetSelectionBoundsFromCoordinate() {
		if ( this.selection['areaOfInterest'] != null ) {
			this.selection['selectionBounds'] = this.computeBoundsFromGeometry(
				this.selection['areaOfInterest']
			)
		}

		if ( this.selection['areaOfInterest'] == null ) {
			this.selection['selectionBounds'] = this.computeBoundsFromCoordinate(
				this.selection['coordinate'],
				this.selection['size'],
			)
		}
	}

	computeSelectionBounds() {
		if (this.selection['selectionBounds'] !== null ) {
			return this.selection['selectionBounds'];
		} else if ( this.selection['polygon'] !== null ) {
			return this.computeBoundsFromGeometry(
				this.selection['polygon']
			);
		} else {
			return this.computeBoundsFromCoordinate(
				this.selection['coordinate'],
				this.selection['size']
			);
		}
	}

	//* DATA CONVERSION *//

	convertToLeaflet( data ) {
		if ( data === null ) {
			return null;
		}
		switch (this.checkDataType(data)) {
			case 'coordinate'	:	return this.convertCoordinateToLeaflet(data);
			case 'coordinates'	:	return this.convertCoordinatesToLeaflet(data);
			case 'polygon'		:
			case 'multipolygon'	:	return this.convertPolygonToLeaflet(data);
		}
		return data;
	}

	convertFromLeaflet( data ) {
		if ( data === null ) {
			return null;
		}
		switch (this.checkDataType(data)) {
			case 'coordinate'	:	return this.convertCoordinateFromLeaflet(data);
			case 'coordinates'	:	return this.convertCoordinatesFromLeaflet(data);
			case 'polygon'		:
			case 'multipolygon'	:	return this.convertPolygonFromLeaflet(data);
		}
		return data;
	}

	checkDataType(data) {
		if ( data === null ) {
			return null;
		}
		if ( !(data instanceof Object) ) {
			if (!isNaN(parseFloat(data)) && isFinite(data)) {
				return 'numeric';
			}
		}
		if ( Array.isArray(data) ) {
			if ( data.length === 0 ) {
				return 'coordinates';
			}
			if ( data.length === 2 ) {
				if ( (this.checkDataType(data[0]) === 'numeric') && (this.checkDataType(data[0]) === 'numeric') ) {
					return 'coordinate';
				}
			}
			if ( this.checkDataType(data[0]) === 'coordinate' ) {
				return 'coordinates';
			}
		}
		if ( data['x'] && data['y'] ) {
			return 'coordinate';
		}
		if ( data['lat'] && data['lng'] ) {
			return 'coordinate';
		}
		if ( data['type'] == 'Polygon' ) {
			return 'polygon';
		}
		if ( data['type'] == 'MultiPolygon' ) {
			return 'multipolygon';
		}
		if ( data['type'] == 'Feature' ) {
			return 'feature';
		}
		return 'unknown';
	}

	swapCoordinatesXY(coordinate) {
		if ( Array.isArray(coordinate) ) {
			if ( this.checkDataType(coordinate) == 'coordinate' ) {
				return [coordinate[1], coordinate[0]];
			} else {
				let arr = [];
				for(let i=0;i<coordinate.length;i++) {
					let c = this.swapCoordinatesXY(coordinate[i]);
					arr.push(c);
				}
				return arr;
			}
		}
	}

	convertCoordinateToLeaflet(coordinate, arrayType = 'xy') {
		let crs = this.selection['crs'];
		if ( coordinate['crs'] ) {
			crs = coordinate['crs'];
		}
		let crsObj = L.CRS['EPSG'+crs];
		if ( Array.isArray(coordinate) ) {
			if (arrayType=='yx') {
				coordinate = L.point([coordinate[1],coordinate[0]]);
			} else {
				coordinate = L.point(coordinate)
			}
		}
		let leafletCoordinate = crsObj.unproject(coordinate);

		return leafletCoordinate;
	}
	convertCoordinateFromLeaflet(leafletCoordinate, arrayType = 'xy') {
		let crs = this.selection['crs'];
		let crsObj = L.CRS['EPSG'+crs];
		leafletCoordinate = L.latLng(leafletCoordinate);
		let coordinate = crsObj.project(leafletCoordinate);

		if ( arrayType == 'yx' ) {
			return [coordinate.y, coordinate.x];
		} else if ( arrayType == 'xy') {
			return [coordinate.x, coordinate.y];
			}
		return coordinate;
	}

	convertCoordinatesToLeaflet(coordinates, arrayType = 'xy' ) {
		let output = [];
		for ( let i=0 ; i<coordinates.length ; i++ ) {
			output[i] = this.convertCoordinateToLeaflet( coordinates[i], arrayType );
		}
		return output;
	}
	convertCoordinatesFromLeaflet(leafletCoordinates, arrayType = 'xy' ) {
		let output = [];
		for ( let i=0 ; i<leafletCoordinates.length ; i++ ) {
			output[i] = this.convertCoordinateFromLeaflet( leafletCoordinates[i], arrayType );
		}
		return output;
	}
	convertPolygonToLeaflet(polygon) {
		let coordinatesSets = polygon.coordinates;
		if ( polygon.type === 'MultiPolygon' ) {
			coordinatesSets = coordinatesSets[0];
		}
		let convertedCoordinateSets = [];
		for ( let i of Object.keys(coordinatesSets) ) {
			convertedCoordinateSets[i] = this.convertCoordinatesToLeaflet( coordinatesSets[i], 'xy' );
		}
		return {
			'type' : 'Polygon',
			'coordinates' : convertedCoordinateSets,
		}
	}
	convertPolygonFromLeaflet(polygon) {
		let coordinatesSets = polygon.coordinates;
		let convertedCoordinateSets = [];
		for ( let i of Object.keys(coordinatesSets) ) {
			convertedCoordinateSets[i] = this.convertCoordinatesFromLeaflet( coordinatesSets[i], 'xy' );
		}
		return {
			'type' : 'MultiPolygon',
			'coordinates' : [convertedCoordinateSets],
		}

	}


	//* DATA CALCULATION *//

	computeBoundsFromCoordinate( coordinate, size ) {
		if ( coordinate == null || size == null ) {
			return null;
		}
		let width = size.width ?? size;
		let height = size.height ?? size;

		let earthRadiusLatMeters = 40008000; //y
		let earthRadiusLngMeters = 40075000; //x

		let halfLat = ( ( height/2 ) / earthRadiusLatMeters ) * 360;

		let boundsUpperLat	= coordinate.lat + halfLat;
		let boundsLowerLat	= coordinate.lat - halfLat;

		let earthRadiusUpperLatMeters = earthRadiusLatMeters * Math.cos(boundsUpperLat*(Math.PI/180));
		let earthRadiusLowerLatMeters = earthRadiusLatMeters * Math.cos(boundsLowerLat*(Math.PI/180));

		let halfLngUpper = ( ( width/2) / earthRadiusUpperLatMeters ) * 360;
		let halfLngLower = ( ( width/2) / earthRadiusLowerLatMeters ) * 360;

		let boundsUpperLeftLng 	= coordinate.lng - halfLngUpper;
		let boundsUpperRightLng	= coordinate.lng + halfLngUpper;
		let boundsLowerLeftLng 	= coordinate.lng - halfLngLower;
		let boundsLowerRightLng	= coordinate.lng + halfLngLower;

		return [
				[boundsUpperLat, boundsUpperLeftLng],
				[boundsUpperLat, boundsUpperRightLng],
				[boundsLowerLat, boundsLowerRightLng],
				[boundsLowerLat, boundsLowerLeftLng]
			];

	}

	computeGeometryFromBounds( bounds ) {
		let coordinates = [];
		for ( let i = 0 ; i < bounds.length ; i++ ) {
			coordinates[i] = [ bounds[i][0], bounds[i][1] ];
		}
		return {
			'type' : 'Polygon',
			'coordinates' : [coordinates],
		};
	}

	computeCenterFromGeometry( geometry ) {
		let coordinates = geometry.coordinates;
		return L.polygon(coordinates).getBounds().getCenter();
	}

	computeBoundsFromGeometry( geometry ) {
		let coordinates = geometry.coordinates;
		let bounds = L.polygon(coordinates).getBounds();
		return [
				[bounds.getNorth(), bounds.getWest()],
				[bounds.getNorth(), bounds.getEast()],
				[bounds.getSouth(), bounds.getEast()],
				[bounds.getSouth(), bounds.getWest()],
			];
	}

	computeSizeFromGeometry( geometry ) {
		let coordinates = geometry.coordinates;
		let bounds = L.polygon(coordinates).getBounds();

		let size = {
			'width': Math.max(
					bounds.getNorthWest().distanceTo(bounds.getNorthEast()),
					bounds.getSouthWest().distanceTo(bounds.getSouthEast())
				),
			'height': Math.max(
					bounds.getNorthWest().distanceTo(bounds.getSouthWest()),
					bounds.getNorthEast().distanceTo(bounds.getSouthEast())
				),
		}

		return size;
	}

	//* DATA INTERFACING *//

	loadSelectionFromDefaults() {
		for ( let key of Object.keys(this.defaults) ) {
			let def = this.defaults[key];
			if ( def['crs'] ) {
				def = this.convertToLeaflet(def);
			}
			this.selection[key] = def;
		}
	}

	loadSelectionFromInterface( ignoreNull ) {
		let data = {
			coordinate : this.readDataFromInterfaceElement( 'location', 'locationX', 'locationY', 'x' , 'y' ),
			size : this.readDataFromInterfaceElement( 'size', 'sizeX', 'sizeY', 'width' , 'height' ),
		};

		for ( let key of Object.keys(this.selection) ) {
			let value = null;
			switch(key) {
				case 'coordinate' :
					value = this.readDataFromInterfaceElement('location', 'locationX', 'locationY', 'x', 'y');
					break;
				case 'size' :
					value = this.readDataFromInterfaceElement('size', 'sizeX', 'sizeY', 'width', 'height');
					break;
				default :
					value = this.readDataFromInterfaceElement(key, null, null, null, null);
					break;
			}

			if ( (!ignoreNull) && (value === null) ) {
				continue;
			}
			if ( ['coordinate','polygon','areaOfInterest'].includes(key) ) {
				value = this.convertToLeaflet(value);
			}
			this.selection[key] = value;
		}
	}

	updateSelectionToInterface() {
		let data = {
			coordinate : this.convertFromLeaflet(this.selection['coordinate']),
		}
		for ( let key of Object.keys(this.selection) ) {
			let value = this.convertFromLeaflet(this.selection[key]);
			switch(key) {
				case 'coordinate' :
					this.writeDataToInterfaceElement('location', 'locationX', 'locationY', 'x', 'y', value);
					break;
				case 'size' :
					this.writeDataToInterfaceElement('size', 'sizeX', 'sizeY', 'width', 'height', value);
					break;
				default	:
					this.writeDataToInterfaceElement(key, null, null, null, null, value);
					break;
			}
		}
	}

	processSelectionData() {
		//Do things like calculating the central coordinate based on Area Of Interest
	}

	//* VISUALIZATION *//

	redrawVisualization() {
		this.visualizeElement(	'coordinate',		this.selection['coordinate']);
		this.visualizeElement(	'polygon',		this.selection['polygon']);
		this.visualizeElement(	'selectionBounds',	this.selection['selectionBounds']);
	}
	visualizeReset( id = null, all = false ) {
		for (let i of Object.keys(this.visualization) ) {
			if ( (!all) && (i != id) ) {
				continue;
			}
			this.visualization[i].removeFrom(this.leaflet);
			delete this.visualization[i];
		}
	}
	visualizeElement( id, element ) {
		this.visualizeElements( id, [element] );
	}

	visualizeElements( id, elements ) {
		this.visualizeReset( id );
		let style = {};
		try {
			style = this.config.styles[id] ?? {};
		} catch (err)
		{
		}
		let group = new L.LayerGroup();
		for ( let i = 0; i < elements.length ; i++ ) {
			let element = elements[i];
			switch( this.checkDataType(element) ) {
				case 'feature':
					element = L.polygon(element.geometry.coordinates);
					break;
				case 'coordinates':
					element = L.polygon(element)
					break;
				case 'polygon':
					element = L.polygon(element.coordinates)
					break;
				case 'coordinate':
					element = L.marker(element)
					break;
				default:
					element = null;
					break;
			}
			if ( element == null ) {
				continue;
			}
			if ( typeof element.setStyle === 'function' ) {
				element.setStyle(style);
			}
			group.addLayer(element);
			//element.addTo(group);
		}
		this.visualization[id] = group;
		group.addTo(this.leaflet);
	}


	reloadSelectablesLayer() {
		let layer = this.selectables[this.config['selectableLayer']];
		if ( !layer ) {
			return;
		}
		if ( layer.zoomLevel && (layer.zoomLevel > this.leaflet.getZoom()) ) {
			this.visualizeReset('selectables');
			return;
		}
		let bbox = this.leaflet.getBounds().toBBoxString();
		let bboxCrs = 'urn:ogc:def:crs::EPSG::4326';
		let bboxString = bbox+','+bboxCrs;
		let parameters = {
			service : 'wfs',
			request: 'GetFeature',
			outputFormat: 'application/json',
			srsName: 'EPSG:4326',
			bbox: bboxString,
			//maxFeatures: 250
		}
		parameters = L.Util.extend(parameters, layer['parameters']);
		let url = layer['url'] + L.Util.getParamString(parameters);
		let request = $.ajax( {
			url : url,
			dataType : 'json',
		} );
		let self = this;
		request.then(function(e){
			let features = e.features;
			for ( let i = 0; i < features.length ; i++ ) {
				let feature = features[i];
				feature.geometry.coordinates = self.swapCoordinatesXY(feature.geometry.coordinates);
			}
			self.visualizeElements( 'selectables', features );
		});
	}

	//* INTERFACE *//

	readDataFromInterfaceElement( el, elX, elY, keyX, keyY ) {
		let $el		= $(this.inputs[el]);
		let $elX	= $(this.inputs[elX]);
		let $elY	= $(this.inputs[elY]);

		let data = '';
		if ( $el.length > 0 ) {
			try {
				data = $el.val()
				data = JSON.parse(data);
			} catch (err) {
			}
		} else if ( ($elX.length > 0) && ($elY.length > 0) ) {
			let x = $elX.val();
			let y = $elY.val();
			if ( !(x==='' || y==='')) {
				data = {};
				data[keyX] = x;
				data[keyY] = y;
			}
		}
		if (data === '') {
			return null;
		}
		return data;
	}

	writeDataToInterfaceElement( el, elX, elY, keyX, keyY, data ) {
		let $el		= $(this.inputs[el]);
		let $elX	= $(this.inputs[elX]);
		let $elY	= $(this.inputs[elY]);

		if ( $el.length > 0 ) {
			try {
				if (data instanceof Object) {
					data = JSON.stringify(data);
				}
			} catch (err) {
			}
			$el.val(data);
		} else if ( ($elX.length > 0) && ($elY.length > 0) ) {
			$elX.val(data[keyX]);
			$elY.val(data[keyY]);
		}
		return;
	}

	//* LEAFLET INITIALIZATION *//

	setupCreateLeaflet() {
		if (this.leaflet != null) {
			return;
		}
		let mapContainer = $(this.dom['mapContainer']);
		if ( mapContainer.length === 0 ) {
			throw 'No container for Leaflet';
		}
		let id = $(this.dom['mapContainer']).attr('id');
		if (id == '') {
			id = 'leafletMap';
			$(this.dom['mapContainer']).attr('id', id);
		}
		this.leaflet = L.map(id, {crs:L.CRS.EPSG3857});
		this.addClickHandler();
		this.addInterfaceHandler();
		this.addMapChangeHandler();
	}

	setupLeafletBackground() {
		for ( let key of Object.keys(this.config['backgroundLayers']) ) {
			let layer = this.config['backgroundLayers'][key];
			L.tileLayer(layer['layer'], layer['config']).addTo(this.leaflet);
		}

	}

	setupData() {
		this.loadSelectionFromDefaults();
		this.loadSelectionFromInterface(false);
		this.processSelectionData();
		this.updateSelectionToInterface();
	}

	setupStartLeaflet() {
		this.leaflet.invalidateSize();
		this.setCameraToSelection();
	}

	//* INTERFACE CREATION *//

	setupCreateMissingInputs() {
		if ( $(this.dom['inputsParent']).length===0 ) {
			this.dom['inputsParent'] = $('<div></div>')
					.addClass('locationSelectorInputs')
					.css('display','noneTODO')
					.appendTo('body');
		}
		for (let key of Object.keys(this.inputs)) {
			let missing = null;
			missing = this.setupCheckIsInputMissing(key, 'location', 'locationX', 'locationY') ?? missing;
			missing = this.setupCheckIsInputMissing(key, 'size', 'sizeX', 'sizeY') ?? missing;
			if ( missing === null ) {
				missing = this.setupCheckIsInputMissing(key, key);
			}
			if (missing) {
				this.setupCreateMissingInput(key);
			}
		}
	}

	setupCheckIsInputMissing(key, main, X, Y) {
		if (key != main && key != X && key != Y) {
			return null;
		}
		if ( (X === null || Y === null) ) {
			return $(this.inputs[main]).length === 0;
		}
		if ( main === null ) {
			return $(this.inputs[key]).length === 0;
		}
		if ( (this.setupCheckIsInputMissing(X, null, X, Y) || this.setupCheckIsInputMissing(Y, null, X, Y)) ) {
			if ( this.setupCheckIsInputMissing(main, main, null, null) ) {
				return true;
			}
		}
		return false;
	}


	setupCreateMissingInput(key) {
		$('<input></input>').attr('type','hiddenTODO').attr('name',key).appendTo($(this.dom['inputsParent']));
	}

	//* HANDLERS *//

	addClickHandler() {
		let self = this;
		this.leaflet.on('click', function(e){
			let coordinate = e.latlng;
			self.setCoordinate(coordinate);
			self.updateSelectionToInterface();
		});
	}

	addInterfaceHandler() {
		let self = this;
		$(this.dom['inputsParent']).on('change','input',function(e) {
			self.loadSelectionFromInterface(true);
			if (e.target.name == 'polygon') {
				self.createAndSetSelectionFromPolygon();
			} else {
				self.createAndSetSelectionData();
			}
			self.redrawVisualization();
			self.updateSelectionToInterface();
		});
	}

	addMapChangeHandler() {
		let self = this;
		let handlerFunction = function(e) {
			console.log('Zoom to : '+self.leaflet.getZoom());
			self.reloadSelectablesLayer();
		};
		this.leaflet.on('zoomend', handlerFunction);
		this.leaflet.on('moveend', handlerFunction);
	}
}
