<?php
/**
 * Akademi Paneli: tüm yönetim işlerinin tek ekrandan erişildiği ayrı yönetim sayfası
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'ragip_admin_panel_menu', 2 );
function ragip_admin_panel_menu() {
	add_menu_page(
		'Akademi Paneli',
		'Akademi Paneli',
		'edit_posts',
		'ragip-panel',
		'ragip_admin_panel_render',
		'dashicons-welcome-learn-more',
		2
	);
}

/**
 * Sayıları bir sorguda hesaplar
 */
function ragip_panel_counts() {
	$say = array(
		'basvuru_yeni' => 0,
		'basvuru_toplam' => 0,
		'randevu_bugun' => 0,
		'randevu_aktif' => 0,
		'egitim' => 0,
		'galeri' => 0,
	);

	$basvurular = get_posts( array( 'post_type' => 'ragip_application', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
	$say['basvuru_toplam'] = count( $basvurular );
	foreach ( $basvurular as $id ) {
		if ( ( get_post_meta( $id, 'ragip_durum', true ) ?: 'yeni' ) === 'yeni' ) {
			$say['basvuru_yeni']++;
		}
	}

	$bugun = wp_date( 'Y-m-d' );
	$randevular = get_posts( array( 'post_type' => 'ragip_appointment', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
	foreach ( $randevular as $id ) {
		$durum = get_post_meta( $id, 'ragip_durum', true ) ?: 'aktif';
		if ( $durum === 'aktif' ) {
			$say['randevu_aktif']++;
		}
		if ( get_post_meta( $id, 'ragip_day', true ) === $bugun && $durum !== 'iptal' ) {
			$say['randevu_bugun']++;
		}
	}

	$say['egitim'] = count( ragip_courses() );
	$say['galeri'] = count( get_posts( array( 'post_type' => 'ragip_gallery', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) );
	return $say;
}

function ragip_admin_panel_render() {
	$say = ragip_panel_counts();

	$kartlar = array(
		array( 'Yeni başvurular', $say['basvuru_yeni'], 'edit.php?post_type=ragip_application', 'Başvurular' ),
		array( 'Bugünkü randevular', $say['randevu_bugun'], 'edit.php?post_type=ragip_appointment', 'Randevular' ),
		array( 'Aktif randevular', $say['randevu_aktif'], 'edit.php?post_type=ragip_appointment', 'Randevular' ),
		array( 'Eğitimler', $say['egitim'], 'edit.php?post_type=ragip_course', 'Eğitimler' ),
		array( 'Galeri görselleri', $say['galeri'], 'edit.php?post_type=ragip_gallery', 'Galeri' ),
	);

	$hizli = array(
		array( 'Yeni eğitim ekle', 'post-new.php?post_type=ragip_course' ),
		array( 'Galeriye görsel ekle', 'post-new.php?post_type=ragip_gallery' ),
		array( 'Ana sayfa ve yazı ayarları', 'customize.php' ),
		array( 'Randevu saatlerini kapat', 'customize.php?autofocus[section]=ragip_appointments' ),
		array( 'Site iletişim bilgileri', 'customize.php?autofocus[section]=ragip_contact' ),
		array( 'Ana sayfa metinleri', 'customize.php?autofocus[section]=ragip_content' ),
	);
	?>
	<div class="wrap ragip-panel">
		<h1>Akademi Paneli</h1>
		<p>Ragıp Savaş Sanat Akademisi'nin tüm yönetim işleri bu ekrandan yapılır.</p>

		<div class="ragip-kartlar" style="display:flex;gap:16px;flex-wrap:wrap;margin:20px 0;">
			<?php foreach ( $kartlar as $kart ) : ?>
				<a href="<?php echo esc_url( admin_url( $kart[2] ) ); ?>" style="flex:1 1 180px;background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:18px;text-decoration:none;color:#1d2327;">
					<span style="display:block;font-size:30px;font-weight:700;"><?php echo esc_html( $kart[1] ); ?></span>
					<span style="color:#50575e;"><?php echo esc_html( $kart[0] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<h2>Hızlı işlemler</h2>
		<ul style="list-style:disc;padding-left:20px;">
			<?php foreach ( $hizli as $is ) : ?>
				<li><a href="<?php echo esc_url( admin_url( $is[1] ) ); ?>"><?php echo esc_html( $is[0] ); ?></a></li>
			<?php endforeach; ?>
		</ul>

		<h2>Yönetim menüsü</h2>
		<p>Sol menüde ayrıca <strong>Eğitimler</strong>, <strong>Galeri</strong>, <strong>Başvurular</strong> ve <strong>Randevular</strong> bölümleri bulunur. Sitenin yazıları ve görünümü için <strong>Görünüm &gt; Özelleştir</strong> kullanılır.</p>
	</div>
	<?php
}
