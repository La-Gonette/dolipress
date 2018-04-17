<?php

require_once( dirname( __FILE__ ) . "/master.inc.php" );

/** /
ini_set( "display_errors", "1" );
error_reporting( E_ALL );
/**/

$conf = "TEST";
$allowedDomains = array( 'TEST' => "", 'PROD' => "" );
//$allowedDomain = $allowedDomains[$conf];

$gonetteBackEndDomains = array( 'TEST' => "http://82.225.211.150:18001", 'PROD' => "https://dolibarr.lagonette.org" );
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
    $hashonly = 0;

    if( isset( $_GET['format'] ) && preg_match( '/^wordpress|geojson|app_categories|app_partners|app_markets$/', $_GET['format'] ) ) {
        $format = $_GET['format'];
    }

    if( $format === "app_categories" ) {
    	$response['categories'] = [];
    } else if( $format === "app_markets" ) {
    	$response['markets'] = [];
    } else {
    	$response['partners'] = [];
    }

    if( strpos( $format, "app_" ) === 0 && isset( $_GET['hashonly'] ) ) {
        $hashonly = intval( $_GET['hashonly'], 10 );
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

    if( $format === "app_markets" ) {
    	$marketsJson = '{"errors":"","markets":[{"id":"1","name":"Marché bio  de la Croix Rousse","openingHours":"Les samedi de 6h à 13h30","partners":[116,92],"longitude":4.831280708312988,"latitude":45.77426575456372},{"id":"2","name":"Marché bio de Collonge aux monts d\'or","openingHours":"Les vendredi de 16h30 à 19h30","partners":[116],"longitude":4.850678443908691,"latitude":45.81694120688579},{"id":"3","name":"Marché Bio de Grezieu la Varenne","openingHours":"Les vendredi de 14h à 19h","partners":[116,230],"longitude":4.692943096160889,"latitude":45.74839773565179},{"id":"4","name":"Marché bio de Vaise","openingHours":"Les mardi de 6 à 13h","partners":[73],"longitude":4.805504679679871,"latitude":45.77513754334658},{"id":"5","name":"Marché bio des clarines","openingHours":"Les samedi de 9h à 13h","partners":[101],"longitude":4.604537487030029,"latitude":45.59384614820627},{"id":"6","name":"Marché bio du Chapi","openingHours":"Les vendredi de 16h30 à 19h","partners":[101],"longitude":4.699380397796631,"latitude":45.67276838832159},{"id":"7","name":"Marché bio Monplaisir","openingHours":"Les mercredi de 15h à 20h","partners":[116],"longitude":4.871245622634887,"latitude":45.74472164768029},{"id":"8","name":"Marché de Caluire","openingHours":"Les samedi de 7h30 à 12h30","partners":[73],"longitude":4.846612215042114,"latitude":45.79051694856825},{"id":"9","name":"Marché de lentilly","openingHours":"Les mercredi et dimanche, de 8h à 12h (13h le dimanche)","partners":[73],"longitude":4.662698507308959,"latitude":45.81940124856704},{"id":"10","name":"Marché de Sainte Foy l\'argentière","openingHours":"Les samedi de 8h00 à 13h","partners":[101],"longitude":4.663696289062501,"latitude":45.767522962149904},{"id":"11","name":"Marché de Tarare","openingHours":"Les samedi matin","partners":[109],"longitude":4.4336700439453125,"latitude":45.8966670611441},{"id":"12","name":"Marché de Tassin la demi lune","openingHours":"Les vendredi de 7h30 à 12h30","partners":[73],"longitude":4.778569936752319,"latitude":45.76262064242203},{"id":"13","name":"Marché de Vaugneray","openingHours":"Les mardi de 7h30 à 12h30","partners":[116],"longitude":4.657232165336608,"latitude":45.7379603080526},{"id":"14","name":"Marché Jean Macé","openingHours":"Les mercredi et samedi de 6h à 13h30","partners":[73],"longitude":4.84246551990509,"latitude":45.74567251238216},{"id":"15","name":"Marché Saint Louis","openingHours":"les dimanche de 6h30 à 13h30","partners":[73],"longitude":4.847899675369263,"latitude":45.74887875369156}]}';
    	$response = json_decode( $marketsJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    } else {

    	$partnerCategories = [];

    	$categoriesSelectionQuery = "SELECT rowid AS partnerCategoryID, partner_category AS partnerCategoryLabel, icon AS partnerCategoryIcon, display_order AS displayOrder, hidden AS hidden ";
    	$categoriesSelectionQuery .= "FROM llx_partner_categories ";
    	$categoriesSelectionQuery .= "ORDER BY display_order ASC";
    	if( $result = $db->query( $categoriesSelectionQuery ) ) {
    	    while( $row = $result->fetch_assoc() ) {
    	        if( $format === "app_categories" ) {
    	            /*$parentCategory = -1;
    	            $separator = strrpos( $row['partnerCategoryLabel'], ">" );
    	            if( FALSE !== $separator ) {
    	                $mainCategory = trim( substr( $row['partnerCategoryLabel'], 0, $separator ) );
    	                $row['partnerCategoryLabel'] = trim( substr( $row['partnerCategoryLabel'], $separator + 1 ) );
    	                for( $i = 0; $parentCategory === -1 && $i < count( $response['categories'] ); ++$i ) {
    	                    if( $response['categories'][$i]['label'] === $mainCategory ) {
    	                        $parentCategory = $response['categories'][$i]['id'];
    	                    }
    	                }
    	                if( $parentCategory === -1 ) {
    	                    $parentCategory = intval( $row['partnerCategoryID'], 10 );
    	                    $category = array();
    	                    $category['parentCategoryType'] = -1;
    	                    $category['categoryType'] = 1;
    	                    $category['parentId'] = -1;
    	                    $category['id'] = $parentCategory;
    	                    $category['label'] = $mainCategory;
    	                    $category['displayOrder'] = $row['displayOrder'];
    	                    $category['icon'] = "";
    	                    array_push( $response['categories'], $category );
    	                }
    	            }*/
    	            $category = array();
					//$category['parentCategoryType'] = $parentCategory === -1 ? -1 : 1;
    	    //        $category['categoryType'] = 0;
    	    //        $category['parentId'] = $parentCategory;
    	    		$category['id'] = intval( $row['partnerCategoryID'], 10 );
    	    		$category['label'] = $row['partnerCategoryLabel'];
    	            $category['displayOrder'] = $row['displayOrder'];
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
		        $partnersSelectionQuery .= "societeExtended.shortdescription AS shortDescription, societeExtended.maincategory AS mainCategory, societeExtended.sidecategories AS sideCategories, societeExtended.hideemail AS hideEmail ";
		        $partnersSelectionQuery .= "FROM llx_societe AS societe LEFT JOIN llx_societe_extrafields AS societeExtended ON societe.rowid = societeExtended.fk_object ";
		        $partnersSelectionQuery .= "LEFT JOIN llx_c_country AS country ON societe.fk_pays = country.rowid ";
		        $partnersSelectionQuery .= "WHERE societeExtended.publishedpartner = 1";
		        $partnersSelectionQuery .= $ccode;
		        $partnersSelectionQuery .= " ORDER BY societe.nom";

		        if( $result = $db->query( $partnersSelectionQuery ) ) {
		            while( $row = $result->fetch_assoc() ) {

		            	$isGonetteHeadquarter = ( $row['clientCode'] === "P0000" );

		            	// Hide email if requested so
		            	$row['email'] = $row['hideEmail'] ? "" : $row['email'];

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
		                        $partner['properties']['_storage_options'] = array();
    	                        if( $isGonetteHeadquarter ) {
    	                            $partner['properties']['description'] = $row['address']."\n".$row['zipCode']." ".$row['city']."\n".$row['openingHours']."\n\n".$row['phone'];
    	                            $partner['properties']['_storage_options']['iconUrl'] = $gonetteFrontEndDomain.'/wp-content/uploads/La-Gonette.png';
    	                        } else {
    	                            $partner['properties']['description'] = $row['shortDescription']."\n".$row['openingHours']."\n".'[['.$gonetteFrontEndDomain.'/'.$row['clientCode'].'|En savoir plus]]';
    	                            $partnerFirstCategory = $row['mainCategory'];
    	                            $partner['properties']['_storage_options']['iconUrl'] = $gonetteFrontEndDomain.'/wp-content/uploads/'.$partnerCategories[$partnerFirstCategory]['icon'];
    	                        }
    	                        $partner['properties']['_storage_options']['color'] = '#'.( $row['isExchangeOffice'] ? '00babe' : 'e6411c' );
    	                        $partner['properties']['_storage_options']['iconClass'] = "Drop";
		                        //$partner['properties']['_storage_options']['showLabel'] = false;
		                        //$partner['properties']['_storage_options']['zoomTo'] = 0;
		                    }
		                } else if( $format === "app_partners" ) {
		                	$partner['id'] = $row['id'];
		                	$partner['clientCode'] = $row['clientCode'];
		                	$partner['name'] = $row['name'];
		                	$partner['logo'] = $gonetteBackEndDomain."/dolibarr/documents/societe/".$row['id']."/logos/".$row['logo'];
		                	$partner['phone'] = $row['phone'];
		                	$partner['website'] = $row['website'];
		                	$partner['email'] = $row['email'];
		                	$partner['description'] = $row['description'];
		                	$partner['shortDescription'] = $row['shortDescription'];
		                	$partner['mainCategory'] = $isGonetteHeadquarter ? "-1" : $row['mainCategory'];
		                	$partner['sideCategories'] = $isGonetteHeadquarter ? "-1" : explode( ",", $row['sideCategories'] );
		                	$partner['isGonetteHeadquarter'] = $isGonetteHeadquarter ? "1" : "0";
		                	$partner['locations'] = array();
    	                    // TODO: handle multiple adresses
    	                    if( $partner['clientCode'] === "P0130" ) {
		                	    $location = array();$location['id'] = $row['id']."0";$location['openingHours'] = "Mercredi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.900127649307251;$location['longitude'] = 45.81412959759548;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."1";$location['openingHours'] = "Jeudi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.784189164638519;$location['longitude'] = 45.756795303266784;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."2";$location['openingHours'] = "Mardi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.863510131835938;$location['longitude'] = 45.74949826887272;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."3";$location['openingHours'] = "Mardi de 17h30 à 19h30";$location['isExchangeOffice'] = null;$location['address'] = "13 rue Delandine";$location['city'] = "Lyon";$location['zipCode'] = "69002";$location['latitude'] = 4.827525615692139;$location['longitude'] = 45.74696775871325;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."4";$location['openingHours'] = "Jeudi de 17h00 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.812660813331604;$location['longitude'] = 45.75751578452979;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."5";$location['openingHours'] = "Vendredi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.806400537490845;$location['longitude'] = 45.71495776284155;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."6";$location['openingHours'] = "Mardi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.806341528892517;$location['longitude'] = 45.775971903538235;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."7";$location['openingHours'] = "Mercredi de 17h00 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.914155602455139;$location['longitude'] = 45.78003876304937;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."8";$location['openingHours'] = "Vendredi de 17h00 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.821345806121826;$location['longitude'] = 45.76884763720772;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."9";$location['openingHours'] = "Vendredi de 17h00 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.82799232006073;$location['longitude'] = 45.73068515069315;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."10";$location['openingHours'] = "Mercredi de 17h00 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.909332990646362;$location['longitude'] = 45.73176731078451;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."11";$location['openingHours'] = "Mercredi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.881067872047424;$location['longitude'] = 45.70079576910608;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."12";$location['openingHours'] = "Jeudi de 17h00 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.882156848907471;$location['longitude'] = 45.759922311653874;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	    $location = array();$location['id'] = $row['id']."13";$location['openingHours'] = "Jeudi de 17h30 à 19h15";$location['isExchangeOffice'] = null;$location['address'] = "";$location['city'] = "";$location['zipCode'] = "";$location['latitude'] = 4.878594875335693;$location['longitude'] = 45.77905107099139;$location['displayLocation'] = "1";array_push( $partner['locations'], $location );
		                	} else {
		                	    $location = array();
		                	    $location['id'] = $row['id']."0";
		                	    $location['openingHours'] = $row['openingHours'];
		                	    $location['isExchangeOffice'] = $row['isExchangeOffice'];
		                	    $location['address'] = $row['address'];
		                	    $location['city'] = $row['city'];
		                	    $location['zipCode'] = $row['zipCode'];
		                	    $gpsCoords = explode( ",", $row['gpsCoordinates'] );
		                	    if( count( $gpsCoords ) == 2 ) {
		                	        $location['latitude'] = floatval( $gpsCoords[0] );
		                	        $location['longitude'] = floatval( $gpsCoords[1] );
		                	        $location['displayLocation'] = "1";
		                	    } else {
		                	        $location['displayLocation'] = "0";
		                	    }
		                	    array_push( $partner['locations'], $location );
    	                    }
		                } else if( !$isGonetteHeadquarter ) {
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
	   		                     	array_splice( $partner['partnerCategories'], $i--, 1 );
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
}

if( $format == "geojson" ) {
    $geojsonResponse = [ "type" => "FeatureCollection" ];
    $geojsonResponse['features'] = $response['partners'];
    $response = $geojsonResponse;
    header( "Access-Control-Allow-Origin: https://umap.openstreetmap.fr" );
    header( "Content-type:application/vnd.geo+json;charset=utf-8" );
} else {
	if( strpos( $format, "app_" ) === 0 ) {
        $md5sum = md5( json_encode( $response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        if( $hashonly ) {
            $response = [];
        }
        $response['md5_sum'] = $md5sum;
    }
    header( "Content-type:application/json;charset=utf-8" );
}

echo json_encode( $response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

?>
