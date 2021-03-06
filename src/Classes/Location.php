<?php
/**
 * This file is part of the SevenShores/NetSuite library
 * AND originally from the NetSuite PHP Toolkit.
 *
 * New content:
 * @package    ryanwinchester/netsuite-php
 * @copyright  Copyright (c) Ryan Winchester
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @link       https://github.com/ryanwinchester/netsuite-php
 *
 * Original content:
 * @copyright  Copyright (c) NetSuite Inc.
 * @license    https://raw.githubusercontent.com/ryanwinchester/netsuite-php/master/original/NetSuite%20Application%20Developer%20License%20Agreement.txt
 * @link       http://www.netsuite.com/portal/developers/resources/suitetalk-sample-applications.shtml
 *
 * generated:  2016-06-02 02:54:03 PM UTC
 */

namespace NetSuite\Classes;

class Location extends Record {
    public $name;
    public $parent;
    public $includeChildren;
    public $subsidiaryList;
    public $isInactive;
    public $tranPrefix;
    public $mainAddress;
    public $returnAddress;
    public $timeZone;
    public $automaticLatLongSetup;
    public $latitude;
    public $longitude;
    public $logo;
    public $makeInventoryAvailable;
    public $makeInventoryAvailableStore;
    public $classTranslationList;
    public $customFieldList;
    public $internalId;
    public $externalId;
    static $paramtypesmap = array(
        "name" => "string",
        "parent" => "RecordRef",
        "includeChildren" => "boolean",
        "subsidiaryList" => "RecordRefList",
        "isInactive" => "boolean",
        "tranPrefix" => "string",
        "mainAddress" => "Address",
        "returnAddress" => "Address",
        "timeZone" => "LocationTimeZone",
        "automaticLatLongSetup" => "boolean",
        "latitude" => "float",
        "longitude" => "float",
        "logo" => "RecordRef",
        "makeInventoryAvailable" => "boolean",
        "makeInventoryAvailableStore" => "boolean",
        "classTranslationList" => "ClassTranslationList",
        "customFieldList" => "CustomFieldList",
        "internalId" => "string",
        "externalId" => "string",
    );
}
