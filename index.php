<?php
	include_once dirname(__FILE__) . "/config.php";

	function getSyntaxName($method,$str) {
		if($str == "nohighlight") {
			$str = "none";
		}
		$vals = array(
				"none" => "None",
				"apache" => "Apache",
				"sh" => "Bash",
				"cs" => "C#",
				"cpp" => "C++",
				"css" => "CSS",
				"cson" => "CoffeeScript",
				"diff" => "Diff",
				"html" => "HTML/XML",
				"http" => "HTTP",
				"ini" => "Ini",
				"json" => "JSON",
				"jsp" => "Java",
				"js" => "JavaScript",
				"mk" => "Makefile",
				"md" => "Markdown",
				"nginx" => "Nginx",
				"objc" => "Objective C",
				"php" => "PHP",
				"pl" => "Perl",
				"py" => "Python",
				"rb" => "Ruby",
				"sql" => "SQL",
				"hs" => "Haskell",
				"lua" => "Lua",
				"less" => "Less",
				"vala" => "Vala",
				"vb" => "VB .NET",
				"vbs" => "VBScript",
				"cmake" => "CMake"
			);
		switch($method) {
			case "all":
				return $vals;
			case "class":
				return array_search($str,$vals);
			default:
			case "full":
				return $vals[$str];
				break;
		}
	}

	if(isset($_GET['raw'])) {
		header("Content-type: text/plain");
		global $dir;

		$textarea_contents = "";

		if(isset($_GET['id'])) { 
			$fileID = substr(htmlspecialchars($_GET['id']),0,10);
			if(!ctype_alnum($fileID)) {
				echo "Invalid ID.";
				break;
			}

			$dirIter = new DirectoryIterator($dir);
			$foundFile = NULL;
			foreach ($dirIter as $fileInfo) {
				if(substr($fileInfo->getBasename(),0,10) == $fileID) {
					$foundFile = $fileInfo->getBasename();
					break;
				}
			}
			
			if(!$foundFile) {
				$textarea_contents = "File not found.";
			} else {
				$textarea_contents = bzdecompress(file_get_contents("$dir/$foundFile"));
			}
		}

		die(html_entity_decode($textarea_contents));
	}
?>

<html>

<head>
	<title>Paste a file</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css"/>
	<link rel="stylesheet" type="text/css" href="css/main.css"/>
	<link rel="stylesheet" type="text/css" href="js/highlightjs/styles/default.css" class="highlight-style"/>

	<script src="js/jquery.js"></script>
	<script src="js/jquery-linedtextarea.js"></script>
	<script>
		$(document).ready(function(){
			$(".lined").linedtextarea();
			$(".datetime").each(function(){
				var timestamp = parseInt($(this).text());
				$(this).text(formatTime(timestamp));
			});
			$(".post").on("click",function(){
				var post_id = $(this).children(".title").text();
				window.location.href = "?id=" + post_id;
			});
		});
		$(document).delegate('textarea', 'keydown', function(e) {
			var keyCode = e.keyCode || e.which;

			if (keyCode == 9) {
				e.preventDefault();
				var start = this.selectionStart;
				var end = this.selectionEnd;

				// set textarea value to: text before caret + tab + text after caret
				$(this).val($(this).val().substring(0, start)
							+ "\t"
							+ $(this).val().substring(end));

				// put caret at right position again
				this.selectionStart =
				this.selectionEnd = start + 1;
			}
		});

		function getCookie(cname) {
			var name = cname + "=";
			var ca = document.cookie.split(';');
			for(var i=0; i<ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0)==' ') c = c.substring(1);
				if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
			}
			return "";
		}
		function setCookie(cname, cvalue, exdays) {
			var d = new Date();
			d.setTime(d.getTime() + (exdays*24*60*60*1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = cname + "=" + cvalue + "; " + expires;
		}

		function ordinal_suffix_of(i) {
			var j = i % 10,
				k = i % 100;
			if (j == 1 && k != 11) {
				return i + "st";
			}
			if (j == 2 && k != 12) {
				return i + "nd";
			}
			if (j == 3 && k != 13) {
				return i + "rd";
			}
			return i + "th";
		}
		function pad(n, width, z) {
			z = z || '0';
			n = n + '';
			return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
		}

		function formatTime(timestamp) {
			var date = new Date(timestamp*1000);
			var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

			return months[date.getMonth()] + " " + ordinal_suffix_of(date.getDate()) + ", " + date.getFullYear() + ", " + pad(date.getHours(),2) + ":" + pad(date.getMinutes(),2) + ":" + pad(date.getSeconds(),2)
		}
	</script>
	<script src="js/highlightjs/highlight.pack.js"></script>
</head>

<body>
	<div class="wrapper">
		<div class="sidebar">
			<div class="buttons">
				<?php
					if(isset($_GET['id'])) { 
						$fileID = substr(htmlspecialchars($_GET['id']),0,10);
						?> <a href="<?php echo "$root_url/?edit=$fileID"; ?>"><span class="button edit_button">Edit Paste</span></a> <?php
					}
				?>
				<a href="<?php echo "$root_url"; ?>"><span class="button">New Paste</span></a>
			</div>
			<div class="container">
				<h1>Recent posts</h1>
				<?php
					$recent_posts = explode("+",urlencode($_COOKIE['posts']));
					arsort($recent_posts);
					foreach($recent_posts as $post) {
						if(!ctype_alnum(str_replace("-","",$post))) {
							continue;
						}
						$time_posted = base_convert(substr($post,0,6),36,10);
						$highlighting = substr($post,11);
						if($highlighting == "" || $highlighting == NULL) {
							$highlighting = "none";
						}
						?>
						<div class="post">
							<span class="title"><?php echo substr($post,0,10); ?></span><br/>
							<span class="datetime"><?php echo $time_posted; ?></span><br/>
							<span class="type"><?php echo getSyntaxName("full",$highlighting); ?></span><br/>
						</div>
						<?php
					}
				?>
			</div>
		</div>
		<?php
			if(isset($_GET['id'])) { 
			//$filename = "$root/pastes/" . htmlspecialchars($_GET['id']) . ".txt";
			$fileID = substr(htmlspecialchars($_GET['id']),0,10);
			if(!ctype_alnum($fileID)) {
				die("Invalid ID.");
			}

			$dirIter = new DirectoryIterator($dir);
			$foundFile = NULL;
			foreach ($dirIter as $fileInfo) {
				if(substr($fileInfo->getBasename(),0,10) == $fileID) {
					$foundFile = $fileInfo->getBasename();
					$highlighting = substr($fileInfo->getBasename('.txt.bz2'),stripos($foundFile,"-")+1);
					if($highlighting == "none") {
						$highlighting = "nohighlight";
					}
					$fileSize = number_format($fileInfo->getSize()/1024,2);
					break;
				}
			}
			
			if(!$foundFile) {
				die("File not found.");
			}

			// using JS to get the user's correct timezone
			$dateSubmitted = base_convert(substr($fileID,0,6),36,10);
			// $dateSubmitted = date('F jS, Y, H:i:s',base_convert(substr($fileID,0,6),36,10));
		?>
			<div class="code-box lined <?php echo $highlighting; ?>"><?php echo bzdecompress(file_get_contents("$dir/$foundFile")); ?></div>
			<div class="bottom-stuff">
				<div class="file-info" unselectable="on">
					<span class="caption" unselectable="on"><strong>Date submitted</strong><span class="datetime"><?php echo $dateSubmitted; ?></span></span>
					<span class="caption" unselectable="on"><strong>Paste type</strong><?php echo getSyntaxName("full",$highlighting); ?></span>
					<span class="caption" unselectable="on"><strong>File size</strong><?php echo "$fileSize KB"; ?></span>
				</div>
			</div>
			<select class="themes">
				<?php
					$dirIter = new DirectoryIterator("$root/js/highlightjs/styles");
					$themes_arr = [];
					foreach ($dirIter as $fileInfo) {
						if(!$fileInfo->isDot()) {
							$themes_arr[] = $fileInfo->getBasename(".css");
						}
					}
					asort($themes_arr);
					foreach ($themes_arr as $theme_css) {
						echo "<option value=\"" . $theme_css . "\">" . $theme_css . "</option>";
					}
				?>
			</select>
			<?php
				if($highlighting == "md") {
					include_once "$root/includes/parsedown.php";
					include_once "$root/includes/parsedown-extra.php";
					$parsedown = new ParsedownExtra();
					$parsedown->setMarkupEscaped(true);
			?>
					<div class="markdown-preview">
						<link rel="stylesheet" type="text/css" href="css/markdown-light.css" class="md-css"/>
						<link rel="stylesheet" type="text/css" href="css/markdown.css"/>
						<h1 class="mdh">Markdown Preview</h1>
						<div class="css-switcher">
							<a href="css/markdown.css" style="float: left; margin-right: 16px; color: #fff;" class="css-link">CSS</a>
							<div class="css-switch" id="light"></div>
							<div class="css-switch" id="dark"></div>
						</div>
						<div class="markdown-content">
							<?php echo $parsedown->text(html_entity_decode(bzdecompress(file_get_contents("$dir/$foundFile")))); ?>
						</div>
					</div>
					<script>
						$(document).ready(function(){
							$(".css-switch").on("click",function(){
								switch($(this).attr("id")) {
									case "dark":
										$(".md-css").attr("href","css/markdown-dark.css");
										$(".css-link").attr("href","css/markdown-dark.css");
										break;

									default:
									case "light":
										$(".md-css").attr("href","css/markdown-light.css");
										$(".css-link").attr("href","css/markdown-light.css");
										break;
								}
							});
						});
					</script>
			<?php
				}
			?>
			<script>
				$('.code-box').each(function(i, block) {
					hljs.highlightBlock(block);
				});

				$('.themes').on("change",function(){
					$('.highlight-style').attr("href","js/highlightjs/styles/" + $(this).val() + ".css");
					setCookie("theme",$(this).val(),365);
				});

				var theme = getCookie("theme");
				if(theme == "") {
					theme = "default";
				}
				$('.themes').val(theme);
				$('.highlight-style').attr("href","js/highlightjs/styles/" + theme + ".css");
			</script>
		<?php } else { ?>
			<form action="post.php" method="POST">
				<?php
					$textarea_contents = "";
					if(isset($_GET['edit'])) {
						$fileID = substr(htmlspecialchars($_GET['edit']),0,10);
						if(!ctype_alnum($fileID)) {
							echo "Invalid ID.";
							break;
						}

						$dirIter = new DirectoryIterator($dir);
						$foundFile = NULL;
						foreach ($dirIter as $fileInfo) {
							if(substr($fileInfo->getBasename(),0,10) == $fileID) {
								$foundFile = $fileInfo->getBasename();
								break;
							}
						}
						
						if(!$foundFile) {
							$textarea_contents = "File not found.";
						} else {
							$textarea_contents = bzdecompress(file_get_contents("$dir/$foundFile"));
						}
					}
				?>
				<textarea class="input-box lined" name="contents"><?php echo $textarea_contents; ?></textarea>
				<div class="bottom-stuff">
					<select name="highlighting">
						<?php
							$vals = getSyntaxName("all");
							foreach($vals as $value => $name) {
								?>
									<option value="<?php echo $value; ?>"><?php echo $name; ?></option>
								<?php
							}
						?>
					</select>
					<input type="submit" value="Submit">
				</div>
			</form>
		<?php } ?>
	</div>
</body>

</html>
