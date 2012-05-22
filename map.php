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
	while(address.indexOf("  ") > -1){
		address = address.replace("  "," ");
	}
	var lcadd = address.toLowerCase();
	// filter addresses which end with ", Santa Cruz, CA" but not "Santa Cruz St"
	if(lcadd.indexOf("santa cruz") != -1){
		if(lcadd.lastIndexOf("santa cruz") > lcadd.lastIndexOf("st")){
			address = address.substring(0, lcadd.lastIndexOf("santa cruz"));
		}
	}
	while(address.indexOf(",") > -1){
		address = address.replace(",","");
	}
	lcadd = address.toLowerCase();

	// abbreviate addresses to match GIS data
	if(lcadd.indexOf(" street") > -1){
		address = address.substring(0, lcadd.indexOf(" street") + 3);
	}
	if(lcadd.indexOf(" avenue") > -1){
		address = address.substring(0, lcadd.indexOf(" avenue") + 4);
	}
	if(lcadd.indexOf(" drive") > -1){
		address = address.substring(0, lcadd.indexOf(" drive") + 3);
	}
	if(lcadd.indexOf(" circle") > -1){
		address = address.substring(0, lcadd.indexOf(" circle") + 4);
	}
	if(lcadd.indexOf(" lane") > -1){
		address = address.substring(0, lcadd.indexOf(" lane")) + " Ln";
	}
	if(lcadd.indexOf(" boulevard") > -1){
		address = address.substring(0, lcadd.indexOf(" boulevard")) + " Blvd";
	}
	if(lcadd.indexOf(" court") > -1){
		address = address.substring(0, lcadd.indexOf(" court")) + " Ct";
	}
	if(lcadd.indexOf(" place") > -1){
		address = address.substring(0, lcadd.indexOf(" place")) + " Pl";
	}
 	address = address.replace(/^\s+|\s+$/g,"");

	query.where = "ADD_ LIKE upper ('%" + address + "%')";
	queryTask.execute(query, function(results){
        if(results.features.length == 0){
        	// if address lookup fails, turn text box red
			document.getElementById("address").style.backgroundColor = "#f44";
		}
		else{
			document.getElementById("address").style.backgroundColor = "#fff";
			var zone = results.features[0].attributes['Zoning1'];
			var street = results.features[0].attributes['ADD_'];
			var usecode = results.features[0].attributes['USECDDESC'];

			var latlng = new L.LatLng(results.features[0].geometry.y, results.features[0].geometry.x);
			//console.log(latlng);
			
			var marker = new L.Marker(latlng);
			map.addLayer(marker);
			marker.bindPopup(street + "<br/>Zone: " + zone + "<br/>Current Use (Prior Use): " + usecode).openPopup();
			
			// test: store zone and use code as a cookie
			//setCookie("zone_and_use", zone + "|" + usecode, 21);
			Cookies.defaults = {
				path: '/',
				expires: 60 * 60 * 24 * 21,
				secure: false
			}
			Cookies.set('address', document.getElementById("address").value );
			Cookies.set('zone_and_use', zone + "|" + usecode);
		}
	});
}

var map;
var _tilejson;
wax.tilejson('http://a.tiles.mapbox.com/v3/tamaracfa.map-lhp1bb4f.jsonp',
	function(tilejson) {
		_tilejson = tilejson;
		map = new L.Map('map-div', { scrollWheelZoom: false, maxZoom: 17 })
    		.addLayer(new wax.leaf.connector(tilejson))
    		.setView(new L.LatLng(36.9749, -122.0263), 13);

		wax.leaf.interaction()
			.map(map)
			.tilejson(tilejson)
			.on(wax.tooltip().animate(false).parent(map._container).events())
	}
);


function codeAddress() {
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

     <div class="alignleft">
        <?php previous_link(); ?>
    </div>
    <div class="alignright">
        <?php next_link(); ?>
    </div>

            </div>
                   	         
		</div>
		<?php endwhile; endif; ?>
	<?php edit_post_link(__('Edit this entry.', 'kubrick'), '<p>', '</p>'); ?>
	
	</div>
<div class="progbar">
            <?php echo  get_post_meta($post->ID, 'percent', 1); ?> 

            </div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
