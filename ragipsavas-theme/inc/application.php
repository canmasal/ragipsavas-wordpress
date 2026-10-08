<?php
/**
 * Başvuru formu: gönderim, doğrulama ve e-posta bildirimi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Başvuru sayfasının adresi (sayfa slug'ı: basvuru)
 */
function ragip_application_url() {
	$sayfa = get_page_by_path( 'basvuru' );
	return $sayfa ? get_permalink( $sayfa ) : home_url( '/basvuru/' );
}

add_action( 'admin_post_nopriv_ragip_application', 'ragip_handle_application' );
add_action( 'admin_post_ragip_application', 'ragip_handle_application' );

/**
 * Form gönderimini işle
 */
function ragip_handle_application() {
	$redirect = ragip_application_url();

	// 1. Güvenlik: nonce doğrulaması
	$nonce = isset( $_POST['ragip_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ragip_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'ragip_application' ) ) {
		wp_safe_redirect( add_query_arg( 'basvuru', 'hata', $redirect ) );
		exit;
	}

	// 2. Spam koruması: gizli alan doluysa bot kabul edilir, sessizce başarılı göster
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'basvuru', 'ok', $redirect ) );
		exit;
	}

	// 3. Verileri temizle
	$ad      = sanitize_text_field( wp_unslash( $_POST['ad_soyad'] ?? '' ) );
	$veli    = sanitize_text_field( wp_unslash( $_POST['veli_adi'] ?? '' ) );
	$yas     = absint( $_POST['yas'] ?? 0 );
	$telefon = sanitize_text_field( wp_unslash( $_POST['telefon'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['eposta'] ?? '' ) );
	$brans   = sanitize_text_field( wp_unslash( $_POST['brans'] ?? '' ) );
	$deneyim = sanitize_text_field( wp_unslash( $_POST['deneyim'] ?? '' ) );
	$mesaj   = sanitize_textarea_field( wp_unslash( $_POST['mesaj'] ?? '' ) );
	$kvkk    = ! empty( $_POST['kvkk'] );

	// 4. Doğrulama
	$brans_listesi = wp_list_pluck( ragip_courses(), 'name' );
	$deneyim_listesi = array( 'Yeni başlayan', 'Orta seviye', 'İleri seviye' );

	$gecerli = $ad !== ''
		&& $telefon !== ''
		&& is_email( $email )
		&& in_array( $brans, $brans_listesi, true )
		&& in_array( $deneyim, $deneyim_listesi, true )
		&& $kvkk;

	if ( ! $gecerli ) {
		wp_safe_redirect( add_query_arg( 'basvuru', 'eksik', $redirect ) );
		exit;
	}

	// 5. Başvuruyu yönetim panelinde kaydet
	$basvuru_id = wp_insert_post( array(
		'post_type'   => 'ragip_application',
		'post_title'  => $ad . ' — ' . $brans,
		'post_status' => 'private',
	) );
	if ( $basvuru_id && ! is_wp_error( $basvuru_id ) ) {
		update_post_meta( $basvuru_id, 'ragip_veli', $veli );
		update_post_meta( $basvuru_id, 'ragip_yas', $yas > 0 ? $yas : '' );
		update_post_meta( $basvuru_id, 'ragip_telefon', $telefon );
		update_post_meta( $basvuru_id, 'ragip_eposta', $email );
		update_post_meta( $basvuru_id, 'ragip_brans', $brans );
		update_post_meta( $basvuru_id, 'ragip_deneyim', $deneyim );
		update_post_meta( $basvuru_id, 'ragip_mesaj', $mesaj );
		update_post_meta( $basvuru_id, 'ragip_durum', 'yeni' );
	}

	// 6. Akademiye bildirim e-postası
	$alici   = ragip_opt( 'ragip_email' );
	$konu    = sprintf( 'Yeni Başvuru: %s (%s)', $ad, $brans );
	$icerik  = "Yeni bir kayıt başvurusu alındı.\n\n";
	$icerik .= "Ad Soyad: {$ad}\n";
	$icerik .= 'Veli Adı: ' . ( $veli !== '' ? $veli : '-' ) . "\n";
	$icerik .= 'Yaş: ' . ( $yas > 0 ? $yas : '-' ) . "\n";
	$icerik .= "Telefon: {$telefon}\n";
	$icerik .= "E-posta: {$email}\n";
	$icerik .= "Branş: {$brans}\n";
	$icerik .= "Deneyim: {$deneyim}\n";
	$icerik .= "Mesaj:\n" . ( $mesaj !== '' ? $mesaj : '-' ) . "\n";

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $ad . ' <' . $email . '>',
	);
	wp_mail( $alici, $konu, $icerik, $headers );

	// 7. Başvuru sahibine onay e-postası
	$onay_konu   = 'Başvurunuz alındı | Ragıp Savaş Sanat Akademisi';
	$onay_icerik = "Merhaba {$ad},\n\n"
		. "{$brans} eğitimi için başvurunuz alınmıştır. En kısa sürede sizinle iletişime geçeceğiz.\n\n"
		. "Ragıp Savaş Sanat Akademisi\n"
		. 'Telefon: ' . ragip_opt( 'ragip_phone' ) . "\n";
	wp_mail( $email, $onay_konu, $onay_icerik, array( 'Content-Type: text/plain; charset=UTF-8' ) );

	wp_safe_redirect( add_query_arg( 'basvuru', 'ok', $redirect ) );
	exit;
}
