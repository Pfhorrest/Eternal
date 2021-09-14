<!DOCTYPE html>

<html lang="en">
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<title>Eternal</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="Eternal is a free scenario for Aleph One, the multi-platform, open-source, first-person-shooter engine derived from Bungie's Marathon engine, which continues the story of the Marathon trilogy." />
		<?php
			$cssfilename = "/styles/css/styles.css" ;
			$csspath = $_SERVER['DOCUMENT_ROOT'] .  $cssfilename ;
			if (file_exists($csspath)) {
				$cssdate = filemtime ($csspath) ;
			}
			echo '<link rel="stylesheet" href="' . $cssfilename . '?v=' . $cssdate . '" />' ;

			$jsfilename = "/scripts.js" ;
			$jspath = $_SERVER['DOCUMENT_ROOT'] . $jsfilename ;
			if (file_exists($jspath)) {
				$jsdate = filemtime ($jspath) ;
			}
			echo '<script src="' . $jsfilename. '?v=' . $jsdate . '" defer="true" /></script>' ;
		?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="icon" type="image/png" href="/favicon-196.png" />
	</head>
	<body>
		<header>
			<h1><span>Eternal</span></h1>
		</header>
		<nav id="main-nav">
			<ul>
				<li><a href="/">About</a></li>
				<li><a href="/story">Story</a></li>
				<li><a href="/screenshots">Screenshots</a></li>
				<li><a href="/development">Development</a></li>
			</ul>
		</nav>

		<main>
			<article>