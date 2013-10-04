<html>
<head>
	<title>Множественное копирование документов</title>
	<link rel='stylesheet' type='text/css' href='/assets/modules/copyEvo/style.css' />
	<script src="media/script/mootools/mootools.js" type="text/javascript"></script>
	<script src="/assets/modules/copyEvo/script.js" type="text/javascript"></script>
</head>

<body>
<div class="copy-docs">	
<h1>Множественное копирование ресурсов <sup>(alpha 0.1)</sup></h1>

<?php
// get parent Id
echo  "<form method='post'>
<table class='f-table'>
	<tr>
		<td><label>Введите id родителя</label></td>
		<td rowspan='2'>ИЛИ</td>
		<td>Введите id ресурсов через запятую</td>
	</tr>
	<tr>
		<td><input type='text' name='whatid' value=''></td> 
		<td><input type='text' name='resourcestid' value=''></td>
	</tr>
	<tr>
		<td> 
<label>Показать по:</label><br>
<input type='text' name='count' value='20'></td>
		<td></td>
		<td></td>
	</tr>
</table>
<br>
<input type='submit'>
</form>
";

// html code
$output = "<div class='doc-list'>
<table class='copy-table'>
<form method='post'>
<thead>
<tr>
<td width='20' align='center'>-</td>
<td width='20' align='center'>id</td>
<td>Заголовок ресурса</td>
</tr>
</thead>

";

$tpl = "<tr>
<td align='center'><input type='checkbox' name='docid[]' value='[+id+]'  class='check-me'></td>
<td align='center'>[+id+]</td>
<td>
[+pagetitle+]
<input type='hidden' name='docname[]' value='[+pagetitle+]'>
</td>
</tr>";

$whatid   = (isset($_POST['whatid'])) ? $_POST['whatid'] : '' ;
$count    = (isset($_POST['count'])) ? $_POST['count'] : '20';
$docid 	  = (isset($_POST['docid'])) ? $_POST['docid'] : '' ;
$parentid = (isset($_POST['parentid'])) ? $_POST['parentid'] : $whatid ;
$docname  = (isset($_POST['docname'])) ? $_POST['docname'] : '';
$newtitle  = (isset($_POST['newtitle'])) ? $_POST['newtitle'] : '';
$ispublish  = (isset($_POST['ispublish'])) ? $_POST['ispublish'] : '0';
$resourcestid = (isset($_POST['resourcestid'])) ? $_POST['resourcestid'] : '';
$db_pref  = $modx->db->config['table_prefix'];

// if exist parent - show table
if($whatid OR $resourcestid){

	$output .= "Показать <b>$count</b> ресурсов из контейнера <b>$parentid</b>";
	if($resourcestid) $where = " WHERE  id IN (".$resourcestid .") " ;
	else $where = "WHERE  parent =".intval($whatid) ;

	$query = $modx->db->query("
	 SELECT id, pagetitle
	 FROM  ".$db_pref."site_content
	 ".$where."
	 ORDER BY id ASC
	 LIMIT ".intval($count)."
	");

	$result = $modx->db->makeArray($query); 
	if(!$result) die('Нет ресурсов');

	foreach ($result as $ar) {
		$output .= "<tr>
			<td align='center'><input type='checkbox' name='docid[]' value='".$ar['id']."'  class='check-me'></td>
			<td align='center'>".$ar['id']."</td>
			<td>
			 ".$ar['pagetitle']."
			<input type='hidden' name='docname[".$ar['id']."]' value='".$ar['pagetitle']."'>
			</td>
			</tr>"; 
	} 

	$output .="
	<tr><td colspan='3'>
	<input type='submit' value='Copy' class='copy-but'> <br> 
	<label>Родительский контейнер</label>  <br> 
	<input type='text' class='par-id' name='parentid' value='".$whatid."'><br>
	<input type='checkbox' name='ispublish' value='1'><label>Опубликовать сразу</label> 
	<br><br>

	<label>Новый заголовок</label> <br>
	<input type='text' class='newtitle' style='width:350px;' name='newtitle' value=''>
	 

	
	</td></tr>
	</form></table></div>";

	echo $output;

} //whatid

if ($docid){  
	foreach($docid as $value){ 
		duplicateDocument($value, $parent, $newtitle, $ispublish);	 
		echo $docname[$value]." - is copied <br>"; 
	}
}

if(!isset($_POST)){ exit; }

?>
<!-- Refresh tree -->
<script>top.mainMenu.reloadtree();</script> 

<?php

function duplicateDocument($docid, $parent=null,$newtitle=null, $ispublish=null, $_toplevel=0) {
	global $modx;

	// invoke OnBeforeDocDuplicate event
	$evtOut = $modx->invokeEvent('OnBeforeDocDuplicate', array(
		'id' => $docid
	));

	$myChildren = array();
	$userID = $modx->getLoginUserID();

	$tblsc = $modx->getFullTableName('site_content');

	// Grab the original document
	$rs = $modx->db->select('*', $tblsc, 'id='.$docid);
	$content = $modx->db->getRow($rs);

	unset($content['id']); // remove the current id.

	// Once we've grabbed the document object, start doing some modifications
	$newpagetitle = (!empty($newtitle)) ? $newtitle : 'Duplicate of '.$content['pagetitle'];

	if ($_toplevel == 0) {
		$content['pagetitle'] = $newpagetitle;
		$content['alias'] = null;
	} elseif($modx->config['friendly_urls'] == 0 || $modx->config['allow_duplicate_alias'] == 0) {
		$content['alias'] = null;
	}

	// change the parent accordingly
	if ($parent !== null) $content['parent'] = $parent;

	// Change the author
	$content['createdby'] = $userID;
	$content['createdon'] = time();
	// Remove other modification times
	$content['editedby'] = $content['editedon'] = $content['deleted'] = $content['deletedby'] = $content['deletedon'] = 0;

    // Set the published status to unpublished by default (see above ... commit #3388)
    $ispublish = (isset($ispublish)) ? $ispublish : '0';
    $content['published'] = $ispublish;//$content['pub_date'] = 0;

	// Escape the proper strings
	$content['pagetitle'] = $modx->db->escape($content['pagetitle']);
	$content['longtitle'] = $modx->db->escape($content['longtitle']);
	$content['description'] = $modx->db->escape($content['description']);
	$content['introtext'] = $modx->db->escape($content['introtext']);
	$content['content'] = $modx->db->escape($content['content']);
	$content['menutitle'] = $modx->db->escape($content['menutitle']);

	// Duplicate the Document
	$newparent = $modx->db->insert($content, $tblsc);

	// duplicate document's TVs & Keywords
	duplicateKeywords($docid, $newparent);
	duplicateTVs($docid, $newparent);
	duplicateAccess($docid, $newparent);
	
	// invoke OnDocDuplicate event
	$evtOut = $modx->invokeEvent('OnDocDuplicate', array(
		'id' => $docid,
		'new_id' => $newparent
	));

	// Start duplicating all the child documents that aren't deleted.
	$_toplevel++;
	$rs = $modx->db->select('id', $tblsc, 'parent='.$docid.' AND deleted=0', 'id ASC');
	if ($modx->db->getRecordCount($rs)) {
		while ($row = $modx->db->getRow($rs))
			duplicateDocument($row['id'], $newparent, $_toplevel);
	}

	// return the new doc id
	return $newparent;
}

// Duplicate Keywords
function duplicateKeywords($oldid,$newid){
	global $modx, $mysqlVerOk;
	// global $dbase, $table_prefix;

	$tblkw = $modx->getFullTableName('keyword_xref');

	if($mysqlVerOk) {
		$modx->db->insert(
			array('content_id'=>'', 'keyword_id'=>''), $tblkw, // Insert into
			$newid.', keyword_id', $tblkw, 'content_id='.$oldid // Copy from
		);
	} else {
		$ds = $modx->db->select('keyword_id', $tblkw, 'content_id='.$oldid);
		while ($row = $modx->db->getRow($ds))
			$modx->db->insert(array('content_id'=>$newid, 'keyword_id'=>$row['keyword_id']), $tblkw);
	}
}

// Duplicate Document TVs
function duplicateTVs($oldid,$newid){
	global $modx, $mysqlVerOk;
	// global $dbase, $table_prefix;

	$tbltvc = $modx->getFullTableName('site_tmplvar_contentvalues');

	if($mysqlVerOk) {
		$modx->db->insert(
			array('contentid'=>'', 'tmplvarid'=>'', 'value'=>''), $tbltvc, // Insert into
			$newid.', tmplvarid, value', $tbltvc, 'contentid='.$oldid // Copy from
		);
	} else {
		$ds = $modx->db->select('tmplvarid, value', $tbltvc, 'contentid='.$oldid);
		while ($row = $modx->db->getRow($ds))
			$modx->db->insert(array('contentid'=>$newid, 'tmplvarid'=>$row['tmplvarid'], 'value'=>$modx->db->escape($row['value'])), $tbltvc);
	}
}

// Duplicate Document Access Permissions
function duplicateAccess($oldid,$newid){
	global $modx, $mysqlVerOk;
	// global $dbase, $table_prefix;

	$tbldg = $modx->getFullTableName('document_groups');

	if($mysqlVerOk) {
		$modx->db->insert(
			array('document'=>'', 'document_group'=>''), $tbldg, // Insert into
			$newid.', document_group', $tbldg, 'document='.$oldid // Copy from
		);
	} else {
		$ds = $modx->db->select('document_group', $tbldg, 'document='.$oldid);
		while ($row = $modx->db->getRow($ds))
			$modx->db->insert(array('document'=>$newid, 'document_group'=>$row['document_group']), $tbldg);
	}
}
?> 

</div>	
</body>
</html>