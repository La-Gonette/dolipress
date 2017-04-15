<?php

$path=dirname(__FILE__).'/';

$error=0;

// -------------------- START OF YOUR CODE HERE --------------------
// Include and load Dolibarr environment variables
require_once($path."../../htdocs/master.inc.php");
// After this $db, $mysoc, $langs, $conf and $hookmanager are defined (Opened $db handler to database will be closed at end of file).
// $user is created but empty.

// may be helpful someday: dol_getmypid()

// Start of transaction
$db->begin();

$conf = "TEST";
$gonetteFrontEndDomains = array( 'TEST' => "http://82.225.211.150:18002", 'PROD' => "http://www.lagonette.org" );
$gonetteFrontEndDomain = $gonetteFrontEndDomains[$conf];

$partnersToUpdate = array();
$oneMinuteAgo = date( "Y-m-d H:i:s", time() - 60 );
if( $result = $db->query( 'SELECT DISTINCT tp.partner_code AS partnerCode, ijtp.modificationDatetime AS modificationDateTime FROM llx_touched_partners tp INNER JOIN (SELECT partner_code, max(modification_datetime) as modificationDatetime FROM llx_touched_partners GROUP BY partner_code) ijtp ON ijtp.partner_code = tp.partner_code WHERE ijtp.modificationDatetime < \''.$oneMinuteAgo.'\'' ) ) {
    while( $row = $result->fetch_assoc() ) {
        array_push( $partnersToUpdate, $row );
    }
    $result->free();
}

foreach( $partnersToUpdate as $partner ) {
	print('Doing a call');
    file_get_contents( $gonetteFrontEndDomain."/refreshpartners.php?ccode=".$partner['partnerCode'] );
    $db->query( 'DELETE FROM llx_touched_partners WHERE partner_code=\''.$partner['partnerCode'].'\' AND modification_datetime <= \''.$partner['modificationDateTime'].'\'' );
}

// -------------------- END OF YOUR CODE --------------------

if (! $error)
{
        $db->commit();
}
else
{
        $db->rollback();
}

$db->close();   // Close $db database opened handler

exit($error);

?>
