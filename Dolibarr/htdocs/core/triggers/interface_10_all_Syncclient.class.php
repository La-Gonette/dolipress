<?php
/**
 *  \file       htdocs/core/triggers/interface_10_all_SyncClientWithThirdParties.class.php
 *  \ingroup    core
 *  \brief
 *  \remarks
 */
require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

/**
 *  Class of triggers for demo module
 */
class InterfaceSyncclient extends DolibarrTriggers {
    public $family = 'thirdpartysync';
    public $picto = 'technic';
    public $description = "Purpose of this trigger is to synchronize the creation/modification/deletion of a client (company) with third party tools";
    public $version = self::VERSION_DOLIBARR;
    public $name = "Syncclient";

    public function runTrigger( $action, $object, User $user, Translate $langs, Conf $conf ) {
        switch( $action ) {
          case 'COMPANY_CREATE':
          case 'COMPANY_MODIFY':
          case 'COMPANY_DELETE':
            $nowAsDateTime = date( "Y-m-d H:i:s" );
            $object->db->query( 'INSERT INTO llx_touched_partners(rowid, partner_code, modification_datetime) VALUES(\'\', \''.$object->code_client.'\', \''.$nowAsDateTime.'\')' );
            break;
        }
        return 0;
    }
}
