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

		// inpput only: the zoom level
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
		this.setupAddHandlers();
		this.setupStartLeaflet();
		console.log(this);
		console.log(this.selection);
	}

	setupSettings( settings ) {
		if ( settings == null ) {
			return;
		}
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

	clearAreaOfInterest() {
		this.createAndSetSelectionClearPolygon();
                this.redrawVisualization();
                this.updateSelectionToInterface();
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
			areaOfInterest = this.computeMultiPolygonsFromGeometries(areaOfInterest);
			this.selection['areaOfInterest'] = areaOfInterest;
		}
	}

	createAndSetSelectionFromPolygon() {
		if (this.selection['polygon'] != null) {
			this.selection['coordinate'] = this.computeCenterFromGeometry(this.selection['polygon']);
			this.createAndSetSizeFromPolygon();
			let areaOfInterest = this.computeGeometriesFromFeatures(this.selection['polygon']);
			areaOfInterest = this.computeMultiPolygonsFromGeometries(areaOfInterest);
			this.selection['areaOfInterest'] = areaOfInterest;
		}
		this.createAndSetSelectionFromCoordinate();
	}
	createAndSetSelectionClearPolygon() {
		this.selection['polygon'] = null;
		this.selection['areaOfInterest'] = null;
		this.selection['areaOfInterestAttributes'] = null;
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

	convertToLeaflet( data, crs = null ) {
		if ( data === null ) {
			return null;
		}
		if (crs === null) {
			crs = data;
		}
		switch (this.checkDataType(data)) {
			case 'coordinate'	:	return this.convertCoordinateToLeaflet(data, crs);
			case 'coordinates'	:	return this.convertCoordinatesToLeaflet(data. crs);
			case 'polygon'		:	return this.convertPolygonToLeaflet(data, crs);
			case 'multipolygon'	:	return this.convertMultiPolygonToLeaflet(data, crs);
			case 'geometries'	:	return this.convertGeometriesToLeaflet(data, crs);
			case 'feature'		:	return this.convertFeatureToLeaflet(data, crs);
			case 'features'		:	return this.convertFeaturesToLeaflet(data, crs);
		}
		return data;
	}

	convertFromLeaflet( data, crs = null ) {
		if ( data === null ) {
			return null;
		}
		switch (this.checkDataType(data)) {
			case 'coordinate'	:	return this.convertCoordinateFromLeaflet(data, crs);
			case 'coordinates'	:	return this.convertCoordinatesFromLeaflet(data, crs);
			case 'polygon'		:	return this.convertPolygonFromLeaflet(data, crs);
			case 'multipolygon'	:	return this.convertMultiPolygonFromLeaflet(data, crs);
			case 'geometries'	:	return this.convertGeometriesFromLeaflet(data, crs);
			case 'feature'		:	return this.convertFeatureFromLeaflet(data, crs);
			case 'features'		:	return this.convertFeaturesFromLeaflet(data, crs);
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
			if ( this.checkDataType(data[0]) === 'polygon' || this.checkDataType(data[0]) == 'multipolygon' ) {
				return 'geometries';
			}
			if ( this.checkDataType(data[0]) === 'feature' ) {
				return 'features';
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
		if ( coordinate.type == 'Feature' ) {
			return {
				'type' : coordinate.type,
				'properties' : coordinate.properties,
				'geometry' : this.swapCoordinatesXY(coordinate.geometry),
			};
		} else if ( coordinate.type != null ) {
			return {
				'type' : coordinate.type,
				'coordinates' : this.swapCoordinatesXY(coordinate.coordinates),
			};
		}

		if ( (coordinate.lat != null) || (coordinate.x != null) ) {
			//If the coordinate is clear on direction, don't change it
			return coordinate;
		}

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
		throw new Error('Could not parse structure of coordinate object.')
	}

	getCrsObject(crs = null) {
		if ( crs != null) {

			if (crs['crs'] != null ) {
				try {
					return L.CRS['EPSG'+crs['crs']];
				} catch (err) {
					console.err('Requested invalid crs: '+crs['crs']);
				}
			} else if ( ['string','number'].includes(typeof crs) ) {
				try {
					return L.CRS['EPSG'+crs];
				} catch (err) {
					console.err('Requested invalid crs: '+crs);
				}
			}
		}

		crs = this.selection['crs'];
		return L.CRS['EPSG'+crs];
	}

	convertCoordinateToLeaflet(coordinate, crs = null, arrayType = 'xy', arrayOutput = null) {
		let crsObj = this.getCrsObject(crs);

		if ( Array.isArray(coordinate) ) {
			if (arrayType=='yx') {
				coordinate = L.point([coordinate[1],coordinate[0]]);
			} else {
				coordinate = L.point(coordinate)
			}
		}
		let leafletCoordinate = crsObj.unproject(coordinate);

		if ( arrayOutput == 'xy') {
			leafletCoordinate = [leafletCoordinate.lng, leafletCoordinate.lat];
		} else if ( arrayOutput == 'yx' ) {
			leafletCoordinate = [leafletCoordinate.lat, leafletCoordinate.lng];
		}

		return leafletCoordinate;
	}
	convertCoordinateFromLeaflet(leafletCoordinate, crs = null, arrayType = 'yx', arrayOutput = 'xy') {
		let crsObj = this.getCrsObject(crs);

		if ( Array.isArray(leafletCoordinate) ) {
			if ( arrayType == 'xy' ) {
				leafletCoordinate = [leafletCoordinate[1], leafletCoordinate[0]];
			} else if ( arrayType == 'yx') {
				leafletCoordinate = [leafletCoordinate[0], leafletCoordinate[1]];
			}
		}

		leafletCoordinate = L.latLng(leafletCoordinate);
		let coordinate = crsObj.project(leafletCoordinate);

		if ( arrayOutput == 'yx' ) {
			return [coordinate.y, coordinate.x];
		} else if ( arrayOutput == 'xy') {
			return [coordinate.x, coordinate.y];
		}
		return coordinate;
	}

	convertCoordinatesToLeaflet(coordinates, crs = null, arrayType = 'xy', arrayOutput = null ) {
		let output = [];
		for ( let i=0 ; i<coordinates.length ; i++ ) {
			output[i] = this.convertCoordinateToLeaflet( coordinates[i], crs, arrayType, arrayOutput );
		}
		return output;
	}
	convertCoordinatesFromLeaflet(leafletCoordinates, crs = null, arrayType = 'yx', arrayOutput = 'xy' ) {
		let output = [];
		for ( let i=0 ; i<leafletCoordinates.length ; i++ ) {
			output[i] = this.convertCoordinateFromLeaflet( leafletCoordinates[i], crs, arrayType, arrayOutput );
		}
		return output;
	}
	convertPolygonToLeaflet(polygon, crs = null) {
		let coordinatesSets = polygon.coordinates;
		if ( polygon.type === 'MultiPolygon' ) {
			coordinatesSets = coordinatesSets[0];
		}
		let convertedCoordinateSets = [];
		for ( let i of Object.keys(coordinatesSets) ) {
			convertedCoordinateSets[i] = this.convertCoordinatesToLeaflet( coordinatesSets[i], crs, 'xy', 'yx' );
		}
		return {
			'type' : 'Polygon',
			'coordinates' : convertedCoordinateSets,
		}
	}
	convertPolygonFromLeaflet(polygon, crs = null) {
		let coordinatesSets = polygon.coordinates;
		let convertedCoordinateSets = [];
		for ( let i of Object.keys(coordinatesSets) ) {
			convertedCoordinateSets[i] = this.convertCoordinatesFromLeaflet( coordinatesSets[i], crs, 'yx', 'xy' );
		}
		return {
			'type' : 'Polygon',
			'coordinates' : convertedCoordinateSets,
		}

	}
	convertMultiPolygonToLeaflet(multipolygon, crs = null) {
		let coordinatesSets = multipolygon.coordinates;
		let convertedCoordinatesSets = [];
		for ( let j of Object.keys(coordinatesSets) ) {
			let coordinatesSet = coordinatesSets[j];

			let convertedCoordinatesSet = [];
			for ( let i of Object.keys(coordinatesSet) ) {
				convertedCoordinatesSet[i] = this.convertCoordinatesToLeaflet( coordinatesSet[i], crs, 'xy', 'yx' );
			}
			convertedCoordinatesSets[j] = convertedCoordinatesSet;
		}
		return {
			'type' : 'MultiPolygon',
			'coordinates' : convertedCoordinatesSets,
		}
	}
	convertMultiPolygonFromLeaflet(multipolygon, crs = null) {
		let coordinatesSets = multipolygon.coordinates;
		let convertedCoordinatesSets = [];
		for ( let j of Object.keys(coordinatesSets) ) {
			let coordinatesSet = coordinatesSets[j];

			let convertedCoordinatesSet = [];
			for ( let i of Object.keys(coordinatesSet) ) {
				convertedCoordinatesSet[i] = this.convertCoordinatesFromLeaflet( coordinatesSet[i], crs, 'yx', 'xy' );
			}
			convertedCoordinatesSets[j] = convertedCoordinatesSet;
		}
		return {
			'type' : 'MultiPolygon',
			'coordinates' : convertedCoordinatesSets,
		}

	}
	convertGeometriesToLeaflet(geometries, crs = null) {
		return this.convertFeaturesToLeaflet(geometries, crs);
	}
	convertGeometriesFromLeaflet(geometries, crs = null) {
		return this.convertFeaturesFromLeaflet(geometries, crs);
	}
	convertFeatureToLeaflet(feature, crs = null) {
		return feature = {
			'type' : 'Feature',
			'properties' : feature['properties'] ?? {},
			'geometry' : this.convertToLeaflet(feature.geometry, crs),
		}
	}
	convertFeatureFromLeaflet(feature, crs = null) {
		return feature = {
			'type' : 'Feature',
			'properties' : feature['properties'] ?? {},
			'geometry' : this.convertFromLeaflet(feature.geometry, crs),
		}
	}
	convertFeaturesToLeaflet(features, crs = null) {
		let converted = [];
		for ( let i of Object.keys(features) ) {
			converted[i] = this.convertToLeaflet(features[i], crs);
		}
		return converted;
	}
	convertFeaturesFromLeaflet(features, crs = null) {
		let converted = [];
		for ( let i of Object.keys(features) ) {
			converted[i] = this.convertFromLeaflet(features[i], crs);
		}
		return converted;
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

	computeGeometriesFromFeatures( features ) {
		if ( features == null ) {
			return null;
		}
		let geometries = [];
		if ( !Array.isArray(features) ) {
			features = [features];
		}
		for ( let i of Object.keys(features) ) {
			let feature = features[i];
			if ( this.checkDataType(feature) == 'feature' ) {
				geometries[i] = feature.geometry;
			} else {
				geometries[i] = feature;
			}
		}
		return geometries;
	}

	computeMultiPolygonsFromGeometries( geometries ) {
		let mps = [];
		if ( !Array.isArray(geometries) ) {
			geometries = [geometries];
		}
		for ( let i of Object.keys(geometries) ) {
			let geometry = geometries[i];
			let mp = {};
			switch( this.checkDataType(geometry) ) {
				case 'multipolygon':
					mp = geometry;
					break;
				case 'polygon':
					mp = {
						'type':'MultiPolygon',
						'coordinates' : [geometry.coordinates]
					};
					break;
			}
			mps[i] = mp;
		}
		return mps;
	}

	computeCenterFromGeometry( geometry ) {
		//if ( Array.isArray(geometry) ) {
			geometry = this.swapCoordinatesXY(geometry);
		//}
		return L.geoJson(geometry).getBounds().getCenter();

		geometry = {
			'type': geometry.type,
			'coordinates' : this.swapCoordinatesXY(geometry.coordinates),
		};
		return L.geoJson(geometry).getBounds().getCenter();
	}

	computeBoundsFromGeometry( geometry ) {
		let bounds = L.geoJson(geometry).getBounds();
		return [
				[bounds.getNorth(), bounds.getWest()],
				[bounds.getNorth(), bounds.getEast()],
				[bounds.getSouth(), bounds.getEast()],
				[bounds.getSouth(), bounds.getWest()],
			];
	}

	computeSizeFromGeometry( geometry ) {
		if ( !Array.isArray(geometry) ) {
			geometry = [geometry];
		} else {
			geometry = this.swapCoordinatesXY(geometry);
		}
		let coordinates = geometry.coordinates;
		let bounds = L.geoJson(geometry).getBounds();

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

	computeCrsFromGeoJson( geojson ) {
		try {
			let crsName = geojson.crs.properties.name;
			let crs = crsName.replace('urn:ogc:def:crs:EPSG::','');
			return crs;
		} catch (err) {
			throw err;
		}
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
		//TODO:
		//Calculate central coordinate from Area of Interest
		//Calculate selectionBounds from coordinate and size
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
	visualizeElement( id, element, handler = null ) {
		this.visualizeElements( id, [element], handler );
	}

	visualizeElements( id, elements, handler = null ) {
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
			let multipleElements = false;
			switch( this.checkDataType(element) ) {
				case 'geometries':
				case 'features':
					element = this.swapCoordinatesXY(element);
					element = L.geoJSON(element);
					multipleElements = true;
					break;
				case 'feature':
					element = L.polygon(element.geometry.coordinates);
					break;
				case 'coordinates':
					element = L.polygon(element);
					break;
				case 'polygon':
					element = L.polygon(element.coordinates);
					break;
				case 'multipolygon':
					element = L.geoJSON(this.swapCoordinatesXY(element));
					break;
				case 'coordinate':
					element = L.marker(element);
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
			if ( typeof handler === 'function' ) {
				if ( multipleElements ) {
					element.eachLayer(function(layer){
						layer.on('click', handler);
					});
				} else {
					element.on('click', handler);
				}
			} else {
				element.options.interactive = false;
			}
			group.addLayer(element);
			//element.addTo(group);
		}
		this.visualization[id] = group;
		group.addTo(this.leaflet);
	}


	reloadSelectablesLayer() {
		let selectedLayerKey = this.config['selectableLayer'];
		if (selectedLayerKey == null || selectedLayerKey == '') {
			this.visualizeElement( 'selectables', null );
			return;
		}
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
			console.log(e.features.length);
			let features = e.features;
			if ( layer['swapxy'] ) {
				features = self.swapCoordinatesXY(features);
			}
			self.visualizeElement( 'selectables', features, function(e){self.selectHandler(e);} );
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
			if ( (typeof data == 'object') && (keyX in data) && (keyY in data) ) {
				$elX.val(data[keyX]);
				$elY.val(data[keyY]);
			} else {
				$elX.val(data);
				$elY.val(data);
			}
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

	setupAddHandlers() {
		let self = this;
		this.leaflet.on('click', function(e){self.clickHandler(e)});

		this.leaflet.on('zoomend', function(e){self.zoomHandler(e)});
		this.leaflet.on('moveend', function(e){self.moveHandler(e)});

		$(this.dom['inputsParent']).on('change','input', function(e){self.interfaceHandler(e)});
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
		$('<input></input>').attr('type','hidden').attr('name',key).appendTo($(this.dom['inputsParent']));
	}

	//* HANDLERS *//

	clickHandler(e) {
		let coordinate = e.latlng;
		this.setCoordinate(coordinate);
		this.updateSelectionToInterface();
	}

	interfaceHandler(e) {
		this.loadSelectionFromInterface(true);
		if (e != null && e.target.name == 'polygon') {
			this.createAndSetSelectionFromPolygon();
		} else {
			this.createAndSetSelectionData();
		}
		this.redrawVisualization();
		this.updateSelectionToInterface();
	}

	uploadHandler(e) {
		if ( e == null || e.target.files.length == 0 ) {
			return;
		}
		let file = e.target.files[0];

		let self = this;
		file.text().then( function(fileText) {
			let geojson = JSON.parse(fileText);
			let crs = self.computeCrsFromGeoJson(geojson);
			let geometries = geojson.features ?? null;
			if ( !geometries || geometries.length == 0 ) {
				return;
			}
			self.setPolygon(self.convertToLeaflet(geometries));
			self.createAndSetSelectionFromPolygon();
			self.updateSelectionToInterface();
		});
	}

	zoomHandler(e) {
		this.selection['zoomLevel'] = this.leaflet.getZoom();
		this.updateSelectionToInterface();
		this.reloadSelectablesLayer();
	}
	moveHandler(e) {
		this.reloadSelectablesLayer();
	}

	selectHandler(e) {
		console.log(e.target);
		console.log(e.target.feature.id);
		let geometry = e.target.feature.geometry;
		geometry = this.swapCoordinatesXY(geometry);
		this.setPolygon(geometry);
		this.createAndSetSelectionFromPolygon();
		this.redrawVisualization();
		this.updateSelectionToInterface();
	}
}
