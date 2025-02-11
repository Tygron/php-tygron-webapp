
class LocationSelector {

	defaultCrs='3857';
	defaultZoomLevel = 13;

	defaultSize=500;
	defaultBuffer=0;

	defaultCoordinate={ x: 479967, y: 6814662 };

	allowMixedSize=false;

	//selected:
	//CRS
	//Size
	//Buffer
	//Centre coordinate

	//AOI
	//Bounds-of-AOI
	//Bounds-of-project (AOI+buffer)

	selectedCrs=null;
	selectedZoomLevel=null;

	selectedSize=null;
	selectedBuffer=0;

	selectedDefiningLocation=null; // coordinate, area of interest
	selectedCoordinate=null;
	selectedAreaOfInterest=null;

	$domLocationButtonElement = null;
	$domLocationMapElement = null;

	visualizations = {};

	leaflet = null;

	constructor(){};

	// Construction

	createLocationSelectorEmbedded( $selectorParent ) {
		this.$domLocationMapElement = this.generateDomForMapSelectorEmbed();
		this.$domLocationButtonElement = this.$domLocationMapElement;
		$($selectorParent).append( this.$domLocationMapElement );
		this.generateLeaflet();
		this.show();
	}

	createLocationSelector( $selectorParent, $buttonParent ) {

		this.$domLocationMapElement = this.generateDomForMapSelectorPopup();
		this.hide();
		$($selectorParent).append( this.$domLocationMapElement );

		this.$domLocationButtonElement = this.generateDomForButton();
		$($buttonParent).append( this.$domLocationButtonElement );

		this.generateLeaflet();
		this.setCameraLocation();
	}

	show() {
		this.load();

		this.$domLocationMapElement.show();
		this.leaflet.invalidateSize(true);
		if (!this.setCameraToBounds()) {
			this.setCameraLocation();
		}
	}

	hide( save ) {
		if ( save ) {
			this.save();
		}
		this.$domLocationMapElement.hide();
	}

	load() {
		this.setDataFromInterface( this.$domLocationButtonElement.parents('form') );
		this.recalculateSelected();
		this.refresh();
	}

	save() {
		this.setDataToInterface( this.$domLocationButtonElement.parents('form') );
	}



	//Camera functions

	setCameraLocation( crs, xy, zoomLevel ) {
		crs = crs==null ? this.getCrs() : crs;
		xy = xy==null ? this.getCoordinate() : xy;
		zoomLevel = zoomLevel==null ? this.defaultZoomLevel : zoomLevel;

		let coordinate = this.convertCoordinateToLeaflet(crs, xy);
		this.leaflet.setView( coordinate, zoomLevel );
	}

	setCameraToBounds( ) {
                let coordinate = this.getCoordinate();
                let crs = this.getCrs();
                let size = this.getSize();
                if ( coordinate == null || crs == null || size == null) {
                        return false;
                }

                let bounds = this.computeBoundsFromCoordinate(crs, coordinate, size.width, size.height);
		if (bounds == null) {
			return false;
		}

		this.leaflet.fitBounds( this.convertCoordinatesToLeaflet( this.getCrs(), bounds), {
				'padding':[0,0],
			});
		return true;
	}


	// Getting selection

	getCrs() {
		return this.selectedCrs ?? this.defaultCrs;
	}
	getZoomlevel() {
		return this.selectedZoomLevel ?? this.defaultZoomLevel;
	}
	getSize() {
		let size = this.selectedSize ?? this.defaultSize;
		if ( typeof size == 'number') {
			return {width:size, height: size};
		}
		return size;
	}
	getWidth() {
		return getSize()[0];
	}
	getHeight() {
		return getHeight()[0];
	}
	getBuffer() {
		return this.getSelectedBuffer ?? this.defaultBuffer;
	}
	getDefiningLocation() {
		return this.selectedDefiningLocation;
	}
	getCoordinate() {
		return this.selectedCoordinate ?? this.defaultCoordinate;
	}
	getAreaOfInterest() {
		return this.selectedAreaOfInterest;
	}



	// Updating selection

	setSelectedCrs(crs) {
		if ( crs != null ) {
			this.selectedCrs = crs;
		}
	}
	setSelectedSize(width, height) {
		if (width !=null) {
			if ( height != null ) {
				this.selectedSize = {width: width, height: height};
			} else {
				this.selectedSize = {width: width, height: width};
			}
		//} else if (this.selectedAOI != null) {
		//	console.log('TODO: Compute Area of Interest Size')
		}
	}
	setSelectedBuffer(buffer) {
		if (buffer != null) {
			this.selectedBuffer = buffer;
		}
	}
	setSelectedDefiningLocation( location ) {
		if (location == null) {
			return;
		}
		if ( typeof location.x == 'number' && typeof location.y == 'number' ) {
			this.selectedDefiningLocation = location;
		} else {
			console.log('TODO: Polygon for defining area of interest');
		}
	}

	setComputedCoordinate(x, y) {
		if (x != null && y != null) {
			this.selectedCoordinate={x:x, y:y};
		}

	}
	setComputedAreaOfInterest( areaOfInterest ) {
		if (areaOfInterest == null) {
			if ( typeof areaOfInterest == 'string' ) {
				try {
					areaOfInterest = JSON.parse(areaOfInterest);
				} catch (err) {
				}
			}
			this.selectedAreaOfInterest = areaOfInterest;
		}
	}

	recalculateSelected() {
		if ( this.selectedDefiningLocation == null ) {
			this.selectedCoodinate = this.defaultCoordinate;
		} else if ( typeof this.selectedDefiningLocation.x == 'number' && typeof this.selectedDefiningLocation.y == 'number' ) {
			this.selectedCoordinate = this.selectedDefiningLocation;
		}
		if ( true ) { //Check whether to generate area
			this.selectedAreaOfInterest = this.computeBoundingBoxGeometryFromCoordinate(
					this.getCrs(),
					this.selectedCoordinate,
					this.getSize().width,
					this.getSize().height,
				);
		}
	}

	// Refreshes after data or interface updates

	refresh() {
		this.refreshInterface();
		this.refreshVisualization();
	}

	refreshInterface() {
		this.setDataToInterface( this.$domLocationMapElement );
	}

	refreshVisualization() {
		this.clearVisualization();
		this.drawVisualizationCoordinate();
		this.drawVisualizationSizeBounds();
		this.drawVisualizationInterestBounds();
	}

	clearVisualization() {
		for (let key of Object.keys(this.visualizations)) {
			this.drawElement(key);
		}
		this.visualization = {};
	}
	drawVisualizationCoordinate() {
		let coordinate = this.selectedCoordinate;
		if ( coordinate == null ) {
			return;
		}
		let leafletCoordinate = this.convertCoordinateToLeaflet(
				this.selectedCrs,
				this.selectedCoordinate
			);
		let marker = L.marker(leafletCoordinate);
		this.drawElement('coordinate', marker);
	}
	drawVisualizationAreaOfInterest() {

	}
	drawVisualizationSizeBounds() {
		let coordinate = this.selectedCoordinate;
		if ( coordinate == null) {
			return;
		}

		let crs = this.getCrs();
		let size = this.getSize();
		let geometry = this.computeBoundingBoxGeometryFromCoordinate(crs, coordinate, size.width, size.height);
		geometry = L.polygon( this.convertGeometryToLeaflet(crs, geometry).coordinates );
		this.drawElement('sizeBounds', geometry);
	}
	drawVisualizationInterestBounds() {

	}
	drawElement( id, element ) {
		if ( id == null ) {
			return;
		}

		if (this.visualizations[id] != null) {
			try{
				this.visualizations[id].removeFrom(this.leaflet);
				delete this.visualizations[id];
			} catch (err) {
			}
		}

		if (element != null) {
			this.visualizations[id] = element;
			element.addTo(this.leaflet);
		}
	}


	// Geometry conversions

	convertCoordinateToLeaflet( crs, coordinate ) {
		if (crs == null) {
			crs = this.defaultCrs;
		}
		let crsObj = L.CRS['EPSG'+crs];
		if ( Array.isArray(coordinate) ) {
			coordinate = L.point(coordinate);
		}
		let leafletCoordinate = crsObj.unproject(coordinate);

		return leafletCoordinate;
	}
	convertCoordinateFromLeaflet( crs, leafletCoordinate ) {
		if (crs == null) {
			crs = this.defaultCrs;
		}
		let crsObj = L.CRS['EPSG'+crs];
		leafletCoordinate = L.latLng(leafletCoordinate);
		let coordinate = crsObj.project(leafletCoordinate);
		return coordinate;
	}

	convertCoordinatesToLeaflet( crs, coordinates) {
		let output = [];
		for ( let i=0; i<coordinates.length;i++ ) {
			output[i] = this.convertCoordinateToLeaflet( crs, coordinates[i] );
		}
		return output;
	}
	convertCoordinatesFromLeaflet(crs, leafletCoordinates ) {
		let output = [];
		for ( let i=0; i<leafletCoordinates.length;i++ ) {
			output[i] = this.convertCoordinateFromLeaflet( crs, leafletCoordinates[i] );
		}
		return output;
	}

	convertGeometryToLeaflet( crs, geometry ) {
		let newGeometry = JSON.parse(JSON.stringify(geometry));
//		let coords3 = [];
//		for (let k=0; k<geometry.coordinates.length;k++) {
		let coords2 = [];
		for (let j=0; j<geometry.coordinates.length;j++) {
			let coords1 = [];
			for (let i=0; i<geometry.coordinates[j].length;i++) {
				let newCoord = this.convertCoordinateToLeaflet( crs, geometry.coordinates[j][i] );
				coords1.push( [newCoord.lat, newCoord.lng] );
			}
			coords2.push(coords1);
		}
		//coords3.push(coords2);
		newGeometry.coordinates = coords2;
//		}
//		newGeometry.coordinates = coords3;

		return newGeometry;
	}
	convertGeometryFromLeaflet( crs, geoJson ) {
		let newGeoJson = JSON.parse(JSON.stringify(geoJson));
		let newCoordinates = [];
		for (let i=0; i<geoJson.geometry.coordinates.length;i++) {
			let newCoordinate = this.convertCoordinateFromLeaflet( crs, geoJson.geometry.coordinates[i] );
			newCoordinates.push( [newCoordinate.x, newCoordinate.y] );
		}
		newGeoJson.geometry.coordinates = newCoordinates;

		return newGeoJson;
	}
	convertPolygonGeometryToMultiPolygon( geometry ) {
		geometry.coordinates = [geometry.coordinates];
		geometry.type = 'MultiPolygon';
		return geometry;
	}


	// Geometry calculations

	computeBoundsFromCoordinate( crs, coordinate, width, height ) {
		if (coordinate == null || width == null || height == null) {
			return null;
		}

		let leafletCoordinate = this.convertCoordinateToLeaflet( crs, coordinate );
		let earthRadiusLatMeters = 40008000; //y
		let earthRadiusLngMeters = 40075000; //x

		let earthRadiusModifier = Math.cos(leafletCoordinate.lat*(Math.PI/180))
		let earthRadiusLngAtLatMeters = earthRadiusLngMeters*earthRadiusModifier;

		let halfWidth = width/2;
		let halfHeight = height/2;

		let halfLat = (halfHeight*360)/earthRadiusLatMeters;
		let halfLng = (halfWidth*360)/earthRadiusLngAtLatMeters;

		let boundsCoordinates = [
				[leafletCoordinate.lat-halfLat, leafletCoordinate.lng-halfLng],
				[leafletCoordinate.lat-halfLat, leafletCoordinate.lng+halfLng],
				[leafletCoordinate.lat+halfLat, leafletCoordinate.lng+halfLng],
				[leafletCoordinate.lat+halfLat, leafletCoordinate.lng-halfLng],
			];
		if ( crs != null ) {
			boundsCoordinates = this.convertCoordinatesFromLeaflet(crs, boundsCoordinates);
		}
		return boundsCoordinates;
	}

	computeBoundingBoxGeometryFromCoordinate( crs, coordinate, width, height ) {
		let boundsCoordinates = this.computeBoundsFromCoordinate( crs, coordinate, width, height );
		if (boundsCoordinates == null) {
			return null;
		}
		//if ( crs != null ) {
		//	boundsCoordinates = this.convertCoordinatesToLeaflet( crs, boundsCoordinates );
		//}

		let geoJSONCoordinates = [
				[boundsCoordinates[0].x, boundsCoordinates[0].y],
				[boundsCoordinates[1].x, boundsCoordinates[1].y],
				[boundsCoordinates[2].x, boundsCoordinates[2].y],
				[boundsCoordinates[3].x, boundsCoordinates[3].y],
			];

		let geometry = {
				type: 'Polygon',
				coordinates: [geoJSONCoordinates],
			};

		return geometry;
	}
	computeGeoJSONBoundingBoxFromCoordinate( crs, coordinate, width, height ) {
		let boundsCoordinates = this.computeBoundsFromCoordinate( crs, coordinate, width, height );
		if (boundsCoordinates == null) {
			return null;
		}
		//if ( crs != null ) {
		//	boundsCoordinates = this.convertCoordinatesToLeaflet( crs, boundsCoordinates );
		//}

		let geoJSONCoordinates = [
				[boundsCoordinates[0].x, boundsCoordinates[0].y],
				[boundsCoordinates[1].x, boundsCoordinates[1].y],
				[boundsCoordinates[2].x, boundsCoordinates[2].y],
				[boundsCoordinates[3].x, boundsCoordinates[3].y],
			];

		//console.log('Verify size');
		//console.log('Width (1):  ' + L.latLng(boundsCoordinates[0]).distanceTo(L.latLng(boundsCoordinates[1])) );
		//console.log('Width (2):  ' + L.latLng(boundsCoordinates[2]).distanceTo(L.latLng(boundsCoordinates[3])) );
		//console.log('Height (1): ' + L.latLng(boundsCoordinates[0]).distanceTo(L.latLng(boundsCoordinates[3])) );
		//console.log('Height (2): ' + L.latLng(boundsCoordinates[1]).distanceTo(L.latLng(boundsCoordinates[2])) );

		let geoJSONPolygon = {
				type: 'Feature',
				geometry: {
						type: 'Polygon',
						coordinates: [geoJSONCoordinates],
					}
			};

		return geoJSONPolygon;
	}






	// Data exchange between object and interface
	setDataFromInterface( $parent ) {
		let locationX = '[name=\'locationX\']';
		let locationY = '[name=\'locationY\']';
		let sizeX = '[name=\'sizeX\']';
		let sizeY = '[name=\'sizeY\']';
		if ( $parent.find(sizeX).length == 0 ) {
			sizeX = '[name=\'size\']';
		}

		let areaOfInterest = '[name=\'areaOfInterest\']';
		let definingLocation = '[name=\'definingLocation\']';

		this.setSelectedCrs(null);
		this.setSelectedSize(
				$parent.find(sizeX).val() ??
					$parent.find(size).val(),
				$parent.find(sizeY).val(),

			);

		this.setSelectedDefiningLocation($parent.find(definingLocation).val());

		this.setComputedAreaOfInterest( $parent.find(areaOfInterest).val() );
		this.setComputedCoordinate(
				$parent.find(locationX).val(),
				$parent.find(locationY).val()
			);

	}

	setDataToInterface ( $parent ) {
		let locationX = '[name=\'locationX\']';
		let locationY = '[name=\'locationY\']';
		let sizeX = '[name=\'sizeX\']';
		let sizeY = '[name=\'sizeY\']';
		if ( $parent.find(sizeX).length == 0 ) {
			sizeX = '[name=\'size\']';
		}

		let areaOfInterest = '[name=\'areaOfInterest\']';
		let definingLocation = '[name=\'definingLocation\']';

		$parent.find(locationX).val( this.getCoordinate().x );
		$parent.find(locationY).val( this.getCoordinate().y );

		$parent.find(sizeX).val( this.getSize().width );
		$parent.find(sizeY).val( this.getSize().height );

		$parent.find(definingLocation).val( this.getDefiningLocation() );
		//$parent.find(areaOfInterest).val( this.getAreaOfInterest() );
		$parent.find(areaOfInterest).val( JSON.stringify(this.convertPolygonGeometryToMultiPolygon(this.getAreaOfInterest())) );
	}









	// Application handlers

	interfaceUpdateHandle() {
		this.setDataFromInterface( this.$domLocationMapElement );
		this.updateHandle();
	}
	mapUpdateHandle() {
		this.updateHandle();
	}
	updateHandle() {
		this.recalculateSelected();
		this.refresh();
	}




	//Creation of application

	generateLeaflet() {
		if ( this.leatlet != null ) {
			return;
		}
		$('.leafletMap').attr('id','leafletMap');
		let leafletMap = L.map('leafletMap',{
				crs:L.CRS.EPSG3857
			});
		this.leaflet = leafletMap;

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(leafletMap);
		this.generateClickHandler();

		this.leaflet.invalidateSize(true);
		this.setCameraLocation(this.selectedCrs, this.selectedCoordinate, this.selectedZoomlevel);
	}

	generateClickHandler() {
		let self = this;
		this.leaflet.on('click', function(e){
			let leafletCoordinate = e.latlng;
			let coordinate = self.convertCoordinateFromLeaflet(null, leafletCoordinate);
			self.setSelectedDefiningLocation( coordinate );
			self.mapUpdateHandle();
		});
	}






	//Creation of DOM

	generateDomForButton() {
		let self = this;
		let $buttons = this.generateInputElement( null,
				{
						'name'		:	'Select',
						'value'		:	'Location Selection',
						'type'		:	'button',
					}, 'click', function(){
							self.show();
						}
					);
			;
		return $buttons;
	}

	generateDomForMapSelectorEmbed() {
		let $embed = $('<div></div>').addClass('embedElement');
		$embed.append(this.generateDomForMapSelector());
		return $embed;
	}

	generateDomForMapSelectorPopup() {
		let $pageCover = $('<div></div>').addClass('coverElement');
		$pageCover.append(this.generateDomForMapSelector());
		return $pageCover;
	}

	generateDomForMapSelector() {
		let self = this;
		let $outerDom = $('<div></div>').addClass('locationSelector');

		let $closeButton = $('<div></div>').addClass('closeButton');


		let $innerDom = $('<div></div>').addClass('elementsContainer');

		let $leafletContainer = $('<div></div>').addClass('leafletContainer')
		let $leafletMap = $('<div></div>').addClass('leafletMap')

		let $buttonsContainer = $('<div></div>').addClass('buttonsContainer');
		let $buttonsLeft = $('<div></div>').addClass('containerLeft');
		let $buttonsRight = $('<div></div>').addClass('containerRight');

		let $buttonsLocation = $('<div></div>').addClass('containerlocation');
		let $buttonsSize = $('<div></div>').addClass('containerSize');
		let $buttonsData = $('<div></div>').addClass('containerData');
		let $buttonsOutput = $('<div></div>').addClass('containerOutput');

		$buttonsLocation.append(
				this.generateInputElement( 'location X', {
						'name'		:	'locationX',
						'type'		:	'number',
						'readonly'	:	true,
						'value'		:	this.getCoordinate().x,
					}, 'change', function() {
							self.interfaceUpdateHandle();
						}
					),
				this.generateInputElement( 'location Y', {
						'name'		:	'locationY',
						'type'		:	'number',
						'readonly'	:	true,
						'value'		:	this.getCoordinate().y,
					}, 'change', function() {
							self.interfaceUpdateHandle();
						}
					),
			);
		if (this.allowMixedSize) {
			$buttonsSize.append(
					this.generateInputElement( 'width', {
							'name'		:	'sizeX',
							'type'		:	'number',
							'min'		:	500,
							'max'		:	5000,
							'value'		:	this.getSize().width,
						}, 'change', function() {
								self.interfaceUpdateHandle();
							}
						),
					this.generateInputElement( 'height', {
							'name'		:	'sizeY',
							'type'		:	'number',
							'min'		:	500,
							'max'		:	5000,
							'value'		:	this.getSize().height,
						}, 'change', function() {
								self.interfaceUpdateHandle();
							}
						),
				);
		} else {
			$buttonsSize.append(
					this.generateInputElement( 'size', {
							'name'		:	'size',
							'type'		:	'number',
							'min'		:	500,
							'max'		:	5000,
							'value'		:	this.getSize().width,
						}, 'change', function() {
								self.interfaceUpdateHandle();
							}
						),
				);

		}

		$buttonsData.append(
				this.generateInputElement( 'Area Of Interest', {
						'name'		:	'areaOfInterest',
						'type'		:	'text',
						'readonly'	:	true,
						'value'		:	this.getAreaOfInterest(),
					}, 'change', function() {
							self.interfaceUpdateHandle();
						}
					),
				this.generateInputElement( 'Buffer', {
						'name'		:	'buffer',
						'type'		:	'number',
						'readonly'	:	true,
						'min'		:	0,
						'value'		:	this.getBuffer(),
					}, 'change', function() {
							self.interfaceUpdateHandle();
						}
					),
			);


		$buttonsOutput.append(
				this.generateInputElement( null, {
						'name'		:	'locationSelect',
						'value'		:	'Select',
						'type'		:	'button',
					}, 'click', function() {
							self.hide(true);
						}
					).addClass('popupOnly'),
			);

		$leafletContainer.append($leafletMap);

		$buttonsLeft.append($buttonsLocation, $buttonsSize, $buttonsData);
		$buttonsRight.append($buttonsOutput);
		$buttonsContainer.append($buttonsLeft, $buttonsRight);

		$innerDom.append($leafletContainer, $buttonsContainer);
		$outerDom.append($innerDom, $closeButton);
		return $outerDom;
	}

	generateInputElement( label, inputProperties, handleType, handler ) {
		let $element = 	$(	'<div></div>'		);
		let $label = 	$(	'<label></label>'	);
		let $span = 	$(	'<span></span>'		).text(label);
		let $input = 	$(	'<input></input>'	).attr(inputProperties ?? {});
		if ( label !== null ) {
			$label.append($span);
		}
		$label.append($input);
		$element.append($label);

		if (handleType != null && handler != null) {
			$input.on(handleType, handler);
		}
		return $element;
	}
}
