<?php

date_default_timezone_set('America/Los_Angeles');

function connectDB(){
	require 'sdf234JLK23fd/config.php';
	$link = mysql_connect('localhost', $db_user, str_rot13($db_password));
	if (!$link) {
	    die('Not connected : ' . mysql_error());
	}
	$db_selected = mysql_select_db($db_name, $link);
	if (!$db_selected) {
	    die ('Can\'t select DB : ' . mysql_error());
	}
}

function check_login($u,$p){
	connectDB();
	$query = sprintf("SELECT `user_id`,`hash` FROM `users` WHERE `username`='%s' LIMIT 1",
		mysql_real_escape_string(strtolower($u)));
	$result = mysql_fetch_assoc(mysql_query($query));
	
	if(check_hash($result['hash'],$p)){
		$now=date('Y-m-d H:i:s');
		mysql_query("UPDATE users SET last_login='$now' where user_id={$result['user_id']}");
		return $result['user_id'];
	}
	return false;
}

function generate_hash($u,$p){
	$salt = hash('sha256', uniqid(mt_rand(), true) . 'caats und doogs' . strtolower($u));
	$hash = $salt . $p; // Prefix the password with the salt
	for ( $i = 0; $i < 100000; $i ++ ){ // Hash the salted password a bunch of times
	    $hash = hash('sha256', $hash);
	}
	$hash = $salt . $hash; // Prefix the hash with the salt so we can find it back later
	return $hash;
}

function check_hash($user_hash,$p){
	$salt = substr($user_hash, 0, 64); // The first 64 characters of the hash is the salt
	$hash = $salt . $p;
	for ( $i = 0; $i < 100000; $i ++ ){	// Hash the password as we did before
	    $hash = hash('sha256', $hash);
	}
	$hash = $salt . $hash;
	if ( $hash == $user_hash ){
	    return true;
	}
}

function survey_table($user_id){
	connectDB();
	$result = mysql_query("SELECT * FROM surveys WHERE created_by=".$user_id);
	if(mysql_num_rows($result)>0){
		echo "<table><tr class=\"head_row\"><td>Survey Name</td><td>Survey ID</td><td>Hits (done)</td><td>Avg. time (min.)</td><td>(1)</td><td>(2)</td><td>(3)</td><td></td></tr>";
		while($row=mysql_fetch_assoc($result)){
			echo "<tr><td>".$row['survey_name']."</td>";
			echo '<td><a title="Link to Qualtrics Survey" href="'.$row['survey_link'].'" target="_blank">'.$row['survey_id']."</td>";
			$stat=mysql_fetch_assoc(mysql_query("SELECT COUNT(status) as total, SUM(status='done') as done, 
				avg(timestampdiff(MINUTE,time_created,date_returned)) as average 
				FROM `hits` 
				WHERE survey_id='".$row['survey_id']."'"));
			$stat['done']=(!is_numeric($stat['done']))?0:$stat['done'];
			$stat['average']=(!is_numeric($stat['average']))?'n/a':round($stat['average'],1);
			echo "<td>".$stat['total']." (".$stat['done'].")</td>";
			echo "<td>".$stat['average']." min (".(($row['min_time']==0)?'off':($row['min_time'])).")</td>";
			echo "<td><a class=\"open_html\" id=\"".$row['survey_id']."\" title=\"HTML for MTurk\">HTML</a></td>";
			echo "<td><a class=\"open_link\" id=\"".$row['survey_id']."\" title=\"Link for 'Redirect to a URL' in Qualtrics Survey Options\">LINK</a></td>";
			echo "<td><a href=\"output.php?survey_id=".$row['survey_id']."\" title=\"QualTurk records\" target=\"_blank\" onclick=\"var w=window.open(this.href,this.target,'width=1170,height=450,scrollbars=1'); return w?false:true\">CSV</a></td>";
			$delete_button='';
			if($stat['total']==0){
				$delete_button = '<a href="?del='.md5($row['survey_id']."bla919").'"><img src="delete.png" alt="Delete" title="Delete Survey from QualTurk (available for zero HITs only)" /></a>';
			}
			echo "<td>".$delete_button."</td></tr>";
		}
		echo "</table>";
	}else{
		echo "<strong>You currently have no surveys in the system.</strong>";
	}
}

function getGUID(){
	mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	$charid = strtoupper(md5(uniqid(rand(), true)));
	$uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4).substr($charid,16, 4).substr($charid,20,12);
	return $uuid;
}

?>