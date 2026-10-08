<?php
/**
 * WordPress genel ayarları ve SEO temel etiketleri.
 * Ayarlar tema etkinleştirildiğinde ve sürüm değiştiğinde bir kez uygulanır.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RAGIP_SETTINGS_VERSION', 2 );

add_action( 'after_switch_theme', 'ragip_apply_site_settings', 30 );
add_action( 'init', 'ragip_apply_site_settings_if_needed', 30 );

function ragip_apply_site_settings_if_needed() {
	if ( (int) get_option( 'ragip_settings_version' ) < RAGIP_SETTINGS_VERSION ) {
		ragip_apply_site_settings();
	}
}

function ragip_apply_site_settings() {
	// Genel
	update_option( 'blogname', 'Ragıp Savaş Sanat Akademisi' );
	update_option( 'blogdescription', 'Müzik, dans, bale, resim ve sanat eğitimleri · Göktürk, İstanbul' );
	update_option( 'timezone_string', 'Europe/Istanbul' );
	update_option( 'date_format', 'd.m.Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 ); // Pazartesi

	// Okuma: arama motorları siteyi görsün, ana sayfa sabit olsun
	update_option( 'blog_public', 1 );
	update_option( 'posts_per_page', 12 );

	// Tartışma: yorumlar ve pingbacks kapalı (akademi sitesinde yorum gerekmez)
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'default_pingback_flag', 0 );
	update_option( 'comment_registration', 0 );
	update_option( 'comments_notify', 0 );

	// WordPress'in örnek içerikleri (ilk kurulumdan gelen) kaldırılır
	$ornek_yazi = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $ornek_yazi ) {
		wp_delete_post( $ornek_yazi->ID, true );
	}
	$ornek_sayfa = get_page_by_path( 'sample-page' );
	if ( $ornek_sayfa ) {
		wp_delete_post( $ornek_sayfa->ID, true );
	}

	update_option( 'ragip_settings_version', RAGIP_SETTINGS_VERSION );
}

/**
 * Arama motorları ve paylaşımlar için meta açıklama ve Open Graph etiketleri
 */
add_action( 'wp_head', 'ragip_meta_tags', 1 );
function ragip_meta_tags() {
	if ( is_front_page() ) {
		$baslik = get_bloginfo( 'name' );
		$aciklama = function_exists( 'ragip_text' ) ? ragip_text( 'ragip_hero_text' ) : get_bloginfo( 'description' );
	} elseif ( is_singular() ) {
		$baslik = get_the_title();
		$aciklama = wp_strip_all_tags( get_the_excerpt() ?: get_bloginfo( 'description' ) );
	} else {
		$baslik = get_bloginfo( 'name' );
		$aciklama = get_bloginfo( 'description' );
	}
	$aciklama = wp_trim_words( $aciklama, 30, '…' );
	$url = is_singular() ? get_permalink() : home_url( '/' );
	?>
	<meta name="description" content="<?php echo esc_attr( $aciklama ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $baslik ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $aciklama ); ?>">
	<meta property="og:type" content="<?php echo is_singular() && ! is_front_page() ? 'article' : 'website'; ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:locale" content="tr_TR">
	<meta name="twitter:card" content="summary_large_image">
	<?php
}
