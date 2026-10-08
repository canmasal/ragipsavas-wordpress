<?php
/**
 * Yönetim paneli içerik türleri: Eğitimler, Galeri, Başvurular
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * İçerik türlerini kaydet
 */
add_action( 'init', 'ragip_register_post_types' );
function ragip_register_post_types() {

	register_post_type( 'ragip_course', array(
		'labels' => array(
			'name'          => 'Eğitimler',
			'singular_name' => 'Eğitim',
			'add_new_item'  => 'Yeni Eğitim Ekle',
			'edit_item'     => 'Eğitimi Düzenle',
			'all_items'     => 'Tüm Eğitimler',
			'menu_name'     => 'Eğitimler',
		),
		'public'       => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-art',
		'menu_position' => 20,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'rewrite'      => array( 'slug' => 'egitim' ),
		'show_in_rest' => true,
	) );

	register_post_type( 'ragip_gallery', array(
		'labels' => array(
			'name'          => 'Galeri',
			'singular_name' => 'Galeri Görseli',
			'add_new_item'  => 'Yeni Görsel Ekle',
			'edit_item'     => 'Görseli Düzenle',
			'all_items'     => 'Tüm Görseller',
			'menu_name'     => 'Galeri',
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-format-gallery',
		'menu_position' => 21,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'ragip_application', array(
		'labels' => array(
			'name'         => 'Başvurular',
			'singular_name' => 'Başvuru',
			'menu_name'    => 'Başvurular',
			'edit_item'    => 'Başvuruyu Görüntüle',
			'all_items'    => 'Tüm Başvurular',
			'search_items' => 'Başvuru Ara',
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-clipboard',
		'menu_position' => 22,
		'supports'      => array( 'title' ),
		// Başvurular yalnızca ön yüzdeki form ile oluşturulur
		'capabilities'  => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'  => true,
	) );
}

/**
 * Tema etkinleştirildiğinde: kalıcı bağlantıları yenile ve varsayılan eğitimleri ekle (yalnızca bir kez)
 */
add_action( 'after_switch_theme', 'ragip_on_activate' );
function ragip_on_activate() {
	ragip_register_post_types();
	flush_rewrite_rules();

	if ( get_option( 'ragip_seeded' ) ) {
		return;
	}

	$sira = 0;
	foreach ( ragip_default_courses() as $course ) {
		$id = wp_insert_post( array(
			'post_type'   => 'ragip_course',
			'post_title'  => $course['name'],
			'post_name'   => $course['slug'],
			'post_status' => 'publish',
			'menu_order'  => $sira++,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'ragip_letter', $course['letter'] );
		}
	}
	update_option( 'ragip_seeded', 1 );
}

/**
 * Yayımlanmış eğitimleri getir. Hiç eğitim yoksa varsayılan liste kullanılır.
 */
function ragip_courses() {
	$posts = get_posts( array(
		'post_type'      => 'ragip_course',
		'post_status'    => 'publish',
		'numberposts'    => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );

	if ( empty( $posts ) ) {
		return ragip_default_courses();
	}

	$liste = array();
	foreach ( $posts as $post ) {
		$harf = get_post_meta( $post->ID, 'ragip_letter', true );
		if ( '' === $harf ) {
			$harf = mb_strtoupper( mb_substr( $post->post_title, 0, 1 ) );
		}
		$liste[] = array(
			'id'     => $post->ID,
			'name'   => $post->post_title,
			'slug'   => $post->post_name,
			'letter' => $harf,
			'url'    => get_permalink( $post->ID ),
		);
	}
	return $liste;
}

/**
 * Eğitim kartı: görsel adresi (öne çıkan görsel yoksa tema klasöründeki dosya)
 */
function ragip_course_image_html( $course ) {
	if ( ! empty( $course['id'] ) && has_post_thumbnail( $course['id'] ) ) {
		return get_the_post_thumbnail( $course['id'], 'medium_large', array(
			'loading' => 'lazy',
			'alt'     => esc_attr( $course['name'] . ' dersi' ),
		) );
	}
	$dosya = get_theme_file_path( 'assets/images/' . $course['slug'] . '.jpg' );
	if ( file_exists( $dosya ) ) {
		return sprintf( '<img src="%s" alt="%s" loading="lazy">',
			esc_url( get_theme_file_uri( 'assets/images/' . $course['slug'] . '.jpg' ) ),
			esc_attr( $course['name'] . ' dersi' )
		);
	}
	return '';
}

/**
 * Yönetim paneli alanları: eğitim harfi, başvuru detayları ve durum
 */
add_action( 'add_meta_boxes', 'ragip_add_meta_boxes' );
function ragip_add_meta_boxes() {
	add_meta_box( 'ragip_letter_box', 'Görsel Harfi (fotoğraf yoksa)', 'ragip_letter_box_html', 'ragip_course', 'side' );
	add_meta_box( 'ragip_app_details', 'Başvuru Detayları', 'ragip_app_details_html', 'ragip_application', 'normal', 'high' );
	add_meta_box( 'ragip_app_status', 'Başvuru Durumu', 'ragip_app_status_html', 'ragip_application', 'side', 'high' );
}

function ragip_letter_box_html( $post ) {
	wp_nonce_field( 'ragip_meta_save', 'ragip_meta_nonce' );
	$harf = get_post_meta( $post->ID, 'ragip_letter', true );
	echo '<p><input type="text" name="ragip_letter" value="' . esc_attr( $harf ) . '" maxlength="2" style="width:100%"></p>';
	echo '<p class="description">Eğitim fotoğrafı yoksa kartta gösterilen tek harf.</p>';
}

function ragip_app_details_html( $post ) {
	$alanlar = array(
		'ragip_brans'    => 'Branş',
		'ragip_deneyim'  => 'Deneyim',
		'ragip_yas'      => 'Yaş',
		'ragip_veli'     => 'Veli Adı',
		'ragip_telefon'  => 'Telefon',
		'ragip_eposta'   => 'E-posta',
		'ragip_mesaj'    => 'Mesaj',
	);
	echo '<table class="form-table"><tbody>';
	foreach ( $alanlar as $anahtar => $etiket ) {
		$deger = get_post_meta( $post->ID, $anahtar, true );
		echo '<tr><th style="width:160px">' . esc_html( $etiket ) . '</th><td>' . nl2br( esc_html( $deger !== '' ? $deger : '-' ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
}

function ragip_app_status_html( $post ) {
	wp_nonce_field( 'ragip_meta_save', 'ragip_meta_nonce' );
	$durum = get_post_meta( $post->ID, 'ragip_durum', true ) ?: 'yeni';
	echo '<select name="ragip_durum" style="width:100%">';
	foreach ( ragip_application_statuses() as $anahtar => $etiket ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $anahtar ), selected( $durum, $anahtar, false ), esc_html( $etiket ) );
	}
	echo '</select>';
}

/**
 * Başvuru durumları
 */
function ragip_application_statuses() {
	return array(
		'yeni'       => 'Yeni',
		'iletisim'   => 'İletişime geçildi',
		'kayit'      => 'Kayıt tamamlandı',
		'arsiv'      => 'Arşiv',
	);
}

/**
 * Alanları kaydet
 */
add_action( 'save_post', 'ragip_save_meta' );
function ragip_save_meta( $post_id ) {
	if ( ! isset( $_POST['ragip_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ragip_meta_nonce'] ) ), 'ragip_meta_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['ragip_letter'] ) ) {
		update_post_meta( $post_id, 'ragip_letter', mb_substr( sanitize_text_field( wp_unslash( $_POST['ragip_letter'] ) ), 0, 2 ) );
	}
	if ( isset( $_POST['ragip_durum'] ) ) {
		$durum = sanitize_key( wp_unslash( $_POST['ragip_durum'] ) );
		if ( array_key_exists( $durum, ragip_application_statuses() ) ) {
			update_post_meta( $post_id, 'ragip_durum', $durum );
		}
	}
}

/**
 * Başvuru listesi sütunları
 */
add_filter( 'manage_ragip_application_posts_columns', 'ragip_application_columns' );
function ragip_application_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'],
		'title'        => 'Ad Soyad / Branş',
		'ragip_telefon' => 'Telefon',
		'ragip_durum'  => 'Durum',
		'date'         => 'Tarih',
	);
}

add_action( 'manage_ragip_application_posts_custom_column', 'ragip_application_column_value', 10, 2 );
function ragip_application_column_value( $column, $post_id ) {
	if ( 'ragip_telefon' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'ragip_telefon', true ) );
	}
	if ( 'ragip_durum' === $column ) {
		$durum    = get_post_meta( $post_id, 'ragip_durum', true ) ?: 'yeni';
		$etiketler = ragip_application_statuses();
		echo esc_html( isset( $etiketler[ $durum ] ) ? $etiketler[ $durum ] : $durum );
	}
}

/**
 * Yönetim sayfasında başvurular için bekleyen sayı rozeti
 */
add_action( 'admin_menu', 'ragip_pending_bubble' );
function ragip_pending_bubble() {
	global $menu;
	$sayi = count( get_posts( array(
		'post_type'   => 'ragip_application',
		'post_status' => 'private',
		'numberposts' => -1,
		'meta_key'    => 'ragip_durum',
		'meta_value'  => 'yeni',
		'fields'      => 'ids',
	) ) );
	if ( $sayi < 1 || empty( $menu ) ) {
		return;
	}
	foreach ( $menu as $index => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=ragip_application' === $item[2] ) {
			$menu[ $index ][0] .= ' <span class="awaiting-mod">' . absint( $sayi ) . '</span>';
		}
	}
}
