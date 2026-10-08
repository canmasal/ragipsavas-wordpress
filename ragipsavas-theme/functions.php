<?php
/**
 * Ragıp Savaş Sanat Akademisi teması
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RAGIP_VERSION', '1.0.0' );

/**
 * Tema desteği ve menüler
 */
function ragip_setup() {
	load_theme_textdomain( 'ragipsavas', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 220, 'flex-height' => true, 'flex-width' => true ) );
	register_nav_menus( array(
		'primary' => __( 'Ana Menü', 'ragipsavas' ),
	) );
}
add_action( 'after_setup_theme', 'ragip_setup' );

/**
 * Stil ve betik yükleme
 */
function ragip_assets() {
	wp_enqueue_style( 'ragip-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap', array(), null );
	wp_enqueue_style( 'ragip-style', get_template_directory_uri() . '/assets/css/style.css', array( 'ragip-fonts' ), RAGIP_VERSION );
	wp_enqueue_script( 'ragip-main', get_template_directory_uri() . '/assets/js/main.js', array(), RAGIP_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ragip_assets' );

/**
 * Özelleştirme paneli ayarları (Görünüm > Özelleştir > Akademi Bilgileri)
 */
function ragip_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'ragip_contact', array(
		'title'    => __( 'Akademi Bilgileri', 'ragipsavas' ),
		'priority' => 30,
	) );

	$fields = array(
		'ragip_address' => array( 'label' => 'Adres', 'default' => "Telekom Sokak, Sinasos İş Merkezi\nKat: 2, Göktürk / İstanbul", 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field' ),
		'ragip_phone'   => array( 'label' => 'Telefon (görünen)', 'default' => '+90 (212) 322 90 90', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_phone_link' => array( 'label' => 'Telefon (bağlantı, örn. +902123229090)', 'default' => '+902123229090', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_whatsapp' => array( 'label' => 'WhatsApp numarası (ülke koduyla, örn. 905xxxxxxxxx)', 'default' => '905413229090', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_email'   => array( 'label' => 'E-posta', 'default' => 'istanbul@ragipsavassanat.com', 'type' => 'email', 'sanitize' => 'sanitize_email' ),
		'ragip_lat'     => array( 'label' => 'Harita enlem (latitude)', 'default' => '41.177000', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_lng'     => array( 'label' => 'Harita boylam (longitude)', 'default' => '28.887333', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_instagram' => array( 'label' => 'Instagram adresi', 'default' => 'https://www.instagram.com/ragipsavassanat', 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
		'ragip_facebook'  => array( 'label' => 'Facebook adresi', 'default' => 'https://www.facebook.com/ragipsavassanat', 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
		'ragip_youtube'   => array( 'label' => 'YouTube adresi', 'default' => 'https://www.youtube.com/channel/UCnusPwkdW6g_EfuYZLn271w', 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $field['default'],
			'sanitize_callback' => $field['sanitize'],
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $field['label'],
			'section' => 'ragip_contact',
			'type'    => $field['type'],
		) );
	}
}
add_action( 'customize_register', 'ragip_customize_register' );

/**
 * Özelleştirme değerini getir
 */
function ragip_opt( $key ) {
	$defaults = array(
		'ragip_address'    => "Telekom Sokak, Sinasos İş Merkezi\nKat: 2, Göktürk / İstanbul",
		'ragip_phone'      => '+90 (212) 322 90 90',
		'ragip_phone_link' => '+902123229090',
		'ragip_whatsapp'   => '905413229090',
		'ragip_email'      => 'istanbul@ragipsavassanat.com',
		'ragip_lat'        => '41.177000',
		'ragip_lng'        => '28.887333',
		'ragip_instagram'  => 'https://www.instagram.com/ragipsavassanat',
		'ragip_facebook'   => 'https://www.facebook.com/ragipsavassanat',
		'ragip_youtube'    => 'https://www.youtube.com/channel/UCnusPwkdW6g_EfuYZLn271w',
	);
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Eğitim listesi (fotoğraflar assets/images/ klasöründen okunur)
 */
function ragip_default_courses() {
	return array(
		array( 'name' => 'Klasik Bale', 'slug' => 'klasik-bale', 'letter' => 'B' ),
		array( 'name' => 'Müzikal Tiyatro', 'slug' => 'muzikal-tiyatro', 'letter' => 'M' ),
		array( 'name' => 'Piyano', 'slug' => 'piyano', 'letter' => 'P' ),
		array( 'name' => 'Resim', 'slug' => 'resim', 'letter' => 'R' ),
		array( 'name' => 'Yaratıcı Drama', 'slug' => 'yaratici-drama', 'letter' => 'Y' ),
		array( 'name' => 'Dans', 'slug' => 'dans', 'letter' => 'D' ),
		array( 'name' => 'Bateri', 'slug' => 'bateri', 'letter' => 'B' ),
		array( 'name' => 'Gitar', 'slug' => 'gitar', 'letter' => 'G' ),
		array( 'name' => 'Jimnastik', 'slug' => 'jimnastik', 'letter' => 'J' ),
		array( 'name' => 'Keman', 'slug' => 'keman', 'letter' => 'K' ),
		array( 'name' => 'Şan', 'slug' => 'san', 'letter' => 'Ş' ),
		array( 'name' => 'Seramik', 'slug' => 'seramik', 'letter' => 'S' ),
		array( 'name' => 'Mix Art', 'slug' => 'mix-art', 'letter' => 'M' ),
		array( 'name' => 'Aikido', 'slug' => 'aikido', 'letter' => 'A' ),
		array( 'name' => 'Yan Flüt', 'slug' => 'yan-flut', 'letter' => 'F' ),
	);
}

/**
 * Başvuru formu modülü
 */
require_once get_template_directory() . '/inc/application.php';

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/customizer-content.php';

require_once get_template_directory() . '/inc/setup.php';

require_once get_template_directory() . '/inc/appointments.php';
require_once get_template_directory() . '/inc/admin-panel.php';

/**
 * WhatsApp bağlantısı: numara boşsa null döner (düğme gösterilmez)
 */
function ragip_whatsapp_url() {
	$numara = preg_replace( '/\D/', '', (string) ragip_opt( 'ragip_whatsapp' ) );
	if ( strlen( $numara ) < 10 ) {
		return null;
	}
	return 'https://wa.me/' . $numara . '?text=' . rawurlencode( 'Merhaba, bilgi almak istiyorum.' );
}
