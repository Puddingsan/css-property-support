<?php require_once('styledata.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
class Obj {};
$platform_code = isset($_REQUEST['platform']) ? $_REQUEST['platform'] : "win";
$browser_code = isset($_REQUEST['browser']) ? $_REQUEST['browser'] : "CHR";
$version_code = isset($_REQUEST['version']) ? $_REQUEST['version'] : "latest";
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "getrecord";


mysql_select_db($database_styledata, $styledata);
switch ($action) {
	case 'getrecord' :
		$query_styledata = sprintf("SELECT * FROM tbl_data WHERE data_platform_code = %s AND data_browser_code = %s" . ($version_code=="latest" ? " ORDER BY data_version DESC" : " AND data_version = %s") . " LIMIT 1",
			GetSQLValueString($platform_code, "text"),
			GetSQLValueString($browser_code, "text"),
			GetSQLValueString($version_code, "text")
		);
		$styledata = mysql_query($query_styledata, $styledata) or die(mysql_error());
		$row_styledata = mysql_fetch_assoc($styledata);
		
		$jsonObj = new Obj;
		if (mysql_num_rows($styledata)==1) {
			foreach ($row_styledata as $entry=>$value) {
				$name = str_replace(array("data_", "_code"), "", $entry);
			//	$jsonObj->$name = ($entry=="data_json") ? json_decode($value) : $value;
				$jsonObj->$name = $value;
			}
		} else {
			$name = 'error';
			$platform = 'platform';
			$browser = 'browser';
			$version = 'version';
			$jsonObj->$name = "RECORD_NOT_FOUND";
			$jsonObj->$platform = $platform_code;
			$jsonObj->$browser = $browser_code;
			$jsonObj->$version = $version_code;
		}
		mysql_free_result($styledata);
	break;
	case 'getversions' :
		$versiondata = array();
		$query_versions = "SELECT data_label AS label, data_version AS version, data_browser_code AS browser, data_platform_code AS platform FROM tbl_data";
		$versions = mysql_query($query_versions, $styledata) or die(mysql_error());
		$row_versions = mysql_fetch_assoc($versions);
//		$jsonObj = new Obj;
		do {
			/*
			$jsonObj = new Obj;
			 $jsonObj->label = $row_versions['label'];
			 $jsonObj->version = $row_versions['version'];
			 $jsonObj->browser = $row_versions['browser'];
			 $jsonObj->platform = $row_versions['platform'];
			$versiondata[] = $jsonObj;
			*/
			$versiondata[] = array(
			'label'=>$row_versions['label'],
			'version'=>$row_versions['version'],
			'browser'=>$row_versions['browser'],
			'platform'=>$row_versions['platform'],
			);
		} while ($row_versions = mysql_fetch_assoc($versions));
		foreach ($versiondata as $key => $row) {
			$label[$key] = $row['label'];
			$version[$key] = $row['version'];
			$browser[$key] = $row['browser'];
			$platform[$key] = $row['platform'];
		}
		array_multisort($platform, SORT_DESC, $browser, $version, SORT_DESC, $versiondata);
		$jsonObj = $versiondata;
		
		mysql_free_result($versions);
	break;
}
//	header("Content-type: appplication/json"); // ;charset=utf8
//header("Content-type: appplication/json");

print json_encode($jsonObj);
?>
