<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="container nav">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" aria-label="<?php esc_attr_e( 'Ana sayfa', 'ragipsavas' ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="https://www.ragipsavassanat.com/assets/frontend/images/logo.png" alt="<?php esc_attr_e( 'Ragıp Savaş Sanat Akademisi logosu', 'ragipsavas' ); ?>">
			<?php endif; ?>
			<div class="brand-text">
				<strong>Ragıp Savaş</strong>
				<span>Sanat Akademisi</span>
			</div>
		</a>

		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_id'        => 'nav',
			'menu_class'     => 'nav-links',
			'fallback_cb'    => 'ragip_fallback_menu',
		) );
		?>

		<a href="<?php echo esc_url( ragip_application_url() ); ?>" target="_blank" rel="noopener" class="btn btn-primary nav-cta"><?php esc_html_e( 'Başvuru Yapın', 'ragipsavas' ); ?></a>
		<button class="menu-toggle" id="menuToggle" aria-label="<?php esc_attr_e( 'Menüyü aç/kapat', 'ragipsavas' ); ?>" aria-expanded="false">
			<span></span><span></span><span></span>
		</button>
	</div>
</header>

<?php
/**
 * Menü tanımlı değilse varsayılan bağlantılar
 */
function ragip_fallback_menu() {
	echo '<ul class="nav-links" id="nav">';
	echo '<li><a href="#egitimler">Eğitimler</a></li>';
	echo '<li><a href="#kurumsal">Kurumsal</a></li>';
	echo '<li><a href="#programlar">Programlar</a></li>';
	echo '<li><a href="#iletisim">İletişim</a></li>';
	echo '</ul>';
}
?>
