<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RepositoryReports/classes/autoload.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RepositoryReports/classes/Settings/ilDB.php");
$settings_db = new \CaT\Plugins\RepositoryReports\Settings\ilDB($ilDB);
$settings_db->install();
?>