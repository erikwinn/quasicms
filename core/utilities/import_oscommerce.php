<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/

require_once('../../includes/prepend.inc.php');
require_once('../classes/ImportOsCommerce.class.php');

/**
* This utility imports an OsCommerce database into Quasi ..
*
* Note: currently you must set the values for the OsCommerce database to import.
*@todo - allow setting the database configs ..
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: import_oscommerce.php 98 2008-08-29 21:38:39Z erikwinn $
*@version 0.1
*
*@copyright (C) 2008 by Erik Winn
*@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111 USA

*
*@package Quasi
*/
    define(DB_SERVER,'your_osc_database_server');
    define(DB_DATABASE,'your_osc_database_name');
    define(DB_SERVER_USERNAME,'your_osc_username');
    define(DB_SERVER_PASSWORD,'your_oce_password');

    $objImporter = new ImportOsCommerce();
    
    $objImporter->Run();
?>