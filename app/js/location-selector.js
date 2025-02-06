
class LocationSelector {

	defaultCrs='3857';
	defaultCoordinate={ x: 479967, y: 6814662 };
	defaultZoomLevel = 13;
	defaultSize=500;

	allowMixedSize=false;

	selectedCrs=null;
	selectedCoordinate=null;
	selectedZoomLevel=null;
	selectedWidth=null;
	selectedHeight=null;

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
		this.setData(
				null,
				this.$domLocationButtonElement.parents('form').find('[name=\'locationX\']').val(),
				this.$domLocationButtonElement.parents('form').find('[name=\'locationY\']').val(),
				this.$domLocationButtonElement.parents('form').find('[name=\'sizeX\']').val() ??
					this.$domLocationButtonElement.parents('form').find('[name=\'size\']').val(),
				this.$domLocationButtonElement.parents('form').find('[name=\'sizeY\']').val(),
			);

	}

	save() {
		this.$domLocationButtonElement.parents('form').find('[name=\'locationX\']').val(this.getCoordinate().x);
		this.$domLocationButtonElement.parents('form').find('[name=\'locationY\']').val(this.getCoordinate().y);
		this.$domLocationButtonElement.parents('form').find('[name=\'size\']').val(this.getSize().width);
		this.$domLocationButtonElement.parents('form').find('[name=\'sizeX\']').val(this.getSize().width);
		this.$domLocationButtonElement.parents('form').find('[name=\'sizeY\']').val(this.getSize().height);
	}



	//Outer functions

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

		this.leaflet.fitBounds(bounds, {
				'padding':[0,0],
			});
		return true;
	}

	setData( crs, x, y, width, height ) {
		if ( crs != null ) {
			this.selectedCrs = crs;
		}
		if (x != null && y != null) {
			this.selectedCoordinate={x:x, y:y};
		}
		if (width != null) {
			this.selectedWidth = width;
			if ( height != null ) {
				this.selectedHeight = height;
			} else {
				this.selectedHeight = width;
			}
		}
		this.refreshInterface();
		this.refreshVisualization();
	}

	setSelectedCoordinate( crs, coordinate ) {
		this.setData(crs, coordinate.x, coordinate.y)
	}

	setDataFromInterface() {
		let locationX = '[name=\'locationX\']';
		let locationY = '[name=\'locationY\']';
		let sizeX = '[name=\'sizeX\']';
		let sizeY = '[name=\'sizeY\']';
		if ( this.$domLocationMapElement.find(sizeX).length == 0 ) {
			sizeX = '[name=\'size\']';
		}

		this.setData(
				null,
				this.$domLocationMapElement.find(locationX).val(),
				this.$domLocationMapElement.find(locationY).val(),
				this.$domLocationMapElement.find(sizeX).val(),
				this.$domLocationMapElement.find(sizeY).val(),
			)
	}

	//Inner functions

	refreshInterface() {
		this.$domLocationMapElement.find('[name=\'locationX\']').val(this.getCoordinate().x);
		this.$domLocationMapElement.find('[name=\'locationY\']').val(this.getCoordinate().y);
		this.$domLocationMapElement.find('[name=\'size\']').val(this.getSize()['width']);
		this.$domLocationMapElement.find('[name=\'sizeX\']').val(this.getSize()['width']);
		this.$domLocationMapElement.find('[name=\'sizeY\']').val(this.getSize()['height']);
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
	drawVisualizationSizeBounds() {
		let coordinate = this.selectedCoordinate;
		if ( coordinate == null) {
			return;
		}

		let crs = this.getCrs();
		let size = this.getSize();
		let geoJSON = this.computeGeoJSONBoundingBoxFromCoordinate(crs, coordinate, size.width, size.height);
		let visualBounds = L.geoJSON( geoJSON );
		this.drawElement('sizeBounds', visualBounds);
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


	convertCoordinateToLeaflet( crs, coordinate ) {
		if (crs == null) {
			crs = this.defaultCrs;
		}
		let crsObj = L.CRS['EPSG'+crs];
		coordinate = L.point(coordinate);
		let leafletCoordinate = crsObj.unproject(coordinate);

		return leafletCoordinate;
	}

	convertCoordinateFromLeaflet( crs, leafletCoordinate ) {
		if (crs == null) {
			crs = this.defaultCrs;
		}
		let crsObj = L.CRS['EPSG'+crs];
		leafletCoordinate = L.latLng(leafletCoordinate.lat, leafletCoordinate.lng);
		let coordinate = crsObj.project(leafletCoordinate);
		return coordinate;
	}

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
		return boundsCoordinates;
	}

	computeGeoJSONBoundingBoxFromCoordinate( crs, coordinate, width, height ) {
		let boundsCoordinates = this.computeBoundsFromCoordinate( crs, coordinate, width, height );
		if (boundsCoordinates == null) {
			return null;
		}

		let geoJSONCoordinates = [
				[boundsCoordinates[0][1], boundsCoordinates[0][0]],
				[boundsCoordinates[1][1], boundsCoordinates[1][0]],
				[boundsCoordinates[2][1], boundsCoordinates[2][0]],
				[boundsCoordinates[3][1], boundsCoordinates[3][0]],
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

	// Value accessors

	getCrs() {
		return this.selectedCrs ?? this.defaultCrs;
	}
	getCoordinate() {
		return this.selectedCoordinate ?? this.defaultCoordinate;
	}
	getZoomlevel() {
		return this.selectedZoomLevel ?? this.defaultZoomLevel;
	}
	getSize() {
		return {
				width: this.selectedWidth ?? this.defaultSize,
				height: this.selectedHeight ?? this.defaultSize,
			}
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
			self.setSelectedCoordinate( null, coordinate, null );
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

		let $buttonsOutput = $('<div></div>').addClass('containerOutput');

		$buttonsLocation.append(
				this.generateInputElement( 'location X', {
						'name'		:	'locationX',
						'type'		:	'number',
						'readonly'	:	true,
						'value'		:	this.getCoordinate().x,
					}, 'change', function() {
							self.setDataFromInterface();
						}
					),
				this.generateInputElement( 'location Y', {
						'name'		:	'locationY',
						'type'		:	'number',
						'readonly'	:	true,
						'value'		:	this.getCoordinate().y,
					}, 'change', function() {
							self.setDataFromInterface();
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
								self.setDataFromInterface();
							}
						),
					this.generateInputElement( 'height', {
							'name'		:	'sizeY',
							'type'		:	'number',
							'min'		:	500,
							'max'		:	5000,
							'value'		:	this.getSize().height,
						}, 'change', function() {
								self.setDataFromInterface();
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
								self.setDataFromInterface();
							}
						),
				);

		}

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

		$buttonsLeft.append($buttonsLocation, $buttonsSize);
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
