<?php
	// This example header.inc.php is intended to be modfied for your application.
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php _p(QApplication::$EncodingType); ?>" />
<?php
    if (isset($strPageTitle))
        print "<title> $strPageTitle </title>";       
    $stylesheet = __SUBDIRECTORY__ . "/core/assets/css/quasiadmin.css";
    if (file_exists( __DOCROOT__ . $stylesheet) )
        print '<link rel="stylesheet" type="text/css" href="' .  __VIRTUAL_DIRECTORY__  . $stylesheet . '">';
    else
        print "Stylesheet missing - flying naked ..<br />\n";
     
     $strProtocol = Quasi::$IsSsl ? 'https://' : 'http://';
     $strTinyMceUri = $strProtocol . Quasi::$ServerName . __QUASI_CORE_JS__ . '/tiny_mce/tiny_mce.js';
?>
<script type="text/javascript" src="<?php print $strTinyMceUri; ?>"></script>
	</head><body>
