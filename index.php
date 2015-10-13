<?
session_start();
include 'functions.php';

$in_session=0;
$logout=0;
$incorrect=0;
$added=0;

$inactive = 600; // set timeout period in seconds

if(isset($_SESSION['timeout']) ) {
	$session_life = time() - $_SESSION['timeout'];
	if($session_life > $inactive){ 
		session_destroy(); 
		header("Location: ?logout=2"); 
	}
}
$_SESSION['timeout'] = time();


if(isset($_GET['logout']) and $_GET['logout']){
	session_unset();
	session_destroy(); 
	$in_session=0;
	$logout=1;
	if($_GET['logout']==2){
		$logout=2;
	}
	if($_GET['logout']==3){//password reset
		$logout=3;
	}
	if($_GET['logout']==4){//new account
		$logout=4;
	}
}
if(isset($_POST['login'])){
	$login_check = check_login($_POST['username'],$_POST['pwd']);
	if(!empty($_POST['username']) and !empty($_POST['pwd']) and is_numeric($login_check)){
		$_SESSION['username']=$_POST['username'];
		$_SESSION['user_id']=$login_check;
		$in_session=1;
	}else{
		$in_session=0;
		$incorrect=1;
	}
}
if(isset($_SESSION['username']) and !empty($_SESSION['username'])){
	$in_session=1;
}
if(isset($_POST['add']) and $_POST['add']=="Add Survey"){
	if(!empty($_POST['survey_name']) and !empty($_POST['survey_id']) and !empty($_POST['survey_link']) and strlen($_POST['min_time'])!=0){
		if(strstr($_POST['survey_link'],$_POST['survey_id'])){
			if(is_numeric($_POST['min_time'])){
				connectDB();
				$check_q=sprintf("SELECT survey_id FROM surveys WHERE survey_id='%s'", mysql_real_escape_string($_POST['survey_id']));
				$check=mysql_query($check_q);
				if(mysql_num_rows($check)==0){
					if(mysql_real_escape_string($_POST['debrief'])==" Debrief Text (optional, displayed with completion code)"){
						$_POST['debrief']='';
					}
					$query = sprintf("INSERT INTO surveys(survey_name,survey_id,survey_link,min_time,debrief,created_by) VALUES('%s','%s','%s','%s','%s',%s)",
						mysql_real_escape_string($_POST['survey_name']),
						mysql_real_escape_string($_POST['survey_id']),
						mysql_real_escape_string($_POST['survey_link']),
						mysql_real_escape_string($_POST['min_time']),
						mysql_real_escape_string($_POST['debrief']),
						$_SESSION['user_id']);
					mysql_query($query);
					$added=1;
				}else{
					$added=4;
				}
			}else{
				$added=5;
			}
		}else{
			$added=2;
		}
	}else{
		$added=3;
	}
}
if(isset($_GET['del']) and !empty($_GET['del'])){
	connectDB();
	$delq=sprintf("DELETE FROM surveys WHERE md5(concat(survey_id,'bla919'))='%s'",
		mysql_real_escape_string($_GET['del']));
	$del=mysql_query($delq);
	$deleted=1;
	if(mysql_affected_rows()>0){
		$deleted=2;
	}
}
?>
<html>
<head>
	<title>QualTurk</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="description" content="QualTurk helps reduce low-quality survey data by filtering participants based on pre- and post-screening questions. The system is designed to support Amazon's Mechanical Turk in conjunction with Qualtrics."/>
	<meta name="keywords" content="Mechanical Turk, Qualtrics, Survey Tricks, Survey Tipps, survey research, Mturk, quality survey data, get quality suvey data, mturk identify bad workers"/>
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="stylesheet" href="frontend.css" type="text/css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
	<script>
		$(document).ready(function(){
			$("input").not(".subm").blur(function(){
				if($(this).val()==""){
					$(this).val($(this).prev("label").text());
				}
			});
			$("input").not(".subm").click(function(){
				if($(this).val()==$(this).prev("label").text()){
					$(this).val("");
				}
			});
			$('a.open_link').click(function(){
				var qid=$(this).attr('id');
			    var linkWindow =  window.open('','Link Window','width=600,height=300');
			    var html = 'http://www.qualturk.com/survey.php?l=c&surveyid='+qid+'&partid=${e://Field/workerid}&long=${q://QIDxxx/ChoiceGroup/SelectedChoices}&comm=${q://QIDyyy/ChoiceTextEntryValue}';
			    linkWindow.document.open();
			    linkWindow.document.write(html);
			    linkWindow.document.close();
			    return false;
			});
			$('a.open_html').click(function(){
				var qid=$(this).attr('id');
			    var htmlWindow =  window.open('','HTML Window','width=1150,height=600');
			    var html = '<textarea style="height:100%;width:100%">'+$("#overlay").html().replace('A123A',qid)+'</textarea>';
			    htmlWindow.document.open();
			    htmlWindow.document.write(html);
			    htmlWindow.document.close();
			    return false;
			});
		});
	</script>
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-32828578-1']);
	  _gaq.push(['_trackPageview']);
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
</head>
<body>

<? if(!$in_session){ ?>
<div id="top_bar">
	<h1 onclick="location.href='http://www.qualturk.com/'">QualTurk</h1>
	<span><a href="signup.php" target="_blank" onclick="var w=window.open(this.href,this.target,'width=650,height=550,scrollbars=0'); return w?false:true">Create Account</a></span>
</div>
<div id="wrapper">
	<div id="instructions">
		<h2>About QualTurk</h2>
		<p>QualTurk aims to increase the quality of survey data collected from Amazon Mechanical Turk (MTurk) by flagging respondents who fail quality checks. The system is designed to support Mechanical Turk in conjunction with Qualtrics.<br /><br />
			<b>How it works</b> <br /><br />
			MTurk workers are automatically registered by this system and forwarded to your Qualtrics survey. The quality checks include i. survey completion above a minimum time threshold (optional), ii. correctly answering an arbitrary set of pre-screening questions (optional), iii. correctly answering two specific questions that test if respondents read instructions (required). The system also ensures that workers cannot repeat the same HIT. The system provides workers with a completion code and optional debrief text. The code enables them to complete the HIT.<br /><br />
			<b>What you get</b><br /><br />
			 QualTurk provides data on which quality check each worker failed, if any (csv file). All workers receive a completion code (except repeat takers who are not eligible), which is also visible on MTurk. The code itself reflects if there was any quality issue and which one. Based on this information, you can decide who to pay on MTurk. Your Qualtrics survey will include responses from lower quality workers, but they can be filtered out by cross-checking with the QualTurk dataset on who passed which quality checks.
			</p>
	</div>
	<div id="login_form">
		<h3>Login</h3>
		<? if($logout==1){ ?>
			<div class="success">You have successfully logged out.</div>
		<? }elseif($logout==2){ ?>
			<div class="error">Your session expired.</div>
		<? }elseif($logout==3){ ?>
			<div class="success">Password successfully reset.</div>
		<? }elseif($logout==4){ ?>
			<div class="success">Your account is now activated.</div>
		<? }elseif($incorrect){ ?>
			<div class="error">Incorrect login details. <a href="forgot_password.php" target="_blank" onclick="var w=window.open(this.href,this.target,'width=650,height=550,scrollbars=0'); return w?false:true">Forgot password?</a></div>
		<? } ?>
		<form action="/" method="post">
			<label for="user"> user</label><input type="text" name="username" value=" user" /><br />
			<label for="pwd"> password</label><input type="password" name="pwd" value=" password" /><br />
			<input type="submit" name="login" class="subm" value="Login" />
		</form>
	</div>
</div>
<!--
<div id="footer">
	<span>support@qualturk.com</span>
</div>
-->
<? } ?>

<? if($in_session){ ?>
<div id="top_bar">
	<h1 onclick="location.href='http://www.qualturk.com/'">QualTurk</h1>
	<span><?=ucfirst(strtolower($_SESSION['username']))?> <a href="reset_password.php" target="_blank" onclick="var w=window.open(this.href,this.target,'width=650,height=550,scrollbars=0'); return w?false:true">Settings</a> <a href="?logout=1">Logout</a></span>
</div>
<div id="wrapper">
	<div id="instructions">
		<h2>About QualTurk</h2>
		<p>QualTurk aims to increase the quality of survey data collected from Amazon Mechanical Turk (MTurk) by flagging respondents who fail quality checks. The system is designed to support Mechanical Turk in conjunction with Qualtrics.<br /><br />
			<b>How it works</b> <br /><br />
			MTurk workers are automatically registered by this system and forwarded to your Qualtrics survey. The quality checks include i. survey completion above a minimum time threshold (optional), ii. correctly answering an arbitrary set of pre-screening questions (optional), iii. correctly answering two specific questions that test if respondents read instructions (required). The system also ensures that workers cannot repeat the same HIT. The system provides workers with a completion code and optional debrief text. The code enables them to complete the HIT.<br /><br />
			<b>What you get</b><br /><br />
			 QualTurk provides data on which quality check each worker failed, if any (csv file). All workers receive a completion code (except repeat takers who are not eligible), which is also visible on MTurk. The code itself reflects if there was any quality issue and which one. Based on this information, you can decide who to pay on MTurk. Your Qualtrics survey will include responses from lower quality workers, but they can be filtered out by cross-checking with the QualTurk dataset on who passed which quality checks.
			</p>
		<h2>Instructions</h2>
		<ol id="steps">
			<li><p>Add your (launched) Qualtrics survey to your QualTurk account using the form below.</p></li>
			<li><p>In MTurk, copy/paste the survey-specific HTML code (1) into the 'Design Layout' box while in 'Edit HTML Source' mode. Switch back to normal viewing mode and change the study time and study description accordingly.</p></li>
			<li><p>In Qualtrics, add your own pre-screening block with skip logic at the beginning of the study
				 (optional, <a href="block_pre.jpg" target="_blank" onclick="var w=window.open(this.href,this.target,'width=1170,height=600,scrollbars=1'); return w?false:true">example</a>).
				Add this <em>exact</em> post-screening block at the end of the study (<a href="block_post.jpg" target="_blank" onclick="var w=window.open(this.href,this.target,'width=1170,height=600,scrollbars=1'); return w?false:true">picture</a>, <a href="block_post_text.txt" target="_blank" onclick="var w=window.open(this.href,this.target,'width=1000,height=350,scrollbars=1'); return w?false:true">text</a>) and enable Force Response on both.</p></li>
			<li><p>In Qualtrics, open 'Survey Options', in 'Survey Termination' select 'Redirect to a URL' and copy/paste the link (2) that was generated for your survey. Replace QIDxxx, QIDyyy with the post-screening question IDs (xxx for 'How long...?'; e.g., QID42) that can be retrieved using the 'Piped Text...' menu. (Important: Make sure to use the Piped Text menu, because internal Qualtics QIDs are different to those displayed next to the question.)</p></li>
			<li><p>In Qualtrics, open 'Survey Flow', click 'Add Below' and select 'Embedded Data'. Type "workerid" in the yellow box and move the box to the top.</p></li>
			<li><p>Release your HITs on MTurk and check the QualTurk records (3). Reject HITs that failed screening based on the completion code (if needed) and filter out low-quality responses in Qualtrics.</p></li>
		</ol>
		<h2>Guide to Quality Checks</h2>
		<p>If a worker passes all quality checks, their status in (3) will be 'done' and their completion code will be a long alphanumeric string.<br />
		   If a worker fails the post-screening, their status in (3) will be 'end_problem' and their completion code will start with "POST".<br />
		   If a worker fails the optional pre-screening, their status in (3) will be 'pre_problem' and their completion code will start with "PRE".<br />
		   If a worker fails the optional time screening, their status in (3) will be 'time_problem' and their completion code will start with "TIME".<br />
		   If a worker attempts to retake the HIT, their status in (3) will be 'duplicate' and they receive no completion code. In this case workers tend not to complete the HIT and are not listed on MTurk, but only QualTurk.
		</p>
	</div>
	<div id="item_table">
		<h2>Your Surveys</h2>
		<? 
		if($deleted==1){
			echo '<div class="error">Survey could not be deleted</div>';
		}elseif($deleted==2){
			echo '<div class="success">Survey deleted successfully</div>';
		}
		survey_table($_SESSION['user_id']);
		?>
	</div>
	<div id="add_survey">
		<h2>Add Survey</h2>
		<? if($added==1){ ?>
			<div class="success">Survey added successfully</div>
		<? }elseif($added==2){ ?>
			<div class="error">Problem with Survey ID and Survey Link. The ID should be part of the link.</div>
		<? }elseif($added==3){ ?>
			<div class="error">Survey Name, ID, Link and min. HIT completion time are mandatory fields.</div>
		<? }elseif($added==4){ ?>
			<div class="error">Cannot add survey: duplicate Survey ID</div>
		<? }elseif($added==5){ ?>
			<div class="error">Min. HIT completion time must be numeric: 0 for off, e.g. 12 for 12min.</div>
		<? } ?>
		<form action="/" method="post" name="add_form">
			<label for="survey_name"> Survey Name (for your reference)</label><input type="text" name="survey_name" value="<?=(isset($_POST['survey_name']))?$_POST['survey_name']:" Survey Name (for your reference)"?>" /><br />
			<label for="survey_id"> Qualtrics Survey ID (e.g. SV_bC5L8txgtzoVRNb)</label><input type="text" name="survey_id" value="<?=(isset($_POST['survey_id']))?$_POST['survey_id']:" Qualtrics Survey ID (e.g. SV_bC5L8txgtzoVRNb)"?>" /><br />
			<label for="survey_link"> Qualtrics Survey Link (with https://)</label><input type="text" name="survey_link" value="<?=(isset($_POST['survey_link']))?$_POST['survey_link']:" Qualtrics Survey Link (with https://)"?>" /><br />
			<label for="min_time"> Minimum HIT completion time (in minutes, 0=off)</label><input type="text" name="min_time" value="<?=(isset($_POST['min_time']))?$_POST['min_time']:" Minimum HIT completion time (in minutes, 0=off)"?>" /><br />
			<label for="debrief"> Debrief Text (optional, displayed with completion code)</label><textarea name="debrief"><?=(isset($_POST['debrief']))?$_POST['debrief']:" Debrief Text (optional, displayed with completion code)"?></textarea><br />
			<input type="submit" name="add" class="subm" value="Add Survey" />			
		</form>
	</div>
</div>
<div id="footer">
	<span>support@qualturk.com</span>
</div>


<div id="overlay">
	<p>
	<style type="text/css"><!-- .body {  font-size: 10pt;  font-family: arial, sans-serif; width: 600px; }  --> </style> 
	<script type="text/javascript">
		function loader() { 
			if(urlParam("workerId")==0) { 
				alert("You must first accept the HIT."); 
			} else { 
				var url='http://www.qualturk.com/survey.php?l=c&surveyid=A123A&workerid=' + urlParam("workerId");
				window.open(url,"mywindow"); 
			} 
		} 
		function urlParam(name) {     
			var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(document.location.href);
	     	if (!results) {
		         return 0;     
			}     
			return results[1] || 0; 
		} 	 
	</script>
	</p> 
	<div class="body"><img src="http://www.qualturk.com/check.png" alt="" style="float: right; padding:5px;" /> 
		<h1><font color="#333399">Study Announcement</font></h1>
			We are researching [THE TOPIC]. This HIT is part of a research project at [THE INSTITUTION].<br /> 
		<h3>Confidentiality</h3> 
			<p>No personally identifiable information will be stored after the study. You will remain completely anonymous.</p>
			<h3>Requirements</h3>
				<ul><li>Complete a survey</li></ul>
			<h3>Eligibility</h3>
				<ol><li style="text-align: left;">You have about <b>12345678</b><b> minutes</b> of time right now</li>
					<li style="text-align: left;">You must not have completed this HIT before</li> </ol>
				<div style="text-align: center;"><input type="button" onClick='loader();' value="Start" name="start" style="height: 30px; width: 150px;" /></div>
	</div> 
	<div class="body" style="text-align: center;">&nbsp;</div>
	<div class="body"><hr /> 
		<h2>Task Confirmation</h2> 
			<p>After completing the initial survey today you will receive a code to enter below:</p>
			<p><input type="text" id="confirmationCode" size="50" name="completioncode" /></p>
			<p>Thank you very much for your participation!</p>
	</div> <p>&nbsp;</p>
</div>
<? } ?>
</body>
</html>








