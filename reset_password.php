<?
session_start();
if(!isset($_SESSION['username']) or !isset($_SESSION['user_id'])){
	header('Location: http://www.qualturk.com/?logout=1');
	die("ERROR");
}

include 'functions.php';

if(isset($_POST['reset']) and !empty($_POST['reset'])){
	if($_POST['pwd1']===$_POST['pwd2']){
		if(check_login($_SESSION['username'],$_POST['pwd0'])){
			$hash=generate_hash($_SESSION['username'],$_POST['pwd2']);
			connectDB();
			mysql_query("UPDATE `users` SET `hash`='$hash' WHERE username='{$_SESSION['username']}'");
			$reset=1;
		}else{
			$reset=2;
		}
	}else{
		$reset=3;
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
			
		});
	</script>
</head>
<body>
<div id="wrapper">
	<div id="login_form">
		<h3>Reset Your QualTurk Password</h3>
		<? if($reset==1){ ?>
			<div class="success">Password reset successfully</div>
		<? }elseif($reset==2){ ?>
			<div class="error">Incorrect current password</div>
		<? }elseif($reset==3){ ?>
			<div class="error">New passwords don't match</div>
		<? } ?>
		<form action="" method="post">
			<label for="pwd1"> current password</label><input type="text" name="pwd0t" value=" current password" /><input class="h" type="password" name="pwd0" value="" /><br />
			<label for="pwd1"> new password</label><input type="text" name="pwd0t" value=" new password" /><input class="h" type="password" name="pwd1" value="" /><br />
			<label for="pwd2"> confirm new password</label><input type="text" name="pwd0t" value=" confirm new password" /><input class="h" type="password" name="pwd2" value="" /><br />
			<input type="submit" name="reset" class="subm" value="Reset" />
		</form>
	</div>
</div>
</body>
</html>








