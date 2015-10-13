<?
session_start();

include 'functions.php';
connectDB();

if(isset($_POST['add']) and !empty($_POST['add'])){
	if(!empty($_POST['username']) and $_POST['username']!=" username" and !empty($_POST['firstname']) and $_POST['firstname']!=" first name" and !empty($_POST['lastname']) and $_POST['lastname']!=" last name"){
		if(ctype_alnum($_POST['username'])){
			if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
				if(strlen($_POST['pwd1'])>5){
					if($_POST['pwd1']===$_POST['pwd2']){
						$query = sprintf("SELECT `user_id` FROM `users` WHERE `username`='%s' LIMIT 1",
							mysql_real_escape_string(strtolower($_POST['username'])));
						if(mysql_num_rows(mysql_query($query))==0){
							$_POST['institution']=($_POST['institution']==" institution (optional)")?'':$_POST['institution'];
							$hash = generate_hash($_POST['username'],$_POST['pwd1']);
							$query = sprintf("INSERT INTO `users` (`full_name`,`institution`,`email`,`username`,`hash`) VALUES('%s','%s','%s','%s','%s')",
								mysql_real_escape_string($_POST['firstname'].' '.$_POST['lastname']),
								mysql_real_escape_string($_POST['institution']),
								mysql_real_escape_string($_POST['email']),
								mysql_real_escape_string(strtolower($_POST['username'])),
								$hash);
							if(mysql_query($query)){
								$key=md5(strtolower($_POST['username']).$_POST['firstname'].' '.$_POST['lastname']);
								$msg="Someone has asked to create an account for the following site and username.\n\nhttp://www.QualTurk.com\n\nUsername: ".$_POST['username']."\n\nTo activate the account visit the following address, otherwise just ignore this email and nothing will happen.\n\nhttp://www.qualturk.com/signup.php?k=".$key;
								mail($_POST['email'], 'Activate QualTurk account', $msg, 'From: support@qualturk.com');
								unset($_POST);
								$add=1;
							}else{
								$add=2;
							}
						}else{
							$add=3;
						}
					}else{
						$add=4;
					}
				}else{
					$add=5;
				}
			}else{
				$add=6;
			}
		}else{
			$add=7;
		}
	}else{
		$add=8;
	}
}

if(isset($_GET['k']) and !empty($_GET['k'])){
	$query=sprintf("UPDATE `users` SET `active`=1 WHERE md5(CONCAT(`username`,`full_name`))='%s'",
		mysql_real_escape_string($_GET['k']));
	mysql_query($query);
	if(mysql_affected_rows()==1){
		header('Location: http://www.qualturk.com/?logout=4');
		die('done');
	}else{
		$add=9;
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
			$("input").not(".subm").blur(function(){
				if($(this).val()==""){
					$(this).val($(this).prev("label").text());
				}
			});
			$("input").not(".subm").focus(function(){
				if($(this).val()==$(this).prev("label").text()){
					$(this).val("");
				}
			});

			$(".h").hide();
			$(".spe").not(".subm").blur(function(){
				if($(this).val()==""){
					$(this).hide();
					$(this).prev('input').show();
				}
			});
			$(".spe").not(".subm").focus(function(){
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
	<div id="login_form" class="no_bar">
			<h3>Create QualTurk Account</h3>
			<? if($add==1){ ?>
				<div class="success">Check your email and follow the link</div>
			<? }elseif($add==2){ ?>
				<div class="error">Error - try again</div>
			<? }elseif($add==3){ ?>
				<div class="error">Username already taken</div>
			<? }elseif($add==4){ ?>
				<div class="error">Passwords do not match</div>
			<? }elseif($add==5){ ?>
				<div class="error">Password too short (min. 6 char)</div>
			<? }elseif($add==6){ ?>
				<div class="error">Invalid email format</div>
			<? }elseif($add==7){ ?>
				<div class="error">Username needs to be alphanumeric</div>	
			<? }elseif($add==8){ ?>
				<div class="error">All non-optional fields are required</div>			
			<? }elseif($add==9){ ?>
				<div class="error">Invalid activation link</div>			
			<? } ?>
			<form action="" method="post">
				<label for="firstname"> first name</label><input type="text" name="firstname" value="<?=(isset($_POST['firstname']))?$_POST['firstname']:" first name"?>" /><br />
				<label for="lastname"> last name</label><input type="text" name="lastname" value="<?=(isset($_POST['lastname']))?$_POST['lastname']:" last name"?>" /><br />
				<label for="institution"> institution (optional)</label><input type="text" name="institution" value="<?=(isset($_POST['institution']))?$_POST['institution']:" institution (optional)"?>" /><br />
				<label for="email"> email</label><input type="text" name="email" value="<?=(isset($_POST['email']))?$_POST['email']:" email"?>" /><br />
				<label for="username"> username</label><input type="text" name="username" value="<?=(isset($_POST['username']))?$_POST['username']:" username"?>" /><br />
				<label for="pwd1"> password (min. 6 characters)</label><input class="spe" type="text" name="pwd0t" value=" new password (min. 6 characters)" /><input class="h spe" type="password" name="pwd1" value="" /><br />
				<label for="pwd2"> confirm password</label><input class="spe" type="text" name="pwd0t" value=" confirm new password" /><input class="h spe" type="password" name="pwd2" value="" /><br />
				<input type="submit" name="add" class="subm" value="Create Account" />
			</form>
	</div>
</div>
</body>
</html>








