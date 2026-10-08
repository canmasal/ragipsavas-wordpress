<?php
/**
 * Kurulum: tema etkinleştirildiğinde sayfa ve menüyü otomatik oluşturur (yalnızca bir kez)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Galeri: boşsa, tema içindeki eğitim fotoğraflarını galeriye ekler (yalnızca bir kez)
 */
function ragip_seed_gallery() {
	if ( get_option( 'ragip_gallery_seeded' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$sira = 0;
	foreach ( ragip_default_courses() as $kurs ) {
		$dosya = get_template_directory() . '/assets/images/' . $kurs['slug'] . '.jpg';
		if ( ! file_exists( $dosya ) ) {
			continue;
		}
		$yukle = wp_upload_bits( $kurs['slug'] . '.jpg', null, file_get_contents( $dosya ) );
		if ( ! empty( $yukle['error'] ) ) {
			continue;
		}
		$ek_id = wp_insert_attachment( array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $kurs['name'],
			'post_status'    => 'inherit',
		), $yukle['file'] );
		if ( ! $ek_id || is_wp_error( $ek_id ) ) {
			continue;
		}
		wp_update_attachment_metadata( $ek_id, wp_generate_attachment_metadata( $ek_id, $yukle['file'] ) );

		$gonderi = wp_insert_post( array(
			'post_type'   => 'ragip_gallery',
			'post_title'  => $kurs['name'],
			'post_status' => 'publish',
			'menu_order'  => ++$sira,
		) );
		if ( $gonderi && ! is_wp_error( $gonderi ) ) {
			set_post_thumbnail( $gonderi, $ek_id );
		}
	}
	update_option( 'ragip_gallery_seeded', 1 );
}

add_action( 'after_switch_theme', 'ragip_first_setup', 20 );
function ragip_first_setup() {
	if ( get_option( 'ragip_setup_done' ) ) {
		return;
	}

	// 1. Başvuru Formu sayfası (slug: basvuru)
	$basvuru = get_page_by_path( 'basvuru' );
	if ( ! $basvuru ) {
		$basvuru_id = wp_insert_post( array(
			'post_title'   => 'Başvuru Formu',
			'post_name'    => 'basvuru',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
	} else {
		$basvuru_id = $basvuru->ID;
	}

	// 1b. Randevu sayfası (slug: randevu)
	$randevu = get_page_by_path( 'randevu' );
	if ( ! $randevu ) {
		$randevu_id = wp_insert_post( array(
			'post_title'  => 'Randevu Al',
			'post_name'   => 'randevu',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
	} else {
		$randevu_id = $randevu->ID;
	}

	// 2. Ana menü: bölüm bağlantıları ve başvuru sayfası
	$menu_adi = 'Ana Menü';
	$menu     = wp_get_nav_menu_object( $menu_adi );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_adi );
		if ( ! is_wp_error( $menu_id ) ) {
			$kalemler = array(
				array( 'title' => 'Eğitimler',  'url' => home_url( '/#egitimler' ) ),
				array( 'title' => 'Kurumsal',   'url' => home_url( '/#kurumsal' ) ),
				array( 'title' => 'Programlar', 'url' => home_url( '/#programlar' ) ),
				array( 'title' => 'İletişim',   'url' => home_url( '/#iletisim' ) ),
			);
			foreach ( $kalemler as $sira => $kalem ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $kalem['title'],
					'menu-item-url'       => $kalem['url'],
					'menu-item-status'    => 'publish',
					'menu-item-type'      => 'custom',
					'menu-item-position'  => $sira + 1,
				) );
			}
			if ( $randevu_id && ! is_wp_error( $randevu_id ) ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => 'Randevu Al',
					'menu-item-object-id' => $randevu_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => 5,
				) );
			}
			if ( $basvuru_id && ! is_wp_error( $basvuru_id ) ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => 'Başvuru Yapın',
					'menu-item-object-id' => $basvuru_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => 5,
				) );
			}
		}
		$menu = wp_get_nav_menu_object( $menu_adi );
	}
	if ( $menu ) {
		$konumlar = get_theme_mod( 'nav_menu_locations', array() );
		$konumlar['primary'] = $menu->term_id;
		set_theme_mod( 'nav_menu_locations', $konumlar );
	}

	// 3. Ana sayfa: sayfa olarak ayarla (front-page.php kullanılır)
	$ana = get_page_by_path( 'ana-sayfa' );
	if ( ! $ana ) {
		$ana_id = wp_insert_post( array(
			'post_title'  => 'Ana Sayfa',
			'post_name'   => 'ana-sayfa',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
	} else {
		$ana_id = $ana->ID;
	}
	if ( $ana_id && ! is_wp_error( $ana_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ana_id );
	}

	// 4. Saat dilimi: randevu saatleri Türkiye saatine (UTC+3) göre hesaplanır
	update_option( 'timezone_string', 'Europe/Istanbul' );

	// 5. Kalıcı bağlantılar
	flush_rewrite_rules();

	// 6. Galeri örnekleri
	ragip_seed_gallery();

	// Sayfalar gerçekten oluştuysa kurulumu tamamlandı say (aksi halde bir sonraki temada tekrar dener)
	if ( $basvuru_id && ! is_wp_error( $basvuru_id ) && $randevu_id && ! is_wp_error( $randevu_id ) ) {
		update_option( 'ragip_setup_done', 1 );
	}
}
