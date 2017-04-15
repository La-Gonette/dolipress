<?php

require_once(dirname(__FILE__)."/master.inc.php");

/** /
ini_set( "display_errors", "1" );
error_reporting( E_ALL );
/**/

$conf = "TEST";
$allowedDomains = array( 'TEST' => "", 'PROD' => "" );
//$allowedDomain = $allowedDomains[$conf];

$gonetteBackEndDomains = array( 'TEST' => "http://82.225.211.150:18001", 'PROD' => "http://dolibarr.lagonette.org" );
$gonetteBackEndDomain = $gonetteBackEndDomains[$conf];

$gonetteFrontEndDomains = array( 'TEST' => "http://82.225.211.150:18002", 'PROD' => "http://www.lagonette.org" );
$gonetteFrontEndDomain = $gonetteFrontEndDomains[$conf];

$response = [ "errors" => "" ];

if( isset( $allowedDomain ) && isset( $_SERVER['REMOTE_ADDR'] ) && $_SERVER['REMOTE_ADDR'] !== $allowedDomain ) {
    $response['errors'] = "";
} else {

    $ccode = "";
    $logo = 0;
    $thumbnail = 0;
    $format = "wordpress";

    if( isset( $_GET['format'] ) && preg_match( '/^wordpress|geojson|app_categories|app_partners$/', $_GET['format'] ) ) {
        $format = $_GET['format'];
    }
    
    if( $format === "app_categories" ) {
    	$response['categories'] = [];
    } else {
    	$response['partners'] = [];
    }

    if( isset( $_GET['ccode'] ) && preg_match( '/^P[0-9]{4}$/', $_GET['ccode'] ) ) {
        $ccode = " AND societe.code_client = '".$_GET['ccode']."'";
        $startIndex = 0;
        $maxNumberOfResults = 1;
    }

    if( isset( $_GET['logo'] ) ) {
        $logo = intval( $_GET['logo'], 10 );
        if( $logo && isset( $_GET['thumbnail'] ) ) {
            $thumbnail = intval( $_GET['thumbnail'], 10 );
        }
    }
    
    $partnerCategories = [];
    if( $result = $db->query( "SELECT rowid AS partnerCategoryID, partner_category AS partnerCategoryLabel, icon AS partnerCategoryIcon, display_order AS displayOrder, hidden AS hidden FROM llx_partner_categories ORDER BY display_order ASC" ) ) {
        while( $row = $result->fetch_assoc() ) {
        	if( $format === "app_categories" ) {
        		$category = array();
        		$category['id'] = $row['partnerCategoryID'];
        		$category['label'] = $row['partnerCategoryLabel'];
        		$category['icon'] = $gonetteFrontEndDomain.'/wp-content/uploads/'.$row['partnerCategoryIcon'];
        		array_push( $response['categories'], $category );
        	} else {
            	$catId = $row['partnerCategoryID'];
            	$partnerCategories[$catId] = array( 'label' => $row['partnerCategoryLabel'], 'icon' => $row['partnerCategoryIcon'], 'displayOrder' => $row['displayOrder'], 'hidden' => $row['hidden'] );
            }
        }
        $result->free();
        
        if( $format !== "app_categories" ) {
            
	        /*$partnersMarkets = [];
	        if( $format == "geojson" ) {
	            if( $result = $db->query( "SELECT rowid AS marketID, name AS marketName, icon AS marketIcon, opening_hours AS marketOpeningHours, address AS marketAddress, gpscoords AS marketGPSCoords FROM llx_partners_markets" ) ) {
	                while( $row = $result->fetch_assoc() ) {
	                    $marketId = $row['marketID'];
	                    $partnersMarkets[$marketId] = array( 'name' => $row['marketName'], 'icon' => $row['marketIcon'], 'openingHours' => $row['marketOpeningHours'], 'address' => $row['marketAddress'], 'gpsCoords' => $row['marketGPSCoords'] );
	                }
	                $result->free();
	            }
	        }*/

	        $partnersSelectionQuery = "SELECT societe.rowid AS id, societe.code_client AS clientCode, societe.nom AS name, societe.address AS address, societe.town AS city, societe.logo AS logo, ";
	        $partnersSelectionQuery .= "societe.zip AS zipCode, societe.fk_departement AS region, country.code AS countryCode, country.label AS countryName, societeExtended.gpscoords AS gpsCoordinates, societe.phone AS phone, ";
	        $partnersSelectionQuery .= "societe.url AS website, societe.email AS email, societeExtended.description AS description, societeExtended.openinghours AS openingHours, societeExtended.exchangeoffice AS isExchangeOffice, ";
	        $partnersSelectionQuery .= "societeExtended.shortdescription AS shortDescription, societeExtended.maincategory AS mainCategory, societeExtended.sidecategories AS sideCategories ";
	        $partnersSelectionQuery .= "FROM llx_societe AS societe LEFT JOIN llx_societe_extrafields AS societeExtended ON societe.rowid = societeExtended.fk_object ";
	        $partnersSelectionQuery .= "LEFT JOIN llx_c_country AS country ON societe.fk_pays = country.rowid ";
	        $partnersSelectionQuery .= "WHERE societeExtended.publishedpartner = 1";
	        $partnersSelectionQuery .= $ccode;
	        $partnersSelectionQuery .= " ORDER BY societe.nom";

	        if( $result = $db->query( $partnersSelectionQuery ) ) {
	            while( $row = $result->fetch_assoc() ) {
	                $partner = array();
	                if( $format === "geojson" ) {
	                    $gpsCoords = explode( ",", $row['gpsCoordinates'] );
	                    if( count( $gpsCoords ) == 2 ) {
	                        $partner['type'] = "Feature";
	                        $partner['properties'] = array();
	                        $partner['geometry']['type'] = "Point";
	                        $partner['geometry']['coordinates'] = array();
	                        array_push( $partner['geometry']['coordinates'], floatval( $gpsCoords[1] ) );
	                        array_push( $partner['geometry']['coordinates'], floatval( $gpsCoords[0] ) );
	                        $partner['properties']['name'] = $row['name'];
	                        $partner['properties']['description'] = $row['shortDescription']."\n".'[['.$gonetteFrontEndDomain.'/'.$row['clientCode'].'|En savoir plus]]';
	                        $partner['properties']['_storage_options'] = array();
	                        $partnerFirstCategory = $row['mainCategory'];
	                        $partner['properties']['_storage_options']['iconUrl'] = $gonetteFrontEndDomain.'/wp-content/uploads/'.$partnerCategories[$partnerFirstCategory]['icon'];
	                        $partner['properties']['_storage_options']['color'] = '#'.( $row['isExchangeOffice'] ? '00babe' : 'e6411c' );
	                        $partner['properties']['_storage_options']['iconClass'] = "Drop";
	                        //$partner['properties']['_storage_options']['showLabel'] = false;
	                        //$partner['properties']['_storage_options']['zoomTo'] = 0;
	                    }
	                } else if( $format === "app_partners" ) {
	                	$partner['id'] = $row['id'];
	                	$partner['clientCode'] = $row['clientCode'];
	                	$partner['name'] = $row['name'];
	                	$partner['address'] = $row['address'];
	                	$partner['city'] = $row['city'];
	                	$partner['logo'] = $gonetteBackEndDomain."/dolibarr/documents/societe/".$row['id']."/logos/".$row['logo'];
	                	$partner['zipCode'] = $row['zipCode'];
	                	$gpsCoords = explode( ",", $row['gpsCoordinates'] );
	                	if( count( $gpsCoords ) == 2 ) {
	                		$partner['latitude'] = floatval( $gpsCoords[0] );
	                		$partner['longitude'] = floatval( $gpsCoords[1] );
	                	}
	                	$partner['phone'] = $row['phone'];
	                	$partner['website'] = $row['website'];
	                	$partner['email'] = $row['email'];
	                	$partner['description'] = $row['description'];
	                	$partner['openingHours'] = $row['openingHours'];
	                	$partner['isExchangeOffice'] = $row['isExchangeOffice'];
	                	$partner['shortDescription'] = $row['shortDescription'];
	                	$partner['mainCategory'] = $row['mainCategory'];
	                	$partner['sideCategories'] = explode( ",", $row['sideCategories'] );
	                } else {
	                    foreach( $row as $key => $value ) {
	                        $partner[$key] = $value;
	                    }
	                    $partner['partnerCategories'] = array();
	                    $partner['partnerCategories'][] = $partner['mainCategory'];
                        
	                    if( isset( $partner['sideCategories'] ) && strlen( $partner['sideCategories'] ) > 0 ) {
	                        $partner['partnerCategories'] = array_merge( $partner['partnerCategories'], explode( ",", $partner['sideCategories'] ) );
	                    }
	                    for( $i = 0; $i < count( $partner['partnerCategories'] ); ++$i ) {
	                        $cat = $partner['partnerCategories'][$i];
	                        if( isset( $partnerCategories[$cat] ) ) {
	                            $partner['partnerCategories'][$i] = $partnerCategories[$cat];
	                        } else {
	                        	unset( $partner['partnerCategories'][$i] );
	                        }
	                    }
	                    if( $logo && $row['logo'] ) {
	                        if( !$thumbnail ) {
	                            $logopath = $row['logo'];
	                        } else {
	                            $logoname = pathinfo( $row['logo'], PATHINFO_FILENAME );
	                            $logoextension = pathinfo( $row['logo'], PATHINFO_EXTENSION );
	                            $logopath = "thumbs/".$logoname."_mini.".$logoextension;
	                        }
	                        $logo = file_get_contents( "../documents/societe/".$row['id']."/logos/".$logopath );
	                        $partner['logoContent'] = $logo ? base64_encode( $logo ) : null;
	                    }
	                }
	                if( count( $partner ) > 0 ) {
	                    array_push( $response['partners'], $partner );
	                }
	            }
	            $result->free();
	        } else {
	            $response['errors'] = "Error executing query Published Partners: ".$db->error;
	        }
	    }
	} else {
	        $response['errors'] = "Error executing query Partner categories: ".$db->error;
	}
}

if( $format == "geojson" ) {
    $geojsonResponse = [ "type" => "FeatureCollection" ];
    $geojsonResponse['features'] = $response['partners'];
    $response = $geojsonResponse;
    header( "Access-Control-Allow-Origin: http://umap.openstreetmap.fr" );
    header( "Content-type:application/vnd.geo+json;charset=utf-8" );
} else {
	if( strpos( $format, "app_" ) === 0 ) {
		$response['md5_sum'] = md5( json_encode( $response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}
    header( "Content-type:application/json;charset=utf-8" );
}
echo json_encode( $response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
?>
