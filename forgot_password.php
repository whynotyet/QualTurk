<?
session_start();

include 'functions.php';
connectDB();

if(isset($_POST['forgot']) and !empty($_POST['forgot'])){
	if((!empty($_POST['username']) and $_POST['username']!=" username") or (!empty($_POST['email']) and $_POST['email']!=" email")){
		$query = sprintf("SELECT `full_name`,`email`,`user_id`,`username` FROM `users` WHERE `username`='%s' OR `email`='%s' LIMIT 1",
			mysql_real_escape_string(strtolower($_POST['username'])),
			mysql_real_escape_string(strtolower($_POST['email'])));
		$r = mysql_query($query);
		$result = mysql_fetch_assoc($r);
		if(mysql_num_rows($r)==1){
			$key=md5($result['user_id'].$result['full_name']);
			$msg="Someone has asked to reset the password for the following site and username.\n\nhttp://www.QualTurk.com\n\nUsername: ".$result['username']."\n\nTo reset your password visit the following address, otherwise just ignore this email and nothing will happen.\n\nhttp://www.qualturk.com/forgot_password.php?k=".$key;
			mail($result['email'], 'Reset your QualTurk password', $msg, 'From: support@qualturk.com');
			$reset=2;
		}else{
			$reset=3;
		}
	}else{
		$reset=1;
	}
}

if(isset($_GET['k']) and !empty($_GET['k'])){
	$query=sprintf("SELECT `username` FROM `users` WHERE md5(CONCAT(`user_id`,`full_name`))='%s'",
		mysql_real_escape_string($_GET['k']));
	$r = mysql_query($query);
	$result = mysql_fetch_assoc($r);
	if(mysql_num_rows($r)==1){
		$_SESSION['username']=$result['username'];
		$reset=4;
	}else{
		$reset=5;
	}
}

if(isset($_POST['reset']) and !empty($_POST['reset'])){
	if($_POST['pwd1']===$_POST['pwd2']){
		$hash=generate_hash($_SESSION['username'],$_POST['pwd2']);
		mysql_query("UPDATE `users` SET `hash`='$hash' WHERE username='{$_SESSION['username']}'");
		header('Location: http://www.qualturk.com/?logout=3');
	}else{
		$reset=7;
	}
}
?>

<html>
<head>
	<title>QualTurk</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="stylesheet" href="frontend.css" type="text/css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
	<script>
		$(document).ready(function(){
		<? if($reset<4){ ?>
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

		<? }elseif($reset==4){ ?>
			$(".h").hide();
			$("input").not(".subm").blur(function(){
				if($(this).val()==""){
					$(this).hide();
					$(this).prev('input').show();
				}
			});
			$("input").not(".subm").focus(function(){
				if(!$(this).hasClass("h")){
					$(this).hide();
					$(this).next('input').show();
					$(this).next('input').focus();
				}
			});
		<? } ?>
		});
	</script>
</head>
<body>

<div id="login_form">
	<? if($reset<4){ ?>
		<h3>Forgot Password?</h3>
		<? if($reset==1){ ?>
			<div class="error">Enter your username or email</div>
		<? }elseif($reset==2){ ?>
			<div class="success">Check your email and follow the link</div>
		<? }elseif($reset==3){ ?>
			<div class="error">Username or email don't exist</div>
		<? } ?>
		<form action="" method="post" id="res">
			<label for="username"> username</label><input type="text" name="username" value=" username" /><br />
			<span id="or"> - or -</span>
			<label for="email"> email</label><input type="text" name="email" value=" email" /><br />
			<input type="submit" name="forgot" class="subm" value="Send new password" />
		</form>
	<? }elseif($reset==4){ ?>
		<h3>Reset Password</h3>
		<? if($reset==6){ ?>
			<div class="success">Password reset successfully</div>
		<? }elseif($reset==7){ ?>
			<div class="error">New passwords don't match</div>
		<? } ?>
		<form action="" method="post" id="pwd">
			<label for="pwd1"> new password</label><input type="text" name="pwd0t" value=" new password" /><input class="h" type="password" name="pwd1" value="" /><br />
			<label for="pwd2"> confirm new password</label><input type="text" name="pwd0t" value=" confirm new password" /><input class="h" type="password" name="pwd2" value="" /><br />
			<input type="submit" name="reset" class="subm" value="Reset" />
		</form>
	<? }elseif($reset==4){ ?>
		<div class="error">Error - invalid link</div>
	<? } ?>	
</div>

</body>
</html>








