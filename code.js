// WMS LAYERS //
var pipelinePointsWMS = new ol.source.TileWMS({
	url: "http://vps362714.ovh.net:8080/geoserver/VitaliFiodarau/wms",
	serverType: 'geoserver',
	params: {'LAYERS': 'VitaliFiodarau:pipelinePoints', 'TILED':true}
});
var WMSlayer1 = new ol.layer.Tile({source: pipelinePointsWMS});

var pipelineSegmentsWMS = new ol.source.TileWMS({
	url: "http://vps362714.ovh.net:8080/geoserver/VitaliFiodarau/wms",
	serverType: 'geoserver',
	params: {'layers': 'VitaliFiodarau:pipelineSegments', 'TILED':true}
});
var WMSlayer2 = new ol.layer.Tile({source: pipelineSegmentsWMS});

// MAP TOOLS HANDLER //
var mousePositionControl = new ol.control.MousePosition({
	coordinateFormat: ol.coordinate.createStringXY(4),
	undefinedHTML: '&nbsp;'
});
var scaleLineControl = new ol.control.ScaleLine();
var zoomslider = new ol.control.ZoomSlider();

// MAP HANDLER //
var view = new ol.View({
	center: ol.proj.fromLonLat([27.65, 52.00]),
	zoom: 9
});

var select = new ol.interaction.Select();
//var selectedPointsCollections = new ol.Collection();
var borderStart = 10;
var borderEnd = 2000;

var map = new ol.Map({
	interactions: ol.interaction.defaults().extend([select]),
	controls: ol.control.defaults().extend([
		mousePositionControl,
		scaleLineControl,
		zoomslider
	]),
	target: 'map',
	layers: [
		new ol.layer.Tile({
			preload: 4,
			source: new ol.source.OSM()
		}),
	WMSlayer1, WMSlayer2//, vectorLayerA, vectorLayerB
	],
	loadTilesWhileAnimating: true,
	view: view
});

// BUTTONS HANDLER//
function onClick(id, callback) {
	document.getElementById(id).addEventListener('click', callback);
;}
// CALCULATE //
function myFunction() {
	window.alert("These are your results");
};

onClick('mapCenter', function() { // CENTER MAP //
	view.animate({
		center: ol.proj.fromLonLat([27.65, 52.00]),
		duration: 2000,
		zoom: 9
	});
});

var pipelinePointsObj = new ol.layer.Vector({});
onClick('boundariesSelect', function() { // SHOW POINTS FOR BORDER SELECTION //
	if (vectorLayer1) {
		map.removeLayer(vectorLayer1);
		vectorLayer1.destroy();
	};
	var pointStyle = new ol.style.Style({
		image: new ol.style.Circle({
			scale: 2000,
			radius: 7,
			stroke: new ol.style.Stroke({
				color: 'orange',
				width: 2
			}),
			fill: new ol.style.Fill({color: '#ffe4b3'})
		})
	});
	var vectorLayer1 =  new ol.layer.Vector({
			source: new ol.source.Vector({
				url:'http://vps362714.ovh.net:8080/geoserver/wfs?service=WFS&' +
					'version=1.1.0&request=GetFeature&typename=VitaliFiodarau:pipelinePointsObj&' +
					'outputFormat=application/json&srsname=EPSG:4326&',      
				format: new ol.format.GeoJSON(),
				strategy: ol.loadingstrategy.bbox
			}),
			style: pointStyle,
			opacity: 0.6
		});
	pipelinePointsObj = vectorLayer1;
	map.addLayer(pipelinePointsObj);
});

onClick('boundariesSave', function() { // SAVE BORDER SELECTION, RENDER AND GRAPH // 
	if (pipelinePointsObj) { // remove selection layer upon save
		//pipelinePointsObj.destroyFeatures();
		map.removeLayer(pipelinePointsObj);
	};
	// GET SELECTION ID's //
	var features = select.getFeatures();
	var borderId = [];
	features.forEach(function(feature,index){
		var gid = feature.get('gid')
		borderId[borderId.length] = gid*10;
	});
	// SORT SELECTION ID's //
	borderId.sort();
	if (borderId[0] < borderId[borderId.length-1]) {
		borderStart = borderId[0];
		borderEnd = borderId[borderId.length-1];
	} else {
		borderStart = borderId[borderId.length-1];
		borderEnd = borderId[0];
	}
	// ASSING SELECTION ID's TO DIV ELEMENTS //
	document.getElementById("100").value = borderStart;
	document.getElementById("101").value = borderEnd;
	select.getFeatures().clear() // clear selection pointers
	// DRAW SELECTED BORDERS ON MAP //
	var gidStart = borderStart/10;
	var gidEnd = borderEnd/10;
	var borderStartString = gidStart.toString();
	var borderEndString = gidEnd.toString();
	//console.log(borderStartString, borderEndString);
	if (pipelineOverlay) {
		map.removeLayer(pipelineOverlay);
		pipelineOverlay.destroy();
	};
	var cql_filter = 'gid<'+borderEndString+' AND gid>='+borderStartString;
	var pipelineOverlay = new ol.layer.Tile({
		source: new ol.source.TileWMS({
			url: "http://vps362714.ovh.net:8080/geoserver/VitaliFiodarau/wms",
			serverType: 'geoserver',
			params: {'layers': 'VitaliFiodarau:pipeSegments_overlay', 'cql_filter': cql_filter, 'TILED':true}
		})
	});
	map.addLayer(pipelineOverlay);
	if (bordersOverlay) {
		map.removeLayer(bordersOverlay);
		bordersOverlay.destroy();
	};
	var cql_filter1 = 'gid='+borderEndString+' OR gid='+borderStartString;;
	var bordersOverlay = new ol.layer.Tile({
		source: new ol.source.TileWMS({
			url: "http://vps362714.ovh.net:8080/geoserver/VitaliFiodarau/wms",
			serverType: 'geoserver',
			params: {'layers': 'VitaliFiodarau:pipelinePointsObj', 'cql_filter': cql_filter1, 'TILED':true}
		})
	});
	map.addLayer(bordersOverlay);
	map.renderSync(); // force map to render
	drawChart(); // draw the chart
});

onClick('boundariesSet', function() { // SET BORDER BY TYPE INPUT, RENDER AND GRAPH // 
	borderStart = parseInt(document.getElementById('100').value);
	borderEnd = parseInt(document.getElementById('101').value);
	// DRAW SELECTED BORDERS ON MAP //
	var gidStart = borderStart/10;
	var gidEnd = borderEnd/10;
	var borderStartString = gidStart.toString();
	var borderEndString = gidEnd.toString();
	//console.log(borderStartString, borderEndString);
	if (pipelineOverlay) {
		map.removeLayer(pipelineOverlay);
		pipelineOverlay.destroy();
	};
	var cql_filter = 'gid<'+borderEndString+' AND gid>='+borderStartString;
	var pipelineOverlay = new ol.layer.Tile({
		source: new ol.source.TileWMS({
			url: "http://vps362714.ovh.net:8080/geoserver/VitaliFiodarau/wms",
			serverType: 'geoserver',
			params: {'layers': 'VitaliFiodarau:pipeSegments_overlay', 'cql_filter': cql_filter, 'TILED':true}
		})
	});
	map.addLayer(pipelineOverlay);
	if (bordersOverlay) {
		map.removeLayer(bordersOverlay);
		bordersOverlay.destroy();
	};
	var cql_filter1 = 'gid='+borderEndString+' OR gid='+borderStartString;;
	var bordersOverlay = new ol.layer.Tile({
		source: new ol.source.TileWMS({
			url: "http://vps362714.ovh.net:8080/geoserver/VitaliFiodarau/wms",
			serverType: 'geoserver',
			params: {'layers': 'VitaliFiodarau:pipelinePointsObj', 'cql_filter': cql_filter1, 'TILED':true}
		})
	});
	map.addLayer(bordersOverlay);
	map.renderSync(); // force map to render
	drawChart(); // draw the chart
});

var leak = new ol.layer.Vector({});
var leak_point = new ol.interaction.Draw({});
onClick('leakDraw', function() {
	var vectorLayer2 =  new ol.layer.Vector({
		source: new ol.source.Vector({
			url:'http://vps362714.ovh.net:8080/geoserver/wfs?service=WFS&' +
				'version=1.1.0&request=GetFeature&typename=VitaliFiodarau:pipelineSegments&' +
				'outputFormat=application/json&srsname=EPSG:4326&',      
			format: new ol.format.GeoJSON(),
			strategy: ol.loadingstrategy.bbox
		}),
		style: new ol.style.Style({
			fill: new ol.style.Fill({
				color: '#ffcc33'
			}),
			stroke: new ol.style.Stroke({
				color: '#ffcc33',
				width: 10
			})
		}),
		opacity: 0.1
	});
	map.addLayer(vectorLayer2);
	var source = new ol.source.Vector({wrapX: false});
	var vector = new ol.layer.Vector({
		source: source
	});
	leak = vector;
	map.addLayer(vector);
	var draw = new ol.interaction.Draw({
		source: source,
		type: /** @type {ol.geom.GeometryType} */ ('Point'),
	});
	map.addInteraction(draw);
	draw.setActive(true);
	var snap = new ol.interaction.Snap({
		source: vectorLayer2.getSource(),
		vertex: false,
		pixelTolerance: '1000'
	});
	map.addInteraction(snap);
	snap.setActive(true);
	leak_point = draw;
})

onClick('leakSave', function() {
	var keys = leak_point.getKeys();
	var features = leak_point.getSource().getFeatures();
	var featureKeys = features.getKeys();

	//var features = leak_point.getFeatures();
	console.log(keys, features);
})

onClick('graphRefresh', function() { // REDRAW THE GRAPH //
	borderStart = parseInt(document.getElementById('100').value);
	borderEnd = parseInt(document.getElementById('101').value);
	drawChart(); // draw the chart
});

// Google charts //
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
google.charts.setOnLoadCallback(drawPieChart);

function drawPieChart() { // The function that handles the Pie Chart selection tool
	var data = google.visualization.arrayToDataTable([
		['TAG', 'SPACING'],
		['1', 1],
		['2', 1],
		['3', 1],
		['4', 1],
		['5', 1],
		['6', 1],
		['7', 1],
		['8', 1],
		['9', 1],
		['10', 1],
		['11', 1],
		['12', 1],
		]);
	var options = {
		legend: 'none',
		pieSliceText: 'label',
		pieStartAngle: 15,
		tooltip : {trigger: 'none'}
	};
	var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));
	chart.draw(data, options);
}

function drawChart() { // The function that handles the Chart
	// The data array from the given CSV //
	var data = [
		['SECTION', 'HEIGHT'],
		[10, 154.681],
		[20, 154.6689],
		[30, 154.1647],
		[40, 153.5897],
		[50, 153.5805],
		[60, 153.5103],
		[70, 153.3866],
		[80, 153.2301],
		[90, 153.1303],
		[100, 152.9757],
		[110, 152.9289],
		[120, 152.9773],
		[130, 152.9978],
		[140, 153.0325],
		[150, 153.0989],
		[160, 153.2423],
		[170, 153.3734],
		[180, 153.4371],
		[190, 153.4221],
		[200, 153.3495],
		[210, 153.3105],
		[220, 153.365],
		[230, 153.4314],
		[240, 153.3078],
		[250, 153.1448],
		[260, 153.1728],
		[270, 153.3376],
		[280, 153.4941],
		[290, 153.6616],
		[300, 153.8893],
		[310, 154.0723],
		[320, 154.0649],
		[330, 153.9093],
		[340, 153.6715],
		[350, 153.5233],
		[360, 153.4789],
		[370, 153.5014],
		[380, 153.3558],
		[390, 153.2392],
		[400, 153.1109],
		[410, 152.9279],
		[420, 152.9094],
		[430, 153.0736],
		[440, 153.0772],
		[450, 152.974],
		[460, 152.9076],
		[470, 152.9174],
		[480, 152.9112],
		[490, 152.8754],
		[500, 152.8288],
		[510, 152.7656],
		[520, 152.7309],
		[530, 152.6855],
		[540, 152.677],
		[550, 152.6094],
		[560, 152.5254],
		[570, 152.3962],
		[580, 152.2737],
		[590, 152.142],
		[600, 152.0471],
		[610, 152.0384],
		[620, 151.8849],
		[630, 151.7508],
		[640, 151.6736],
		[650, 151.6477],
		[660, 151.6743],
		[670, 151.6124],
		[680, 151.549],
		[690, 151.4508],
		[700, 151.2834],
		[710, 151.0853],
		[720, 150.8838],
		[730, 150.6818],
		[740, 150.5914],
		[750, 150.7026],
		[760, 150.7981],
		[770, 150.9581],
		[780, 151.0801],
		[790, 151.0896],
		[800, 151.1219],
		[810, 151.1602],
		[820, 151.2754],
		[830, 151.3436],
		[840, 151.2337],
		[850, 151.1919],
		[860, 151.3042],
		[870, 151.4498],
		[880, 151.5523],
		[890, 151.6731],
		[900, 151.7925],
		[910, 151.9176],
		[920, 152.139],
		[930, 152.2769],
		[940, 152.3627],
		[950, 152.5691],
		[960, 152.6272],
		[970, 152.7304],
		[980, 152.8354],
		[990, 152.7889],
		[1000, 152.9462],
		[1010, 153.1936],
		[1020, 153.4233],
		[1030, 153.448],
		[1040, 153.4367],
		[1050, 153.444],
		[1060, 153.6156],
		[1070, 153.8485],
		[1080, 153.981],
		[1090, 154.1329],
		[1100, 154.2372],
		[1110, 154.3519],
		[1120, 154.5351],
		[1130, 154.6878],
		[1140, 154.8704],
		[1150, 155.1446],
		[1160, 155.3317],
		[1170, 155.5139],
		[1180, 155.8169],
		[1190, 156.1657],
		[1200, 156.4577],
		[1210, 156.7557],
		[1220, 156.9317],
		[1230, 156.9817],
		[1240, 157.0655],
		[1250, 157.112],
		[1260, 157.1348],
		[1270, 157.0784],
		[1280, 157.0031],
		[1290, 156.7787],
		[1300, 156.4755],
		[1310, 156.2466],
		[1320, 156.0993],
		[1330, 155.938],
		[1340, 155.8554],
		[1350, 155.804],
		[1360, 155.684],
		[1370, 155.5645],
		[1380, 155.3494],
		[1390, 155.0895],
		[1400, 154.9229],
		[1410, 154.829],
		[1420, 154.8032],
		[1430, 154.7747],
		[1440, 154.6914],
		[1450, 154.5139],
		[1460, 154.3715],
		[1470, 154.3101],
		[1480, 154.2408],
		[1490, 154.1938],
		[1500, 154.2821],
		[1510, 154.4325],
		[1520, 154.5049],
		[1530, 154.4586],
		[1540, 154.341],
		[1550, 154.4364],
		[1560, 154.7004],
		[1570, 155.0457],
		[1580, 155.2953],
		[1590, 155.6197],
		[1600, 156.0713],
		[1610, 156.6281],
		[1620, 156.8756],
		[1630, 156.9793],
		[1640, 156.8333],
		[1650, 156.3798],
		[1660, 155.815],
		[1670, 155.4089],
		[1680, 155.2416],
		[1690, 155.3061],
		[1700, 155.29],
		[1710, 155.0451],
		[1720, 154.9693],
		[1730, 155.0355],
		[1740, 155.1394],
		[1750, 155.2115],
		[1760, 155.3069],
		[1770, 155.2979],
		[1780, 155.1744],
		[1790, 154.9517],
		[1800, 154.7441],
		[1810, 154.6634],
		[1820, 154.6277],
		[1830, 154.6444],
		[1840, 154.652],
		[1850, 154.6908],
		[1860, 154.7439],
		[1870, 154.6989],
		[1880, 154.6183],
		[1890, 154.5916],
		[1900, 154.6439],
		[1910, 154.6106],
		[1920, 154.5096],
		[1930, 154.4402],
		[1940, 154.3834],
		[1950, 154.3299],
		[1960, 154.2676],
		[1970, 154.2093],
		[1980, 154.0636],
		[1990, 153.9278],
		[2000, 153.8551]
	];
	// Create the table to be drawn //
	var chartData = new google.visualization.DataTable();
	chartData.addColumn('number', 'Section');
	chartData.addColumn('number', 'Height');
	chartData.addColumn({type: 'string', role: 'annotation'});
	chartData.addColumn({type: 'string', role: 'annotationText'});
	// Create the data to the table from the selection of the data array //
	j=borderStart/10;
	for (i=borderStart; i<=borderEnd; i=i+10) {
		//window.console.log(j, data[j][0]);
		if (i==borderStart) {chartData.addRow([data[j][0], data[j][1], 'Right Border', 'Right Border'])};
		if (i==borderEnd) {chartData.addRow([data[j][0], data[j][1], 'Left Border', 'Left Border'])};
		chartData.addRow([data[j][0], data[j][1], null, null]);
		if (data[j][0] < 110 & data[j][0] > 90) {chartData.addRow([105, 152.976, 'Leak', 'Leak annotation'])};
		j=j+1;
	};
	// The options for drawing (Here can also be added x-axis control //
	var options = {
		title: 'Pipeline Section Height Chart',
		curveType: 'line',
		pointSize: 5,
		legend: {position: 'top'},
		annotations: {
			stem: {
				color: '#cc0000'
			},
			textStyle: {	
				bold: true,
				italic: true,
				color: '#cc0000',
				auraColor: 'ffffff',
				opacity: 0.8
			},
			style: 'line'
		}
	};
	// Assinging the graph to HTML element and drawing //
	var chart = new google.visualization.LineChart(document.getElementById('line_chart'));
	chart.draw(chartData, options);
}