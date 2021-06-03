<?php
/**
 * The template for displaying maps. 
 *
 * @package   Industria_members_map
 * @author    jetonr <jetonr@gmail.com>
 */

$map_id = uniqid( 'imm_map_' ); // generate a unique ID for this map
?>

<div class="imm_map_canvas" id="<?php echo esc_attr( $map_id ); ?>" style="height: <?php echo esc_attr( $atts['height'] ); ?>; width: <?php echo esc_attr( $atts['width'] ); ?>"></div>
<script type="text/javascript">
    var map_<?php echo $map_id; ?>;
    if (!Object.entries) {
        Object.entries = function( obj ){
            var ownProps = Object.keys( obj ),
            i = ownProps.length,
            resArray = new Array(i); // preallocate the Array
            while (i--)
            resArray[i] = [ownProps[i], obj[ownProps[i]]];

            return resArray;
        };
    }
    function imm_run_map_<?php echo $map_id ; ?>(){
        
        var location = new google.maps.LatLng( 46.204391, 6.143158 );
        var map_options = {
            zoom: <?php echo $atts['zoom'] ?>,
            center: location,
            scrollwheel: <?php echo $atts['scrollwheel'] ?>,
            mapTypeId: "<?php echo $atts['type'] ?>",
            streetViewControl: <?php echo $atts['street'] ?>,
        }
        map_<?php echo $map_id ; ?> = new google.maps.Map(document.getElementById("<?php echo $map_id ; ?>"), map_options);
        var markers = [];
        var locations = <?php echo $locations; ?>;
        var infoWindow = new google.maps.InfoWindow();
        Object.entries(locations).forEach(([key, value]) => {
            if (typeof(value.location) !== 'undefined') {
                var latLng = new google.maps.LatLng(
                    value.location.lat,
                    value.location.lng
                );
                var marker = new google.maps.Marker({
                    position: latLng,
                    title:  value.name + ' ' + value.surname,
                    optimized: true,
                });
                marker.addListener("click", () => {
                    infoWindow.close();
                    infoWindow.setContent(
                        '<h4 style="margin: 0 0 5px 0">'+ marker.getTitle() +' v/o '+ value.nickname +'</h4>'+
                        '<span>Address:</span><br>'+
                        '<span>'+ value.location.address + '</span>'
                    );
                    infoWindow.open(marker.getMap(), marker);
                });
                markers.push(marker);
            }
        });
        var markerCluster = new MarkerClusterer(map_<?php echo $map_id ; ?>, markers, {
            imagePath: "<?php echo INDUSTRIA_PLUGIN_URL ?>public/images/m",
        });
    }
    google.maps.event.addDomListener(window, "load", imm_run_map_<?php echo $map_id ; ?>);
</script>
