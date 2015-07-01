<?php
	include_once dirname(__FILE__) . "/config.php";

	function getSyntaxName($method,$str) {
		$vals = array(
				"none" => "None",
				"nohighlight" => "None",
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
	</script>
	<script src="js/highlightjs/highlight.pack.js"></script>
</head>

<body>
	<div class="wrapper">
		<?php if(isset($_GET['id'])) { 
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
			<div class="code-box lined <?php echo $highlighting; ?>"><?php echo bzdecompress(file_get_contents("$dir/$foundFile")); ?></div><br/>
			<div class="file-info" unselectable="on">
				<span class="caption" unselectable="on"><strong>Date submitted</strong><span class="datetime"><?php echo $dateSubmitted; ?></span></span>
				<span class="caption" unselectable="on"><strong>Paste type</strong><?php echo getSyntaxName("full",$highlighting); ?></span>
				<span class="caption" unselectable="on"><strong>File size</strong><?php echo "$fileSize KB"; ?></span>
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
			<script>
				$('.code-box').each(function(i, block) {
					hljs.highlightBlock(block);
				});
				
				var timestamp = parseInt($(".datetime").text());
				var date = new Date(timestamp*1000);
				var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
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
				$(".datetime").text(months[date.getMonth()] + " " + ordinal_suffix_of(date.getDate()) + ", " + date.getFullYear() + ", " + pad(date.getHours(),2) + ":" + pad(date.getMinutes(),2) + ":" + pad(date.getSeconds(),2));

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
				<textarea class="input-box lined" name="contents"></textarea><br/>
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
			</form>
		<?php } ?>
	</div>
</body>

</html>