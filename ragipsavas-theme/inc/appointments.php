<?php
/**
 * Randevu sistemi: 15 dk aralıkla başlangıç, her randevu en fazla 30 dk, çakışma yok.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RAGIP_SLOT_STEP', 15 ); // dakika: başlangıç saatleri arası
define( 'RAGIP_SLOT_LEN', 30 );  // dakika: her randevunun süresi (en fazla)

/**
 * Metni WordPress saat diliminde (Türkiye, UTC+3) Unix zamanına çevirir.
 * WordPress PHP'nin varsayılan saat dilimini UTC tuttuğu için strtotime() kullanılmaz.
 */
function ragip_local_ts( $metin ) {
	try {
		return ( new DateTime( $metin, wp_timezone() ) )->getTimestamp();
	} catch ( Exception $e ) {
		return 0;
	}
}

/**
 * Çalışma saatleri (gün numarasına göre, 0 = Pazar)
 */
function ragip_working_hours( $ymd ) {
	$saatler = array(
		0 => array( '09:30', '15:00' ), // Pazar
		1 => array( '12:00', '21:00' ), // Pazartesi
		2 => array( '12:00', '21:00' ), // Salı
		3 => array( '12:00', '21:00' ), // Çarşamba
		4 => array( '11:00', '21:00' ), // Perşembe
		5 => array( '10:00', '21:00' ), // Cuma
		6 => array( '08:30', '20:00' ), // Cumartesi
	);
	$gun = (int) wp_date( 'w', ragip_local_ts( $ymd . ' 12:00:00' ) );
	return $saatler[ $gun ];
}

/**
 * Yönetim panelinden kapatılan saatler (her satır: 08.10.2026 11:00)
 * Bir kapalı saat, 30 dakikalık bir randevu gibi davranır.
 */
function ragip_blocked_starts() {
	$metin = (string) get_theme_mod( 'ragip_blocked', "08.10.2026 11:00" );
	$liste = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $metin ) as $satir ) {
		$satir = trim( $satir );
		if ( preg_match( '/^(\d{2})\.(\d{2})\.(\d{4})\s+(\d{2}:\d{2})$/', $satir, $m ) ) {
			$ts = ragip_local_ts( "{$m[3]}-{$m[2]}-{$m[1]} {$m[4]}:00" );
			if ( $ts ) {
				$liste[] = $ts;
			}
		}
	}
	return $liste;
}

/**
 * Bir günün tüm olası başlangıç saatleri
 */
function ragip_day_slots( $ymd ) {
	$saat = ragip_working_hours( $ymd );
	$bas  = ragip_local_ts( "$ymd {$saat[0]}:00" );
	$son  = ragip_local_ts( "$ymd {$saat[1]}:00" );
	$liste = array();
	for ( $t = $bas; $t + RAGIP_SLOT_LEN * 60 <= $son; $t += RAGIP_SLOT_STEP * 60 ) {
		$liste[] = $t;
	}
	return $liste;
}

/**
 * Bu gün için dolu aralıklar: randevular + kapalı saatler
 */
function ragip_busy_ranges( $ymd ) {
	$aralik = array();
	foreach ( ragip_blocked_starts() as $ts ) {
		if ( wp_date( 'Y-m-d', $ts ) === $ymd ) {
			$aralik[] = array( $ts, $ts + RAGIP_SLOT_LEN * 60 );
		}
	}
	// Ziyaretçi olarak özel (private) kayıtlar WP_Query ile görünmez; bu yüzden doğrudan veritabanından okunur.
	global $wpdb;
	$idler = $wpdb->get_col( $wpdb->prepare(
		"SELECT pm.post_id FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = 'ragip_day' AND pm.meta_value = %s
		 AND p.post_type = 'ragip_appointment' AND p.post_status IN ('private','publish')",
		$ymd
	) );
	foreach ( $idler as $id ) {
		if ( get_post_meta( $id, 'ragip_durum', true ) === 'iptal' ) {
			continue;
		}
		$ts = (int) get_post_meta( $id, 'ragip_start', true );
		if ( $ts ) {
			$aralik[] = array( $ts, $ts + RAGIP_SLOT_LEN * 60 );
		}
	}
	return $aralik;
}

/**
 * Başlangıç saati müsait mi? (çakışma ve geçmiş saat kontrolü)
 */
function ragip_slot_is_free( $ts, $ymd ) {
	if ( $ts <= time() ) {
		return false;
	}
	if ( ! in_array( $ts, ragip_day_slots( $ymd ), true ) ) {
		return false;
	}
	$bitis = $ts + RAGIP_SLOT_LEN * 60;
	foreach ( ragip_busy_ranges( $ymd ) as $aralik ) {
		if ( $ts < $aralik[1] && $aralik[0] < $bitis ) {
			return false;
		}
	}
	return true;
}

/**
 * Özelleştirme: kapalı saatler
 */
add_action( 'customize_register', 'ragip_appointments_customizer' );
function ragip_appointments_customizer( $wp_customize ) {
	$wp_customize->add_section( 'ragip_appointments', array(
		'title'    => 'Randevu Ayarları',
		'priority' => 32,
	) );
	$wp_customize->add_setting( 'ragip_blocked', array(
		'default'           => "08.10.2026 11:00",
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'ragip_blocked', array(
		'label'       => 'Kapalı / dolu saatler (her satıra: gg.aa.yyyy ss:dd)',
		'description' => 'Bu saatler randevu listesinde dolu görünür. Örnek: 08.10.2026 11:00',
		'section'     => 'ragip_appointments',
		'type'        => 'textarea',
	) );
}

/**
 * Randevu içerik türü
 */
add_action( 'init', 'ragip_register_appointments' );
function ragip_register_appointments() {
	register_post_type( 'ragip_appointment', array(
		'labels' => array(
			'name'          => 'Randevular',
			'singular_name' => 'Randevu',
			'menu_name'     => 'Randevular',
			'edit_item'     => 'Randevuyu Görüntüle',
			'all_items'     => 'Tüm Randevular',
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-calendar-alt',
		'menu_position' => 23,
		'supports'      => array( 'title' ),
		'capabilities'  => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'  => true,
	) );
}

add_action( 'add_meta_boxes', 'ragip_appointment_metabox' );
function ragip_appointment_metabox() {
	add_meta_box( 'ragip_appointment_box', 'Randevu Detayları', 'ragip_appointment_box_html', 'ragip_appointment', 'normal', 'high' );
	add_meta_box( 'ragip_appointment_status', 'Randevu Durumu', 'ragip_appointment_status_html', 'ragip_appointment', 'side', 'high' );
}

function ragip_appointment_box_html( $post ) {
	$ts = (int) get_post_meta( $post->ID, 'ragip_start', true );
	$alanlar = array(
		'Tarih ve saat' => $ts ? wp_date( 'd.m.Y H:i', $ts ) : '-',
		'Süre'          => RAGIP_SLOT_LEN . ' dakika',
		'Ad Soyad'      => get_post_meta( $post->ID, 'ragip_ad', true ),
		'Telefon'       => get_post_meta( $post->ID, 'ragip_telefon', true ),
		'E-posta'       => get_post_meta( $post->ID, 'ragip_eposta', true ),
		'Branş'         => get_post_meta( $post->ID, 'ragip_brans', true ),
		'Not'           => get_post_meta( $post->ID, 'ragip_not', true ),
	);
	echo '<table class="form-table"><tbody>';
	foreach ( $alanlar as $etiket => $deger ) {
		echo '<tr><th style="width:160px">' . esc_html( $etiket ) . '</th><td>' . nl2br( esc_html( $deger !== '' ? $deger : '-' ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
}

function ragip_appointment_status_html( $post ) {
	wp_nonce_field( 'ragip_appointment_save', 'ragip_appointment_nonce' );
	$durum = get_post_meta( $post->ID, 'ragip_durum', true ) ?: 'aktif';
	$secenekler = array( 'aktif' => 'Aktif', 'tamamlandi' => 'Tamamlandı', 'iptal' => 'İptal' );
	echo '<select name="ragip_durum" style="width:100%">';
	foreach ( $secenekler as $anahtar => $etiket ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $anahtar ), selected( $durum, $anahtar, false ), esc_html( $etiket ) );
	}
	echo '</select>';
}

add_action( 'save_post_ragip_appointment', 'ragip_appointment_save_status' );
function ragip_appointment_save_status( $post_id ) {
	if ( ! isset( $_POST['ragip_appointment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ragip_appointment_nonce'] ) ), 'ragip_appointment_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['ragip_durum'] ) ) {
		$durum = sanitize_key( wp_unslash( $_POST['ragip_durum'] ) );
		if ( in_array( $durum, array( 'aktif', 'tamamlandi', 'iptal' ), true ) ) {
			update_post_meta( $post_id, 'ragip_durum', $durum );
		}
	}
}

/**
 * Randevu sayfası adresi
 */
function ragip_appointment_url() {
	$sayfa = get_page_by_path( 'randevu' );
	return $sayfa ? get_permalink( $sayfa ) : home_url( '/randevu/' );
}

/**
 * Form gönderimi
 */
add_action( 'admin_post_nopriv_ragip_appointment', 'ragip_handle_appointment' );
add_action( 'admin_post_ragip_appointment', 'ragip_handle_appointment' );
function ragip_handle_appointment() {
	$gun_url = ragip_appointment_url();

	$nonce = isset( $_POST['ragip_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ragip_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'ragip_appointment' ) ) {
		wp_safe_redirect( add_query_arg( 'randevu', 'hata', $gun_url ) );
		exit;
	}

	// Spam koruması
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'randevu', 'ok', $gun_url ) );
		exit;
	}

	$ts      = absint( $_POST['slot'] ?? 0 );
	$ad      = sanitize_text_field( wp_unslash( $_POST['ad_soyad'] ?? '' ) );
	$telefon = sanitize_text_field( wp_unslash( $_POST['telefon'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['eposta'] ?? '' ) );
	$brans   = sanitize_text_field( wp_unslash( $_POST['brans'] ?? '' ) );
	$not     = sanitize_textarea_field( wp_unslash( $_POST['not'] ?? '' ) );

	$brans_listesi = wp_list_pluck( ragip_courses(), 'name' );
	$gecerli = $ts && $ad !== '' && $telefon !== '' && is_email( $email ) && in_array( $brans, $brans_listesi, true );
	if ( ! $gecerli ) {
		wp_safe_redirect( add_query_arg( 'randevu', 'eksik', $gun_url ) );
		exit;
	}

	$ymd = wp_date( 'Y-m-d', $ts );
	if ( ! ragip_slot_is_free( $ts, $ymd ) ) {
		wp_safe_redirect( add_query_arg( array( 'randevu' => 'dolu', 'gun' => $ymd ), $gun_url ) );
		exit;
	}

	$etiket = wp_date( 'd.m.Y H:i', $ts );
	$id = wp_insert_post( array(
		'post_type'   => 'ragip_appointment',
		'post_title'  => $ad . ' — ' . $etiket,
		'post_status' => 'private',
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		wp_safe_redirect( add_query_arg( 'randevu', 'hata', $gun_url ) );
		exit;
	}
	update_post_meta( $id, 'ragip_start', $ts );
	update_post_meta( $id, 'ragip_day', $ymd );
	update_post_meta( $id, 'ragip_ad', $ad );
	update_post_meta( $id, 'ragip_telefon', $telefon );
	update_post_meta( $id, 'ragip_eposta', $email );
	update_post_meta( $id, 'ragip_brans', $brans );
	update_post_meta( $id, 'ragip_not', $not );
	update_post_meta( $id, 'ragip_durum', 'aktif' );

	// Akademiye bildirim
	wp_mail(
		ragip_opt( 'ragip_email' ),
		'Yeni Randevu: ' . $ad . ' (' . $etiket . ')',
		"Yeni randevu alındı.\n\nAd Soyad: {$ad}\nTelefon: {$telefon}\nE-posta: {$email}\nBranş: {$brans}\nTarih ve saat: {$etiket}\nNot: " . ( $not !== '' ? $not : '-' ),
		array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $ad . ' <' . $email . '>' )
	);

	// Müşteriye onay
	wp_mail(
		$email,
		'Randevunuz alındı | Ragıp Savaş Sanat Akademisi',
		"Merhaba {$ad},\n\n{$etiket} tarihli randevunuz alınmıştır. Süre en fazla " . RAGIP_SLOT_LEN . " dakikadır.\n\nRagıp Savaş Sanat Akademisi\nTelefon: " . ragip_opt( 'ragip_phone' ) . "\nAdres: " . str_replace( "\n", ', ', ragip_opt( 'ragip_address' ) ),
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);

	wp_safe_redirect( add_query_arg( array( 'randevu' => 'ok', 'gun' => $ymd ), $gun_url ) );
	exit;
}
