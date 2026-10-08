<?php
/**
 * Randevu sayfası (slug: randevu)
 */
get_header();

$bugun = wp_date( 'Y-m-d' );
$gun   = isset( $_GET['gun'] ) ? sanitize_text_field( wp_unslash( $_GET['gun'] ) ) : $bugun;
if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $gun ) || $gun < $bugun ) {
	$gun = $bugun;
}

$durum = isset( $_GET['randevu'] ) ? sanitize_key( wp_unslash( $_GET['randevu'] ) ) : '';
$mesajlar = array(
	'ok'    => array( 'tip' => 'ok',  'metin' => __( 'Randevunuz alındı. Onay e-postası adresinize gönderildi.', 'ragipsavas' ) ),
	'dolu'  => array( 'tip' => 'err', 'metin' => __( 'Seçtiğiniz saat az önce doldu. Lütfen başka bir saat seçin.', 'ragipsavas' ) ),
	'eksik' => array( 'tip' => 'err', 'metin' => __( 'Lütfen bir saat seçin ve zorunlu alanları doldurun.', 'ragipsavas' ) ),
	'hata'  => array( 'tip' => 'err', 'metin' => __( 'Bir sorun oluştu. Lütfen tekrar deneyin.', 'ragipsavas' ) ),
);

$saatler = ragip_day_slots( $gun );
$dolu_aralik = ragip_busy_ranges( $gun );
?>

<main id="top">

	<div class="hero hero-small">
		<div class="container hero-content">
			<span class="eyebrow"><?php esc_html_e( 'Randevu', 'ragipsavas' ); ?></span>
			<h1><?php esc_html_e( 'Randevu Alın', 'ragipsavas' ); ?></h1>
			<p><?php esc_html_e( 'Tarih seçin, uygun bir saat belirleyin ve formu doldurun. Her randevu en fazla 30 dakikadır.', 'ragipsavas' ); ?></p>
		</div>
	</div>

	<section class="form-section">
		<div class="container">

			<?php if ( isset( $mesajlar[ $durum ] ) ) : ?>
				<div class="alert alert-<?php echo esc_attr( $mesajlar[ $durum ]['tip'] ); ?>" role="status">
					<?php echo esc_html( $mesajlar[ $durum ]['metin'] ); ?>
				</div>
			<?php endif; ?>

			<div class="form-card reveal randevu-card">
				<form method="get" action="<?php echo esc_url( ragip_appointment_url() ); ?>" class="tarih-form">
					<label for="gun"><strong><?php esc_html_e( 'Tarih', 'ragipsavas' ); ?></strong></label>
					<div class="tarih-satir">
						<input id="gun" type="date" name="gun" min="<?php echo esc_attr( $bugun ); ?>" value="<?php echo esc_attr( $gun ); ?>">
						<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Saatleri Göster', 'ragipsavas' ); ?></button>
					</div>
				</form>

				<form method="post" class="application-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
					<input type="hidden" name="action" value="ragip_appointment">
					<?php wp_nonce_field( 'ragip_appointment', 'ragip_nonce' ); ?>
					<!-- Spam koruması -->
					<div class="hp" aria-hidden="true">
						<label>Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
					</div>

					<fieldset>
						<legend><?php echo esc_html( wp_date( 'd.m.Y', ragip_local_ts( $gun ) ) ); ?> <?php esc_html_e( 'için saat seçin', 'ragipsavas' ); ?></legend>
						<?php if ( empty( $saatler ) ) : ?>
							<p class="form-note"><?php esc_html_e( 'Bu gün için randevu bulunmuyor.', 'ragipsavas' ); ?></p>
						<?php else : ?>
						<div class="slot-grid">
							<?php foreach ( $saatler as $ts ) :
								$bitis = $ts + RAGIP_SLOT_LEN * 60;
								$dolu  = ( $ts <= time() );
								foreach ( $dolu_aralik as $ar ) {
									if ( $ts < $ar[1] && $ar[0] < $bitis ) {
										$dolu = true;
										break;
									}
								}
							?>
								<label class="slot<?php echo $dolu ? ' slot-dolu' : ''; ?>">
									<input type="radio" name="slot" value="<?php echo esc_attr( $ts ); ?>" <?php disabled( $dolu ); ?> required>
									<span class="slot-saat"><?php echo esc_html( wp_date( 'H:i', $ts ) ); ?></span>
									<span class="slot-durum"><?php echo $dolu ? esc_html__( 'Dolu', 'ragipsavas' ) : esc_html__( 'Müsait', 'ragipsavas' ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					</fieldset>

					<fieldset>
						<legend><?php esc_html_e( 'Bilgileriniz', 'ragipsavas' ); ?></legend>
						<div class="form-grid">
							<div class="field">
								<label for="ad_soyad"><?php esc_html_e( 'Ad Soyad', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<input id="ad_soyad" name="ad_soyad" type="text" required autocomplete="name">
							</div>
							<div class="field">
								<label for="telefon"><?php esc_html_e( 'Telefon', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<input id="telefon" name="telefon" type="tel" required autocomplete="tel">
							</div>
							<div class="field">
								<label for="eposta"><?php esc_html_e( 'E-posta', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<input id="eposta" name="eposta" type="email" required autocomplete="email">
							</div>
							<div class="field">
								<label for="brans"><?php esc_html_e( 'Branş', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<select id="brans" name="brans" required>
									<option value=""><?php esc_html_e( 'Seçiniz', 'ragipsavas' ); ?></option>
									<?php foreach ( ragip_courses() as $course ) : ?>
										<option value="<?php echo esc_attr( $course['name'] ); ?>"><?php echo esc_html( $course['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="field full">
								<label for="not"><?php esc_html_e( 'Not', 'ragipsavas' ); ?></label>
								<textarea id="not" name="not" rows="3"></textarea>
							</div>
						</div>
					</fieldset>

					<button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Randevuyu Onayla', 'ragipsavas' ); ?></button>
					<p class="form-note"><?php esc_html_e( 'Randevular 15 dakikalık aralıklarla başlar ve her biri en fazla 30 dakika sürer.', 'ragipsavas' ); ?></p>
				</form>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
