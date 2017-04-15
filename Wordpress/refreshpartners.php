<?php

require_once( dirname(__FILE__) . '/wp-load.php' );

/**/
ini_set( "display_errors", "1" );
error_reporting( E_ALL );
/**/

$conf = "TEST";
$gonetteBackEndDomains = array( 'TEST' => "http://82.225.211.150:18001", 'PROD' => "http://dolibarr.lagonette.org" );
$gonetteBackEndDomain = $gonetteBackEndDomains[$conf];

$allowedDomains = array( 'TEST' => "", 'PROD' => "" );
//$allowedDomain = $allowedDomains[$conf];

$ccode = "";
$refreshfaq = 0;

if( ( isset( $_GET['ccode'] ) && preg_match( '/^P[0-9]{4}$/', $_GET['ccode'] ) ) && ( !isset( $allowedDomain ) || !isset( $_SERVER['REMOTE_ADDR'] ) || $_SERVER['REMOTE_ADDR'] === $allowedDomains ) ) {
    $ccode = $_GET['ccode'];
} else if( isset( $_GET['refreshfaq'] ) ) {
    $refreshfaq = intval( $_GET['refreshfaq'], 10 );
}

clog( 'ccode: '.$ccode );
clog( 'refreshfaq: '.$refreshfaq );

if( strlen( $ccode ) > 0 || $refreshfaq == 1 ) {
    $dirtyBit = 0;
    if( strlen( $ccode ) > 0 ) {
        $result = file_get_contents( $gonetteBackEndDomain."/dolibarr/htdocs/partnersinterface.php?logo=1&thumbnail=1&ccode=".$ccode );
        $result = json_decode( $result );

        if( !$result->errors ) {
            $result = $result->partners;

            $existenceInWP = checkExistenceInWP( $ccode );

            // unpublished partner, delete existing page
            if( count( $result ) == 0 ) {
                if( $existenceInWP !== null ) {
                    deletePartnerInWP( $existenceInWP['id'] );
                    $dirtyBit = 1;
                }
            } else {
                $result = $result[0];
                $hashSignature = md5( json_encode( $result ) );
                // published partner not yet in wp, create a new page
                if( $existenceInWP === null ) {
                    clog( 'client does not exist in WP yet, create it' );
                    $result = file_get_contents( $gonetteBackEndDomain."/dolibarr/htdocs/partnersinterface.php?logo=1&ccode=".$ccode );
                    $result = json_decode( $result );
                    
                    if( !$result->errors ) {
                        $result = $result->partners[0];
                        createPartnerInWP( $hashSignature, $result );
                        $dirtyBit = 1;
                    } else {
                        clog( 'error retrieving client full details: '.$result->errors );
                    }
                // published partner that already exists, proceed to update if needed
                } else if( $existenceInWP['hash_signature'] !== $hashSignature ) {
                    log( 'client does exist in WP, but hash signature is different' );
                    $result = file_get_contents( $gonetteBackEndDomain."/dolibarr/htdocs/partnersinterface.php?logo=1&ccode=".$ccode );
                    $result = json_decode( $result );

                    if( !$result->errors ) {
                        $result = $result->partners[0];
                        updatePartnerInWP( $existenceInWP['id'], $hashSignature, $result );
                        $dirtyBit = 1;
                    } else {
                        clog( 'error retrieving client full details: '.$result->errors );
                    }
                }
            }
        } else {
            clog( 'error retrieving client light detailis: '.$result->errors );
        }
    }

    // Pages have been modified, recreate the FAQ
    if( $dirtyBit || $refreshfaq ) {
        clog( 'dirtybit is set, need to update FAQ' );
        $result = file_get_contents( $gonetteBackEndDomain."/dolibarr/htdocs/partnersinterface.php" );
        $result = json_decode( $result );

        if( !$result->errors ) {
            $result = $result->partners;
            updateFAQInWP( $result );
        } else {
            clog( 'error retrieving all clients summary: '.$result->errors );
        }
    }
}

function checkExistenceInWP( $ccode ) {
    clog( 'Check existence of client '.$ccode.' in WP' );
    $post = null;
    $myquery = new WP_Query( "post_type=page&meta_key=code_client&meta_value=".$ccode );
    if( $myquery->have_posts() ) {
        $myquery->the_post();
        $post = ['hash_signature' => "", 'id' => 0];
        $post['id'] = get_the_ID();
        $post['hash_signature'] = get_post_meta( get_the_ID(), "hash_signature", true );
    }
    wp_reset_postdata();
    clog( 'return value: ', $post );
    return $post;
}

function createPostFromPartner( $partner, $hashSignature ) {
    $post = array(
        'ID' => "",
        'post_content' => "",
        'post_title' => "",
        'post_status' => "publish",
        'post_author' => 1,
        'post_type' => "page",
        'comment_status' => "closed",
        'meta_input' => []);
    $post['meta_input']['code_client'] = $partner->clientCode;
    $post['meta_input']['hash_signature'] = $hashSignature;

    $content = '<p style="text-align: center;">***</p>';

    if( $partner->isExchangeOffice ) {
        $content .= '<h3 style="text-align: center;"><span style="color: #ff6600;">*Comptoir de change*</span></h3>';
    }

    $content .= '<h3 style="text-align: center;">Description</h3><p style="text-align: center;">'.$partner->description.'</p>';

    $content .= '<h3 style="text-align: center;">Infos pratiques</h3>';
    if( strlen( $partner->website ) > 0 ) {
        $content .= '<p style="text-align: center;">Site: <a href="http://'.$partner->website.'" target="_blank">'.$partner->website.'</a></p>';
    }

    $content .= '<p style="text-align: center;">'.$partner->address.'<br />'.$partner->zipCode.' '.$partner->city.'</p>';

    $content .= '<h4 style="text-align: center;">&gt;Horaires d\'ouverture</h4><p style="text-align: center;">'.$partner->openingHours.'</p>';

    $content .= '<h4 style="text-align: center;">&gt;Contact</h4>';
    if( strlen( $partner->phone ) > 0 ) {
        $content .= '<p style="text-align: center;">T&eacute;l&eacute;phone: '.$partner->phone.'</p>';
    }
    if( strlen( $partner->email ) > 0 ) {
        $content .= '<p style="text-align: center;">Email: <a href="mailto:'.$partner->email.'">'.$partner->email.'</a></p>';
    }


    $content .= '<p style="text-align: center;">***</p>';

    if( $partner->logo !== null && $partner->logoContent !== null) {
        $logoFileName = '../wp-content/uploads/'.sanitize_file_name( $partner->name ).'_'.$partner->logo;
        $content = '<img class="size-full aligncenter" src="'.$logoFileName.'" alt="'.$partner->logo.'" />'.$content.'<img class="size-full aligncenter" src="'.$logoFileName.'" alt="'.$partner->logo.'" />';
    }

    $post['post_content'] = $content;
    $post['post_title'] = $partner->name;
    $post['post_name'] = $partner->clientCode;
    return $post;
}

function updateLogo( $partner ) {
    if( $partner->logo !== null && $partner->logoContent !== null ) {
        $logoFileName = 'wp-content/uploads/'.sanitize_file_name( $partner->name ).'_'.$partner->logo;
        $ifp = fopen( $logoFileName, "wb" );
        fwrite( $ifp, base64_decode( $partner->logoContent ) );
        fclose( $ifp );
    }
}

function createPartnerInWP( $hashSignature, $partner ) {
    clog( 'createPartnerInWP, signature = '.$hashSignature );
    $post = createPostFromPartner( $partner, $hashSignature );
    wp_insert_post( $post );
    updateLogo( $partner );
}

function updatePartnerInWP( $id, $hashSignature, $partner ) {
    clog( 'updatePartnerInWP, id = '.$id.', signature = '.$hashSignature );
    $post = createPostFromPartner( $partner, $hashSignature );
    $post['ID'] = $id;
    wp_update_post( $post );
    updateLogo( $partner );
}

function deletePartnerInWP( $id ) {
    clog( 'deletePartnerInWP, id = '.$id );
    wp_delete_post( $id, true );
}

function sortFAQs( $a, $b ) {
    return $a['__DISPLAY_ORDER'] == $b['__DISPLAY_ORDER'] ? 0 : ( $a['__DISPLAY_ORDER'] < $b['__DISPLAY_ORDER']  ? 1 : -1 );
}

function updateFAQInWP( $partners ) {
    $myquery = new WP_Query( "post_type=hrf_faq&category_name=categories-partenaires&posts_per_page=-1" );
    while( $myquery->have_posts() ) {
        $myquery->the_post();
        $id = get_the_ID();
        wp_delete_post( $id, true );
    }
    wp_reset_postdata();

    $faq = array();

    $partnersCategoriesCatId = get_cat_ID( "catégories de partenaires" );

    for( $i = 0; $i < count( $partners ); ++$i ) {
        for( $j = 0; $j < count( $partners[$i]->partnerCategories ); ++$j ) {
            if( $partners[$i]->partnerCategories[$j]->hidden ) {
                continue;
            }
            $k = 0;
            while( $k < count( $faq ) ) {
                if( $faq[$k]['post_title'] == $partners[$i]->partnerCategories[$j]->label ) {
                    break;
                }
                ++$k;
            }
            if( $k === count( $faq ) ) {
                $newFaq = array(
                    '__DISPLAY_ORDER' => $partners[$i]->partnerCategories[$j]->displayOrder,
                    'ID' => "",
                    'post_content' => "",
                    'post_title' => "",
                    'post_status' => "publish",
                    'post_author' => 1,
                    'post_type' => "hrf_faq",
                    'post_category' => array( $partnersCategoriesCatId ),
                    'comment_status' => "closed",
                );
                $newFaq['post_title'] = $partners[$i]->partnerCategories[$j]->label;
                array_push( $faq, $newFaq );
            }
            $faq[$k]['post_content'] .= '<div><ul><li>'.$partners[$i]->name.'<br />'.$partners[$i]->shortDescription.'<br />'.$partners[$i]->address.', '.$partners[$i]->zipCode.' '.$partners[$i]->city;
            if( strlen( $partners[$i]->website ) > 0 ) {
                $faq[$k]['post_content'] .= '<br /><a href="http://'.$partners[$i]->website.'" target="_blanck">'.$partners[$i]->website.'</a>';
            }
            $faq[$k]['post_content'] .= '<br /><a href="'.$partners[$i]->clientCode.'" target="_blanck">En savoir plus...</a></li></ul></div>';
        }
    }

    usort ( $faq , "sortFAQs" );

    for( $i = 0; $i < count( $faq ); ++$i ) {
        array_splice( $faq[$i], 0, 1 );
        wp_insert_post( $faq[$i] );
    }
}

function clog( $str, $dump = null) {
    $debug = true;
    if( $debug === true ) {
        echo $str.'<br />';
        if( $dump !== null ) {
            var_dump( $dump );
            echo '<br />';
        }
    }
}

?>
