<?php
/**
 * Başvuru formu sayfası (slug: basvuru)
 */
get_header();

$durum = isset( $_GET['basvuru'] ) ? sanitize_key( wp_unslash( $_GET['basvuru'] ) ) : '';

$mesajlar = array(
	'ok'    => array( 'tip' => 'ok',  'metin' => __( 'Başvurunuz başarıyla alındı. Teşekkür ederiz, en kısa sürede sizinle iletişime geçeceğiz.', 'ragipsavas' ) ),
	'eksik' => array( 'tip' => 'err', 'metin' => __( 'Lütfen zorunlu alanları doldurun ve KVKK metnini onaylayın.', 'ragipsavas' ) ),
	'hata'  => array( 'tip' => 'err', 'metin' => __( 'Bir sorun oluştu. Lütfen sayfayı yenileyip tekrar deneyin.', 'ragipsavas' ) ),
);
?>

<main id="top">

	<div class="hero hero-small">
		<div class="container hero-content">
			<span class="eyebrow"><?php esc_html_e( 'Kayıt', 'ragipsavas' ); ?></span>
			<h1><?php esc_html_e( 'Başvuru Formu', 'ragipsavas' ); ?></h1>
			<p><?php esc_html_e( 'Formu doldurarak eğitim alanlarımızdan birine başvurabilirsiniz. Başvurunuzu aldıktan sonra sizinle iletişime geçeceğiz.', 'ragipsavas' ); ?></p>
		</div>
	</div>

	<section class="form-section">
		<div class="container form-layout">

			<div class="form-card reveal">
				<?php if ( isset( $mesajlar[ $durum ] ) ) : ?>
					<div class="alert alert-<?php echo esc_attr( $mesajlar[ $durum ]['tip'] ); ?>" role="status">
						<?php echo esc_html( $mesajlar[ $durum ]['metin'] ); ?>
					</div>
				<?php endif; ?>

				<form class="application-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
					<input type="hidden" name="action" value="ragip_application">
					<?php wp_nonce_field( 'ragip_application', 'ragip_nonce' ); ?>

					<!-- Spam koruması: bu alan kullanıcıya gösterilmez -->
					<div class="hp" aria-hidden="true">
						<label>Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
					</div>

					<fieldset>
						<legend><?php esc_html_e( 'Öğrenci Bilgileri', 'ragipsavas' ); ?></legend>
						<div class="form-grid">
							<div class="field">
								<label for="ad_soyad"><?php esc_html_e( 'Ad Soyad', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<input id="ad_soyad" name="ad_soyad" type="text" required autocomplete="name">
							</div>
							<div class="field">
								<label for="yas"><?php esc_html_e( 'Yaş', 'ragipsavas' ); ?></label>
								<input id="yas" name="yas" type="number" min="3" max="99" inputmode="numeric">
							</div>
							<div class="field full">
								<label for="veli_adi"><?php esc_html_e( 'Veli Adı Soyadı (çocuklar için)', 'ragipsavas' ); ?></label>
								<input id="veli_adi" name="veli_adi" type="text" autocomplete="off">
							</div>
						</div>
					</fieldset>

					<fieldset>
						<legend><?php esc_html_e( 'İletişim Bilgileri', 'ragipsavas' ); ?></legend>
						<div class="form-grid">
							<div class="field">
								<label for="telefon"><?php esc_html_e( 'Telefon', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<input id="telefon" name="telefon" type="tel" required autocomplete="tel" placeholder="05xx xxx xx xx">
							</div>
							<div class="field">
								<label for="eposta"><?php esc_html_e( 'E-posta', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<input id="eposta" name="eposta" type="email" required autocomplete="email">
							</div>
						</div>
					</fieldset>

					<fieldset>
						<legend><?php esc_html_e( 'Eğitim Tercihi', 'ragipsavas' ); ?></legend>
						<div class="form-grid">
							<div class="field">
								<label for="brans"><?php esc_html_e( 'Branş', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<select id="brans" name="brans" required>
									<option value=""><?php esc_html_e( 'Seçiniz', 'ragipsavas' ); ?></option>
									<?php foreach ( ragip_courses() as $course ) : ?>
										<option value="<?php echo esc_attr( $course['name'] ); ?>"><?php echo esc_html( $course['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="field">
								<label for="deneyim"><?php esc_html_e( 'Deneyim Düzeyi', 'ragipsavas' ); ?> <span class="req">*</span></label>
								<select id="deneyim" name="deneyim" required>
									<option value=""><?php esc_html_e( 'Seçiniz', 'ragipsavas' ); ?></option>
									<option value="Yeni başlayan"><?php esc_html_e( 'Yeni başlayan', 'ragipsavas' ); ?></option>
									<option value="Orta seviye"><?php esc_html_e( 'Orta seviye', 'ragipsavas' ); ?></option>
									<option value="İleri seviye"><?php esc_html_e( 'İleri seviye', 'ragipsavas' ); ?></option>
								</select>
							</div>
							<div class="field full">
								<label for="mesaj"><?php esc_html_e( 'Eklemek İstedikleriniz', 'ragipsavas' ); ?></label>
								<textarea id="mesaj" name="mesaj" rows="4"></textarea>
							</div>
						</div>
					</fieldset>

					<label class="checkbox">
						<input type="checkbox" name="kvkk" value="1" required>
						<span><?php esc_html_e( 'Kişisel verilerimin başvurumun değerlendirilmesi ve tarafımla iletişime geçilmesi amacıyla işlenmesini kabul ediyorum.', 'ragipsavas' ); ?> <span class="req">*</span></span>
					</label>

					<button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Başvuruyu Gönder', 'ragipsavas' ); ?></button>
					<p class="form-note"><?php esc_html_e( '* ile işaretli alanlar zorunludur.', 'ragipsavas' ); ?></p>
				</form>
			</div>

			<aside class="form-aside reveal">
				<h3><?php esc_html_e( 'Başvuru Sonrası', 'ragipsavas' ); ?></h3>
				<ul class="values">
					<li><?php esc_html_e( 'Başvurunuz akademimize iletilir.', 'ragipsavas' ); ?></li>
					<li><?php esc_html_e( 'Ekibimiz sizinle telefon veya e-posta ile iletişime geçer.', 'ragipsavas' ); ?></li>
					<li><?php esc_html_e( 'Deneme dersi ve kayıt detaylarını birlikte planlarız.', 'ragipsavas' ); ?></li>
				</ul>
				<div class="aside-contact">
					<p><strong><?php esc_html_e( 'Sorularınız mı var?', 'ragipsavas' ); ?></strong></p>
					<p><a href="<?php echo esc_attr( 'tel:' . preg_replace( '/[^0-9+]/', '', ragip_opt( 'ragip_phone_link' ) ) ); ?>"><?php echo esc_html( ragip_opt( 'ragip_phone' ) ); ?></a></p>
				</div>
			</aside>

		</div>
	</section>

</main>

<?php
get_footer();
