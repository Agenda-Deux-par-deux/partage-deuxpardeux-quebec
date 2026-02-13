<!DOCTYPE html>
<html lang="fr-CA" data-theme="auto">
<head>
	<meta charset="UTF-8">
	<meta itemprop="lang" content="fr_ca">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="description" content="<?php echo esc(DESCRIPTION); ?>">
	<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="Deux par deux">
	<meta property="og:locale" content="fr_CA">
	<meta property="og:url" content="<?php echo esc(URL); ?>">
	<meta property="og:title" content="<?php echo esc(TITLE); ?>">
	<meta property="og:description" content="<?php echo esc(DESCRIPTION); ?>">
	<meta property="og:image" content="<?php echo esc(IMAGE_URL); ?>">
	<meta property="og:image:secure_url" content="<?php echo esc(IMAGE_URL); ?>">
	<meta property="og:image:type" content="image/webp">
	<meta property="og:image:width" content="1200">
	<meta property="og:image:height" content="630">
	<meta property="og:image:alt" content="<?php echo esc(IMAGE_ALT); ?>">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc(TITLE); ?>">
	<meta name="twitter:description" content="<?php echo esc(DESCRIPTION); ?>">
	<meta name="twitter:image" content="<?php echo esc(IMAGE_URL); ?>">
	<meta name="twitter:image:alt" content="<?php echo esc(IMAGE_ALT); ?>">
	<meta name="theme-color" content="<?php echo esc(THEME_COLOR_HEX); ?>">
	<link rel="canonical" href="<?php echo esc(URL); ?>">
	<link rel="icon" type="image/x-icon" href="https://images.deuxpardeux.quebec/favicon.ico">
	<link rel="stylesheet" href="https://images.deuxpardeux.quebec/styles/agenda.core.min.css">
	<title><?php echo TITLE; ?></title>
	<script type="application/ld+json">
<?php echo SCHEMA; ?>

	</script>
</head>
<body>
	<header>
		<a href="/">
			<h1 title="Agenda deux par deux">
				<div>
					<div>deux par deux</div>
					<div>L'agenda de nos événements</div>
				</div>
			</h1>
		</a>
	</header>
	<main>
		<section>
			<div>
				<img src="<?php echo IMAGE_URL; ?>" width="100%">
				<h3><?php echo TITLE; ?></h3>
				<p>
					Où: <?php echo $place; ?><br>
					Quand: <?php echo date("Y-m-d H:i:s", strtotime($data->start->dateTime)); ?>
				</p>
				<p><?php echo $html; ?></p>
			</div>
		</section>
	</main>
	<footer title="Tous droits réservés © Fondation pour la langue française, <?php echo date('Y'); ?>">
		© Tous droits réservés<br>Fondation pour la langue française, <?php echo date('Y'); ?>

	</footer>
</body>
</html>