<?php
require_once (dirname(__FILE__) . "/master.inc.php");

/** /
 ini_set( "display_errors", "1" );
 error_reporting( E_ALL );
 /*
 */
$conf = "";
$allowedDomains = array(
    'TEST' => "",
    'PROD' => ""
);

// $allowedDomain = $allowedDomains[$conf];

$gonetteBackEndDomains = array(
    'TEST' => "http://82.225.211.150:18001",
    'PROD' => "https://dolibarr.lagonette.org"
);
$gonetteBackEndDomain = $gonetteBackEndDomains[$conf];
$gonetteFrontEndDomains = array(
    'TEST' => "http://82.225.211.150:18002",
    'PROD' => "http://www.lagonette.org"
);
$gonetteFrontEndDomain = $gonetteFrontEndDomains[$conf];
$response = ["errors" => ""];

if (isset($allowedDomain) && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== $allowedDomain) {
    $response['errors'] = "";
}
else {
    $ccode = "";
    $logo = 0;
    $thumbnail = 0;
    $format = "wordpress";
    $hashonly = 0;
    if (isset($_GET['format']) && preg_match('/^wordpress|geojson|app_categories|app_partners|app_markets$/', $_GET['format'])) {
        $format = $_GET['format'];
    }

    if ($format === "app_categories") {
        $response['categories'] = [];
    }
    else
    if ($format === "app_markets") {
        $response['markets'] = [];
    }
    else {
        $response['partners'] = [];
    }

    if (strpos($format, "app_") === 0 && isset($_GET['hashonly'])) {
        $hashonly = intval($_GET['hashonly'], 10);
    }

    if (isset($_GET['ccode']) && preg_match('/^P[0-9]{4}$/', $_GET['ccode'])) {
        $ccode = " AND societe.code_client = '" . $_GET['ccode'] . "'";
        $startIndex = 0;
        $maxNumberOfResults = 1;
    }

    if (isset($_GET['logo'])) {
        $logo = intval($_GET['logo'], 10);
        if ($logo && isset($_GET['thumbnail'])) {
            $thumbnail = intval($_GET['thumbnail'], 10);
        }
    }

    if ($format === "app_markets") {
        $marketsJson = '{"errors":"","markets":[{"id":"1","name":"Marché bio  de la Croix Rousse","openingHours":"Les samedi de 6h à 13h30","partners":[116,92],"longitude":4.831280708312988,"latitude":45.77426575456372},{"id":"2","name":"Marché bio de Collonge aux monts d\'or","openingHours":"Les vendredi de 16h30 à 19h30","partners":[116],"longitude":4.850678443908691,"latitude":45.81694120688579},{"id":"3","name":"Marché Bio de Grezieu la Varenne","openingHours":"Les vendredi de 14h à 19h","partners":[116,230],"longitude":4.692943096160889,"latitude":45.74839773565179},{"id":"4","name":"Marché bio de Vaise","openingHours":"Les mardi de 6 à 13h","partners":[73],"longitude":4.805504679679871,"latitude":45.77513754334658},{"id":"5","name":"Marché bio des clarines","openingHours":"Les samedi de 9h à 13h","partners":[101],"longitude":4.604537487030029,"latitude":45.59384614820627},{"id":"6","name":"Marché bio du Chapi","openingHours":"Les vendredi de 16h30 à 19h","partners":[101],"longitude":4.699380397796631,"latitude":45.67276838832159},{"id":"7","name":"Marché bio Monplaisir","openingHours":"Les mercredi de 15h à 20h","partners":[116],"longitude":4.871245622634887,"latitude":45.74472164768029},{"id":"8","name":"Marché de Caluire","openingHours":"Les samedi de 7h30 à 12h30","partners":[73],"longitude":4.846612215042114,"latitude":45.79051694856825},{"id":"9","name":"Marché de lentilly","openingHours":"Les mercredi et dimanche, de 8h à 12h (13h le dimanche)","partners":[73],"longitude":4.662698507308959,"latitude":45.81940124856704},{"id":"10","name":"Marché de Sainte Foy l\'argentière","openingHours":"Les samedi de 8h00 à 13h","partners":[101],"longitude":4.663696289062501,"latitude":45.767522962149904},{"id":"11","name":"Marché de Tarare","openingHours":"Les samedi matin","partners":[109],"longitude":4.4336700439453125,"latitude":45.8966670611441},{"id":"12","name":"Marché de Tassin la demi lune","openingHours":"Les vendredi de 7h30 à 12h30","partners":[73],"longitude":4.778569936752319,"latitude":45.76262064242203},{"id":"13","name":"Marché de Vaugneray","openingHours":"Les mardi de 7h30 à 12h30","partners":[116],"longitude":4.657232165336608,"latitude":45.7379603080526},{"id":"14","name":"Marché Jean Macé","openingHours":"Les mercredi et samedi de 6h à 13h30","partners":[73],"longitude":4.84246551990509,"latitude":45.74567251238216},{"id":"15","name":"Marché Saint Louis","openingHours":"les dimanche de 6h30 à 13h30","partners":[73],"longitude":4.847899675369263,"latitude":45.74887875369156}]}';
        $response = json_decode($marketsJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    else {
        $partnerCategories = [];
        $categoriesSelectionQuery = "SELECT rowid AS partnerCategoryID, partner_category AS partnerCategoryLabel, icon AS partnerCategoryIcon, display_order AS displayOrder, hidden AS hidden ";
        $categoriesSelectionQuery.= "FROM llx_partner_categories ";
        $categoriesSelectionQuery.= "ORDER BY display_order ASC";
        if ($result = $db->query($categoriesSelectionQuery)) {
            while ($row = $result->fetch_assoc()) {
                if ($format === "app_categories") {
                    $labels = explode(">", $row['partnerCategoryLabel']);
                    $label = trim($labels[count($labels) - 1]);

                    $category = array();
                    $category['id'] = intval($row['partnerCategoryID'], 10);
                    $category['label'] = $label;
                    $category['displayOrder'] = $row['displayOrder'];
                    $category['icon'] = $gonetteFrontEndDomain . '/wp-content/uploads/' . $row['partnerCategoryIcon'];
                    array_push($response['categories'], $category);
                }
                else {
                    $catId = $row['partnerCategoryID'];
                    $partnerCategories[$catId] = array(
                        'label' => $row['partnerCategoryLabel'],
                        'icon' => $row['partnerCategoryIcon'],
                        'displayOrder' => $row['displayOrder'],
                        'hidden' => $row['hidden']
                    );
                }
            }

            $result->free();
            if ($format !== "app_categories") {
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
                $partnersSelectionQuery.= "societe.zip AS zipCode, societe.fk_departement AS region, country.code AS countryCode, country.label AS countryName, societeExtended.gpscoords AS gpsCoordinates, societe.phone AS phone, ";
                $partnersSelectionQuery.= "societe.url AS website, societe.email AS email, societeExtended.description AS description, societeExtended.openinghours AS openingHours, societeExtended.exchangeoffice AS isExchangeOffice, ";
                $partnersSelectionQuery.= "societeExtended.shortdescription AS shortDescription, societeExtended.maincategory AS mainCategory, societeExtended.sidecategories AS sideCategories, societeExtended.hideemail AS hideEmail ";
                $partnersSelectionQuery.= "FROM llx_societe AS societe LEFT JOIN llx_societe_extrafields AS societeExtended ON societe.rowid = societeExtended.fk_object ";
                $partnersSelectionQuery.= "LEFT JOIN llx_c_country AS country ON societe.fk_pays = country.rowid ";
                $partnersSelectionQuery.= "WHERE societeExtended.publishedpartner = 1";
                $partnersSelectionQuery.= $ccode;
                $partnersSelectionQuery.= " ORDER BY societe.nom";
                if ($result = $db->query($partnersSelectionQuery)) {
                    $locationId = 1;
                    while ($row = $result->fetch_assoc()) {
                        $isGonetteHeadquarter = ($row['clientCode'] === "P0000");

                        // Hide email if requested so

                        $row['email'] = $row['hideEmail'] ? "" : $row['email'];
                        $partner = array();
                        if ($format === "geojson") {
                            $gpsCoords = explode(",", $row['gpsCoordinates']);
                            if (count($gpsCoords) == 2) {
                                $partner['type'] = "Feature";
                                $partner['properties'] = array();
                                $partner['geometry']['type'] = "Point";
                                $partner['geometry']['coordinates'] = array();
                                array_push($partner['geometry']['coordinates'], floatval($gpsCoords[1]));
                                array_push($partner['geometry']['coordinates'], floatval($gpsCoords[0]));
                                $partner['properties']['name'] = $row['name'];
                                $partner['properties']['_storage_options'] = array();
                                if ($isGonetteHeadquarter) {
                                    $partner['properties']['description'] = $row['address'] . "\n" . $row['zipCode'] . " " . $row['city'] . "\n" . $row['openingHours'] . "\n\n" . $row['phone'];
                                    $partner['properties']['_storage_options']['iconUrl'] = $gonetteFrontEndDomain . '/wp-content/uploads/La-Gonette.png';
                                }
                                else {
                                    $partner['properties']['description'] = $row['shortDescription'] . "\n" . $row['openingHours'] . "\n" . '[[' . $gonetteFrontEndDomain . '/' . $row['clientCode'] . '|En savoir plus]]';
                                    $partnerFirstCategory = $row['mainCategory'];
                                    $partner['properties']['_storage_options']['iconUrl'] = $gonetteFrontEndDomain . '/wp-content/uploads/' . $partnerCategories[$partnerFirstCategory]['icon'];
                                }

                                $partner['properties']['_storage_options']['color'] = '#' . ($row['isExchangeOffice'] ? '00babe' : 'e6411c');
                                $partner['properties']['_storage_options']['iconClass'] = "Drop";

                                // $partner['properties']['_storage_options']['showLabel'] = false;
                                // $partner['properties']['_storage_options']['zoomTo'] = 0;

                            }
                        }
                        else if ($format === "app_partners") {
                            $partner['id'] = $row['id'];
                            $partner['clientCode'] = $row['clientCode'];
                            $partner['name'] = $row['name'];
                            $partner['logo'] = $gonetteBackEndDomain . "/dolibarr/documents/societe/" . $row['id'] . "/logos/" . $row['logo'];
                            $partner['phone'] = $row['phone'];
                            $partner['website'] = $row['website'];
                            $partner['email'] = $row['email'];
                            $partner['description'] = $row['description'];
                            $partner['shortDescription'] = $row['shortDescription'];
                            $partner['mainCategory'] = $isGonetteHeadquarter ? "-1" : $row['mainCategory'];
                            $partner['sideCategories'] = $isGonetteHeadquarter ? [] : explode(",", $row['sideCategories']);
                            $partner['isGonetteHeadquarter'] = $isGonetteHeadquarter ? "1" : "0";
                            $partner['locations'] = array();

                            // TODO: handle multiple adresses

                            if ($partner['clientCode'] === "P0130") {
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mardi de 17h00 à 19h15";    $location['isExchangeOffice'] = null; $location['address'] = "13, rue Delandine";               $location['city'] = "Lyon";            $location['zipCode'] = "69002"; $location['latitude'] = 45.7469504;        $location['longitude'] = 4.827567300000055;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Vendredi de 17h à 19h";     $location['isExchangeOffice'] = null; $location['address'] = "36 Cours du Général Giraud";      $location['city'] = "Lyon";            $location['zipCode'] = "69001"; $location['latitude'] = 45.7687103;        $location['longitude'] = 4.820799999999963;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Jeudi de 17h à 19h";        $location['isExchangeOffice'] = null; $location['address'] = "6 rue des Fossées de Trion";      $location['city'] = "Lyon";            $location['zipCode'] = "69005"; $location['latitude'] = 45.75749099999999; $location['longitude'] = 4.812553600000001;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Jeudi de 17h30 à 19h15";    $location['isExchangeOffice'] = null; $location['address'] = "10 impasse Secret";               $location['city'] = "Lyon";            $location['zipCode'] = "69005"; $location['latitude'] = 45.7552259;        $location['longitude'] = 4.7945038000000295;  $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Vendredi de 17h à 19h";     $location['isExchangeOffice'] = null; $location['address'] = "1 rue Jacques Monod";             $location['city'] = "Lyon";            $location['zipCode'] = "69007"; $location['latitude'] = 45.73072130000001; $location['longitude'] = 4.828029700000002;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Vendredi de 17h30 à 19h15"; $location['isExchangeOffice'] = null; $location['address'] = "91 Rue de la République";         $location['city'] = "Oullins";         $location['zipCode'] = "69600"; $location['latitude'] = 45.714789;         $location['longitude'] = 4.806413099999986;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Jeudi de 17h30 à 19h15";    $location['isExchangeOffice'] = null; $location['address'] = "39 Rue Georges Courteline";       $location['city'] = "Villeurbanne";    $location['zipCode'] = "69100"; $location['latitude'] = 45.779221;         $location['longitude'] = 4.878558999999996;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Jeudi de 17h à 19h";        $location['isExchangeOffice'] = null; $location['address'] = "19 Rue Louis Braille";            $location['city'] = "Villeurbanne";    $location['zipCode'] = "69100"; $location['latitude'] = 45.7594383;        $location['longitude'] = 4.882456600000069;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mercredi de 17h à 19h";     $location['isExchangeOffice'] = null; $location['address'] = "3 Avenue Maurice Thorez";         $location['city'] = "Vaulx-en-Velin";  $location['zipCode'] = "69120"; $location['latitude'] = 45.7803056;        $location['longitude'] = 4.9138977999999724;  $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mercredi de 17h30 à 19h15"; $location['isExchangeOffice'] = null; $location['address'] = "9 bis Avenue du Général Leclerc"; $location['city'] = "Rilleux-la-Pape"; $location['zipCode'] = "69140"; $location['latitude'] = 45.8141458;        $location['longitude'] = 4.900217300000008;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mercredi de 17h à 19h";     $location['isExchangeOffice'] = null; $location['address'] = "20 Rue Villard";                  $location['city'] = "Bron";            $location['zipCode'] = "69500"; $location['latitude'] = 45.7322109;        $location['longitude'] = 4.908110099999931;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mardi de 17h à 19h";        $location['isExchangeOffice'] = null; $location['address'] = "8 Rue Saint Théodore";            $location['city'] = "Lyon";            $location['zipCode'] = "69003"; $location['latitude'] = 45.7494349;        $location['longitude'] = 4.86304240000004;    $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mercredi de 17h30 à 19h";   $location['isExchangeOffice'] = null; $location['address'] = "13 Avenue Marcel Paul";           $location['city'] = "Vénissieux";      $location['zipCode'] = "69200"; $location['latitude'] = 45.6998899;        $location['longitude'] = 4.881551100000024;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                                $location = array(); $location['id'] = $locationId++;  $location['openingHours'] = "Mardi de 17h30 à 19h";      $location['isExchangeOffice'] = null; $location['address'] = "19 Rue Roquette";                 $location['city'] = "Lyon";            $location['zipCode'] = "69009"; $location['latitude'] = 45.77634519999999; $location['longitude'] = 4.807012500000042;   $location['displayLocation'] = "1"; array_push($partner['locations'], $location);
                            }
                            else {
                                $location = array();
                                $location['id'] = $locationId++;
                                $location['openingHours'] = $row['openingHours'];
                                $location['isExchangeOffice'] = $row['isExchangeOffice'];
                                $location['address'] = $row['address'];
                                $location['city'] = $row['city'];
                                $location['zipCode'] = $row['zipCode'];
                                $gpsCoords = explode(",", $row['gpsCoordinates']);
                                if (count($gpsCoords) == 2) {
                                    $location['latitude'] = floatval($gpsCoords[0]);
                                    $location['longitude'] = floatval($gpsCoords[1]);
                                    $location['displayLocation'] = "1";
                                }
                                else {
                                    $location['displayLocation'] = "0";
                                }

                                array_push($partner['locations'], $location);
                            }
                        }
                        else
                        if (!$isGonetteHeadquarter) {
                            foreach($row as $key => $value) {
                                $partner[$key] = $value;
                            }

                            $partner['partnerCategories'] = array();
                            $partner['partnerCategories'][] = $partner['mainCategory'];
                            if (isset($partner['sideCategories']) && strlen($partner['sideCategories']) > 0) {
                                $partner['partnerCategories'] = array_merge($partner['partnerCategories'], explode(",", $partner['sideCategories']));
                            }

                            for ($i = 0; $i < count($partner['partnerCategories']); ++$i) {
                                $cat = $partner['partnerCategories'][$i];
                                if (isset($partnerCategories[$cat])) {
                                    $partner['partnerCategories'][$i] = $partnerCategories[$cat];
                                }
                                else {
                                    array_splice($partner['partnerCategories'], $i--, 1);
                                }
                            }

                            if ($logo && $row['logo']) {
                                if (!$thumbnail) {
                                    $logopath = $row['logo'];
                                }
                                else {
                                    $logoname = pathinfo($row['logo'], PATHINFO_FILENAME);
                                    $logoextension = pathinfo($row['logo'], PATHINFO_EXTENSION);
                                    $logopath = "thumbs/" . $logoname . "_mini." . $logoextension;
                                }

                                $logo = file_get_contents("../documents/societe/" . $row['id'] . "/logos/" . $logopath);
                                $partner['logoContent'] = $logo ? base64_encode($logo) : null;
                            }
                        }

                        if (count($partner) > 0) {
                            array_push($response['partners'], $partner);
                        }
                    }

                    $result->free();
                }
                else {
                    $response['errors'] = "Error executing query Published Partners: " . $db->error;
                }
            }
        }
        else {
            $response['errors'] = "Error executing query Partner categories: " . $db->error;
        }
    }
}

if ($format == "geojson") {
    $geojsonResponse = ["type" => "FeatureCollection"];
    $geojsonResponse['features'] = $response['partners'];
    $response = $geojsonResponse;
    header("Access-Control-Allow-Origin: https://umap.openstreetmap.fr");
    header("Content-type:application/vnd.geo+json;charset=utf-8");
}
else {
    if (strpos($format, "app_") === 0) {
        $md5sum = md5(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ($hashonly) {
            $response = [];
        }

        $response['md5_sum'] = $md5sum;
    }

    header("Content-type:application/json;charset=utf-8");
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
