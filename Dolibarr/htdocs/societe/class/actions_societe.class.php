<?php

$path=dirname(__FILE__).'/';
require_once($path."../../../scripts/geocoding/geocoding.php");

class ActionsSociete
{
        /**
         * Overloading the doActions function : replacing the parent's function with the one below
         *
         * @param   array()         $parameters     Hook metadatas (context, etc...)
         * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
         * @param   string          &$action        Current action (if set). Generally create or edit or null
         * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
         * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
         */
        function insertExtraFields($parameters, &$object, &$action, $hookmanager)
        {
                $error = 0; // Error counter

                if (in_array('thirdpartydao', explode(':', $parameters['context'])) && $action == 'update' )
                {
                        if( !$object->array_options['options_bypasscoordinatescalc'] ) {
                                $geocode = geocoding_address2gps( $object->address.' '.$object->zip.' '.$object->town );
                                if( is_null( $geocode ) ) {
                                    $object->array_options['options_gpscoords'] = "ADRESSE INCONNUE";
                                	$object->array_options['options_publishedpartner'] = 0;
                                } else {
                                	$object->array_options['options_gpscoords'] = $geocode['latitude'].",".$geocode['longitude'];
                                }
                        }
                }

                if (! $error)
                {
                        $this->results = array();
                        $this->resprints = 'A text to show';
                        return 0; // or return 1 to replace standard code
                }
                else
                {
                        $this->errors[] = 'Error message';
                        return -1;
                }
        }
}

?>
