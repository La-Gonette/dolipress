<?php

function geocoding_address2gps( $address ) {
    $gpsCoords = null;
    $url = 'https://nominatim.openstreetmap.org/search?format=json&q='.urlencode( $address );
    $geocode = json_decode( file_get_contents( $url ) );
    if( !is_null( $geocode ) && count( $geocode ) > 0 ) {
		$gpsCoords = [];
        $gpsCoords['latitude'] = $geocode[0]->lat;
        $gpsCoords['longitude'] = $geocode[0]->lon;
    } else {
        $url = 'http://photon.komoot.de/api/?q='.urlencode( $address );
        $geocode = json_decode( file_get_contents( $url ) );
        if( !is_null( $geocode ) && count( $geocode->features ) > 0 ) {
            for( $i = 0; $i < count( $geocode->features ); ++$i ) {
                if( isset( $geocode->features[$i]->properties ) && isset( $geocode->features[$i]->properties->postcode ) && isset( $geocode->features[$i]->properties->housenumber ) && $object->zip == $geocode->features[$i]->properties->postcode && false !== strpos( $object->address, $geocode->features[$i]->properties->housenumber ) ) {
                	$gpsCoords = [];
                	$gpsCoords['latitude'] = $geocode->features[$i]->geometry->coordinates[1];
					$gpsCoords['longitude'] = $geocode->features[$i]->geometry->coordinates[0];
                    break;
                }
            }
        }
    }
    return $gpsCoords;
}

?>
