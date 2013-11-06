<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>jqCron Demos</title>
		<link rel="stylesheet" type="text/css" href="reset.css" />
		<style type="text/css">
			h1 {
				font-weight: bold;
				font-size: 2em;
				margin: 1em 0;
			}

			body {
				background: #eee;
			}

			#container {
				width: 1000px;
				margin: 0 auto;
			}

			#menu {
				display: block;
			}

			#menu li {
				display: inline-block;
				margin: 0 1px;
				padding: 0.4em;
				border-left: 1px solid #888;
				border-top: 1px solid #888;
				border-right: 1px solid #888;
				vertical-align: bottom;
			}
			#menu li:nth-child(1) {
				margin-left: 0;
			}

			#menu li.active {
				padding: 0.5em;
				background: #fff;
			}

			#menu a {
				color: #333;
				text-decoration: none;
			}
			#menu a:hover {
				color: black;
				text-decoration: underline;
			}

			#demo {
				width: 100%;
				border: 1px solid #888;
				height: 400px;
			}


		</style>
	</head>
	<body>
		<div id="container">
			<h1>jqCron Demos</h1>
			<ul id="menu">
				<?php
				$files = glob(dirname(__FILE__) . '/demo_*.php');

				foreach ( $files as $file ) {
					$demo = basename($file, '.php');
					echo '<li><a href="' . $demo . '.php">' . str_replace('_', ' ', $demo) . '</a></li>';
				}
				?>
			</ul>
			<iframe id="demo"></iframe>
		</div>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
		<script type="text/javascript">
			$(function() {
				$('#menu a').click(function(e) {
					$('#menu li').removeClass('active');
					$(this).parent().addClass('active');
					$('#demo').attr('src', $(this).attr('href'));
					e.preventDefault();
					return false;
				}).eq(0).trigger('click');
			});
		</script>
	</body>
</html>