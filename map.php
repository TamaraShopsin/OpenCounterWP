<?php /* Template Name: map
*/ ?>
<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>
<div id="header3">
    <a href="http://opencounter.org">
    <canvas id="clockcanvas" width="50" height="50"></canvas>
</a>
    
        
    
</div>
<script>
dojo.require("esri.tasks.query");
var queryTask = new esri.tasks.QueryTask("http://gis.cityofsantacruz.com/ArcGIS/rest/services/AddressSeach/MapServer/0");
var query = new esri.tasks.Query();
query.returnGeometry = true;
query.outFields = ["*"];

function executeQuery(address){
	
	var result;
	
	address = address.replace(/^\s+|\s+$/g,"");
	query.where = "ADD_ LIKE upper ('%" + address + "')";
	queryTask.execute(query, function(results){
		zone = results.features[0].attributes['Zoning1'];
	
	    var street = results.features[0].attributes['ADD_'];
		var latlng = new L.LatLng(results.features[0].geometry.y, results.features[0].geometry.x);
                console.log(latlng);
		var marker = new L.Marker(latlng); 
        map.addLayer(marker);

        street += "<br/>Zone: " + zone;
		street += "<br/>Current Use: " + results.features[0].attributes['USECDDESC'];

		marker.bindPopup(street).openPopup();
	
		console.log(results);
		
	});
}


var codeAddress;
var map;
var _tilejson;
wax.tilejson('http://a.tiles.mapbox.com/v3/tamaracfa.map-lhp1bb4f.jsonp',
  function(tilejson) {
  _tilejson = tilejson;
  map = new L.Map('map-div')
    .addLayer(new wax.leaf.connector(tilejson))
    .setView(new L.LatLng(36.9749, -122.0263), 14);


	wax.leaf.interaction()
    .map(map)
    .tilejson(tilejson)
    .on(wax.tooltip().animate(false).parent(map._container).events())

});


	codeAddress = function() {
	var address = document.getElementById("address").value;
	executeQuery(address);
  }

function checkForEnter(e){
	if(e.keyCode == 13){
		codeAddress();
	}
}

</script>
    
	<div id="content" class="narrowcolumn" role="main">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		
			<div class="entry">
				<?php the_content('<p class="serif">' . __('Read the rest of this page &raquo;', 'kubrick') . '</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'kubrick') . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
<div class="navigation">

    <?php previous_link(); ?>
<?php next_link(); ?>

</div>
                   	         
		</div>
		<?php endwhile; endif; ?>
	<?php edit_post_link(__('Edit this entry.', 'kubrick'), '<p>', '</p>'); ?>
	
	</div>



<?php get_footer(); ?>
