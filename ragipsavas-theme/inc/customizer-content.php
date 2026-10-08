<?php
/**
 * Ana sayfa metinleri: Görünüm > Özelleştir > Ana Sayfa İçeriği
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Varsayılan metinler ve alan tanımları
 */
function ragip_content_fields() {
	return array(
		'ragip_hero_eyebrow' => array( 'label' => 'Üst başlık (hero)', 'default' => 'Göktürk · İstanbul', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_hero_title'   => array( 'label' => 'Ana başlık (hero)', 'default' => 'Sanatla büyüyen bir yaşam', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_hero_text'    => array( 'label' => 'Hero açıklama metni', 'default' => "2007 yılından bu yana Göktürk'te çocuk ve yetişkinlere klasik bale, müzik, resim, dans, tiyatro ve daha birçok alanda nitelikli sanat eğitimi veriyoruz.", 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field' ),
		'ragip_about_title'  => array( 'label' => 'Kurumsal başlık', 'default' => 'Sanatın gücüne inanan bir akademi', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_about_text'   => array( 'label' => 'Kurumsal metin 1', 'default' => "Ragıp Savaş Sanat Akademisi, Telekom Sokak Sinasos İş Merkezi'nde faaliyet göstermektedir. Amacımız; her yaştan bireyin yeteneğini keşfetmesine, disiplin ve estetik bilinç kazanmasına destek olmaktır.", 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field' ),
		'ragip_about_text2'  => array( 'label' => 'Kurumsal metin 2', 'default' => 'Deneyimli eğitmen kadromuz ve uluslararası müfredat iş birliklerimizle, öğrencilerimizi hem sahnede hem hayatta güçlendirmeyi hedefliyoruz.', 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field' ),
		'ragip_courses_title' => array( 'label' => 'Eğitimler başlığı', 'default' => 'Size uygun alanı seçin', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_gallery_title' => array( 'label' => 'Galeri başlığı', 'default' => 'Öğrencilerimizden kareler', 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'ragip_gallery_text'  => array( 'label' => 'Galeri açıklaması', 'default' => 'Dersler, performanslar ve etkinliklerimizden seçilmiş fotoğraflar.', 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field' ),
	);
}

/**
 * Özelleştirme alanlarını kaydet
 */
add_action( 'customize_register', 'ragip_content_customizer' );
function ragip_content_customizer( $wp_customize ) {
	$wp_customize->add_section( 'ragip_content', array(
		'title'    => 'Ana Sayfa İçeriği',
		'priority' => 31,
	) );

	foreach ( ragip_content_fields() as $id => $field ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $field['default'],
			'sanitize_callback' => $field['sanitize'],
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $field['label'],
			'section' => 'ragip_content',
			'type'    => $field['type'],
		) );
	}
}

/**
 * Metin getir (özelleştirme değeri yoksa varsayılan)
 */
function ragip_text( $key ) {
	$alanlar = ragip_content_fields();
	$varsayilan = isset( $alanlar[ $key ] ) ? $alanlar[ $key ]['default'] : '';
	return get_theme_mod( $key, $varsayilan );
}
