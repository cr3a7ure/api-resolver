 OpenLayers Examples  
Docs
Examples
API
Code
GeoJSON

+
−
© OpenStreetMap contributors.
Example of GeoJSON features.

Related API documentation: ol.Feature,ol.Map,ol.View,ol.control,ol.format.GeoJSON,ol.geom.Circle,ol.layer.Tile,ol.layer.Vector,ol.source.OSM,ol.source.Vector,ol.style.Circle,ol.style.Fill,ol.style.Stroke,ol.style.Style
 Copy  Edit
<!DOCTYPE html>
<html>
  <head>
    <title>GeoJSON</title>
    <link rel="stylesheet" href="https://openlayers.org/en/v4.1.1/css/ol.css" type="text/css">
    <!-- The line below is only needed for old environments like Internet Explorer and Android 4.x -->
    <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL"></script>
    <script src="https://openlayers.org/en/v4.1.1/build/ol.js"></script>
  </head>
  <body>
    <div id="map" class="map"></div>
    <script>
      var image = new ol.style.Circle({
        radius: 5,
        fill: null,
        stroke: new ol.style.Stroke({color: 'red', width: 1})
      });

      var styles = {
        'Point': new ol.style.Style({
          image: image
        }),
        'LineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'green',
            width: 1
          })
        }),
        'MultiLineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'green',
            width: 1
          })
        }),
        'MultiPoint': new ol.style.Style({
          image: image
        }),
        'MultiPolygon': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'yellow',
            width: 1
          }),
          fill: new ol.style.Fill({
            color: 'rgba(255, 255, 0, 0.1)'
          })
        }),
        'Polygon': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'blue',
            lineDash: [4],
            width: 3
          }),
          fill: new ol.style.Fill({
            color: 'rgba(0, 0, 255, 0.1)'
          })
        }),
        'GeometryCollection': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'magenta',
            width: 2
          }),
          fill: new ol.style.Fill({
            color: 'magenta'
          }),
          image: new ol.style.Circle({
            radius: 10,
            fill: null,
            stroke: new ol.style.Stroke({
              color: 'magenta'
            })
          })
        }),
        'Circle': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'red',
            width: 2
          }),
          fill: new ol.style.Fill({
            color: 'rgba(255,0,0,0.2)'
          })
        })
      };

      var styleFunction = function(feature) {
        return styles[feature.getGeometry().getType()];
      };

      var geojsonObject = {
        'type': 'FeatureCollection',
        'crs': {
          'type': 'name',
          'properties': {
            'name': 'EPSG:3857'
          }
        },
        'features': [{
          'type': 'Feature',
          'geometry': {
            'type': 'Point',
            'coordinates': [0, 0]
          }
        }, {
          'type': 'Feature',
          'geometry': {
            'type': 'LineString',
            'coordinates': [[4e6, -2e6], [8e6, 2e6]]
          }
        }, {
          'type': 'Feature',
          'geometry': {
            'type': 'LineString',
            'coordinates': [[4e6, 2e6], [8e6, -2e6]]
          }
        }, {
          'type': 'Feature',
          'geometry': {
            'type': 'Polygon',
            'coordinates': [[[-5e6, -1e6], [-4e6, 1e6], [-3e6, -1e6]]]
          }
        }, {
          'type': 'Feature',
          'geometry': {
            'type': 'MultiLineString',
            'coordinates': [
              [[-1e6, -7.5e5], [-1e6, 7.5e5]],
              [[1e6, -7.5e5], [1e6, 7.5e5]],
              [[-7.5e5, -1e6], [7.5e5, -1e6]],
              [[-7.5e5, 1e6], [7.5e5, 1e6]]
            ]
          }
        }, {
          'type': 'Feature',
          'geometry': {
            'type': 'MultiPolygon',
            'coordinates': [
              [[[-5e6, 6e6], [-5e6, 8e6], [-3e6, 8e6], [-3e6, 6e6]]],
              [[[-2e6, 6e6], [-2e6, 8e6], [0, 8e6], [0, 6e6]]],
              [[[1e6, 6e6], [1e6, 8e6], [3e6, 8e6], [3e6, 6e6]]]
            ]
          }
        }, {
          'type': 'Feature',
          'geometry': {
            'type': 'GeometryCollection',
            'geometries': [{
              'type': 'LineString',
              'coordinates': [[-5e6, -5e6], [0, -5e6]]
            }, {
              'type': 'Point',
              'coordinates': [4e6, -5e6]
            }, {
              'type': 'Polygon',
              'coordinates': [[[1e6, -6e6], [2e6, -4e6], [3e6, -6e6]]]
            }]
          }
        }]
      };

      var vectorSource = new ol.source.Vector({
        features: (new ol.format.GeoJSON()).readFeatures(geojsonObject)
      });

      vectorSource.addFeature(new ol.Feature(new ol.geom.Circle([5e6, 7e6], 1e6)));

      var vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        style: styleFunction
      });

      var map = new ol.Map({
        layers: [
          new ol.layer.Tile({
            source: new ol.source.OSM()
          }),
          vectorLayer
        ],
        target: 'map',
        controls: ol.control.defaults({
          attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
            collapsible: false
          })
        }),
        view: new ol.View({
          center: [0, 0],
          zoom: 2
        })
      });
    </script>
  </body>
</html>