<?php
	session_start();

	$root = dirname(__FILE__);
	include "$root/config.php";

	if(isset($_SESSION['timesubmitted'])) {
		if(time() - $_SESSION['timesubmitted'] < 5) {
			http_response_code(403);
			die("You are submitting pastes too fast.");
		}
	}

	if(!isset($_POST['contents'])) {
		http_response_code(403);
		die("You tried to submit a blank file.");
	}
	$contents = htmlentities($_POST['contents']);
	// cloudflare fix
	$contents = str_replace("\t","&#9;",$contents);

	if(!isset($_POST['highlighting'])) {
		http_response_code(403);
		die("No highlighting method was specified.");
	}

	function check_valid_highlight($str) {
		if(!isset($str)) {
			return "none";
		}
		$str = addslashes($str);

		$valid = array(
			"none", "apache", "sh", "cs", "cpp", "css", "cson", "diff", "html",
			"http", "ini", "json", "jsp", "js", "mk", "md",
			"nginx", "objc", "php", "pl", "py", "rb", "sql",
			"hs", "lua", "less", "vala", "vb", "vbs", "cmake"
		);

		if(!in_array($str,$valid)) {
			return "none";
		} else {
			return $str;
		}
	}

	$paste_id = base_convert(time(),10,36) . substr(sha1(uniqid()),0,4);
	$highlighting = check_valid_highlight(htmlentities($_POST['highlighting']));

	$file = "$dir/$paste_id-$highlighting.txt.bz2";

	if(!file_exists($dir)) {
		mkdir($dir,0755,true);
	}
	file_put_contents($file,bzcompress($contents,9));

	$_SESSION['timesubmitted'] = time();

	header("Location: $root_url/?id=$paste_id");