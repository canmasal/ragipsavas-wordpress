<?php
/**
 * Ana sayfa şablonu: tüm içerikler yönetim panelinden gelir
 */
get_header();

$lat      = ragip_opt( 'ragip_lat' );
$lng      = ragip_opt( 'ragip_lng' );
$map_src  = add_query_arg( array(
	'q'      => $lat . ',' . $lng,
	'hl'     => 'tr',
	'z'      => 17,
	'output' => 'embed',
), 'https://maps.google.com/maps' );
$dir_url  = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $lat . ',' . $lng );
$maps_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $lat . ',' . $lng );

$galeri = get_posts( array(
	'post_type'   => 'ragip_gallery',
	'post_status' => 'publish',
	'numberposts' => 24,
	'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
) );
?>

<main id="top">

	<!-- Hero -->
	<div class="hero">
		<div class="container hero-content">
			<span class="eyebrow"><?php echo esc_html( ragip_text( 'ragip_hero_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( ragip_text( 'ragip_hero_title' ) ); ?></h1>
			<p><?php echo esc_html( ragip_text( 'ragip_hero_text' ) ); ?></p>
			<div class="hero-actions">
				<a href="#egitimler" class="btn btn-primary"><?php esc_html_e( 'Eğitimleri Keşfedin', 'ragipsavas' ); ?></a>
				<a href="<?php echo esc_url( ragip_appointment_url() ); ?>" class="btn btn-primary"><?php esc_html_e( 'Randevu Al', 'ragipsavas' ); ?></a>
				<a href="<?php echo esc_url( ragip_application_url() ); ?>" target="_blank" rel="noopener" class="btn btn-outline"><?php esc_html_e( 'Başvuru Yapın', 'ragipsavas' ); ?></a>
			</div>
			<div class="hero-stats">
				<div class="stat"><strong>2007</strong><span><?php esc_html_e( 'Kuruluş yılı', 'ragipsavas' ); ?></span></div>
				<div class="stat"><strong>15+</strong><span><?php esc_html_e( 'Eğitim alanı', 'ragipsavas' ); ?></span></div>
				<div class="stat"><strong><?php esc_html_e( 'Çocuk & Yetişkin', 'ragipsavas' ); ?></strong><span><?php esc_html_e( 'Her yaşa uygun', 'ragipsavas' ); ?></span></div>
			</div>
		</div>
	</div>

	<!-- Kurumsal -->
	<section id="kurumsal">
		<div class="container about-grid">
			<div class="reveal">
				<span class="eyebrow"><?php esc_html_e( 'Kurumsal', 'ragipsavas' ); ?></span>
				<h2 class="section-title"><?php echo esc_html( ragip_text( 'ragip_about_title' ) ); ?></h2>
				<p class="section-lead"><?php echo esc_html( ragip_text( 'ragip_about_text' ) ); ?></p>
				<p class="section-lead mt"><?php echo esc_html( ragip_text( 'ragip_about_text2' ) ); ?></p>
			</div>
			<div class="about-card reveal">
				<h3><?php esc_html_e( 'Neden Ragıp Savaş?', 'ragipsavas' ); ?></h3>
				<p><?php esc_html_e( 'Yılların deneyimiyle, her öğrencinin kendi temposunda ilerlediği, güvenli ve ilham veren bir öğrenme ortamı sunuyoruz.', 'ragipsavas' ); ?></p>
				<ul class="values">
					<li><?php esc_html_e( 'Alanında uzman ve deneyimli eğitmenler', 'ragipsavas' ); ?></li>
					<li><?php esc_html_e( 'Çocuk ve yetişkinlere yönelik ayrı programlar', 'ragipsavas' ); ?></li>
					<li><?php esc_html_e( 'Uluslararası sertifika programları', 'ragipsavas' ); ?></li>
					<li><?php esc_html_e( 'Düzenli performans ve sergi etkinlikleri', 'ragipsavas' ); ?></li>
				</ul>
			</div>
		</div>
	</section>

	<!-- Eğitimler -->
	<section id="egitimler" class="courses">
		<div class="container">
			<div class="section-head reveal">
				<span class="eyebrow"><?php esc_html_e( 'Eğitimlerimiz', 'ragipsavas' ); ?></span>
				<h2 class="section-title"><?php echo esc_html( ragip_text( 'ragip_courses_title' ) ); ?></h2>
				<p class="section-lead"><?php esc_html_e( 'Müzikten dansa, görsel sanatlardan sporda kendini ifade etmeye kadar geniş bir yelpaze.', 'ragipsavas' ); ?></p>
			</div>
			<div class="course-grid">
				<?php foreach ( ragip_courses() as $course ) :
					$link = ! empty( $course['url'] ) ? $course['url'] : 'https://www.ragipsavassanat.com/egitimlerimiz/' . $course['slug'];
				?>
				<article class="course reveal">
					<div class="course-media" data-letter="<?php echo esc_attr( $course['letter'] ); ?>">
						<?php echo ragip_course_image_html( $course ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<div class="course-body">
						<h3><?php echo esc_html( $course['name'] ); ?></h3>
						<a class="more" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Detaylı bilgi →', 'ragipsavas' ); ?></a>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $galeri ) ) : ?>
	<!-- Galeri -->
	<section id="galeri" class="gallery">
		<div class="container">
			<div class="section-head reveal">
				<span class="eyebrow"><?php esc_html_e( 'Galeri', 'ragipsavas' ); ?></span>
				<h2 class="section-title"><?php echo esc_html( ragip_text( 'ragip_gallery_title' ) ); ?></h2>
				<p class="section-lead"><?php echo esc_html( ragip_text( 'ragip_gallery_text' ) ); ?></p>
			</div>
			<div class="gallery-grid">
				<?php foreach ( $galeri as $item ) :
					$thumb_id = get_post_thumbnail_id( $item->ID );
					if ( ! $thumb_id ) {
						continue;
					}
					$full = wp_get_attachment_image_url( $thumb_id, 'full' );
					$alt  = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ?: $item->post_title;
				?>
				<a class="gallery-item reveal" href="<?php echo esc_url( $full ); ?>" data-caption="<?php echo esc_attr( $item->post_title ); ?>">
					<?php echo wp_get_attachment_image( $thumb_id, 'large', false, array( 'loading' => 'lazy', 'alt' => esc_attr( $alt ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $item->post_title ) : ?>
						<span class="gallery-caption"><?php echo esc_html( $item->post_title ); ?></span>
					<?php endif; ?>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Programlar -->
	<section id="programlar">
		<div class="container">
			<div class="section-head reveal">
				<span class="eyebrow"><?php esc_html_e( 'Uluslararası Programlar', 'ragipsavas' ); ?></span>
				<h2 class="section-title"><?php esc_html_e( 'Dünya standartlarında eğitim', 'ragipsavas' ); ?></h2>
				<p class="section-lead"><?php esc_html_e( 'Uluslararası kurumlarla yürüttüğümüz özel müfredatlar ile öğrencilerimize global bir referans sunuyoruz.', 'ragipsavas' ); ?></p>
			</div>
			<div class="programs">
				<div class="program reveal">
					<span class="eyebrow"><?php esc_html_e( 'Müzik Eğitimi', 'ragipsavas' ); ?></span>
					<h3>London College of Music</h3>
					<p><?php esc_html_e( 'Londra\'nın köklü müzik kurumunun müfredatı ile sınavlara ve uluslararası sertifikalara hazırlık.', 'ragipsavas' ); ?></p>
					<a href="https://www.ragipsavassanat.com/london-college-of-music" class="btn btn-outline"><?php esc_html_e( 'Programı İncele', 'ragipsavas' ); ?></a>
				</div>
				<div class="program alt reveal">
					<span class="eyebrow"><?php esc_html_e( 'Bale Eğitimi', 'ragipsavas' ); ?></span>
					<h3>Vaganova Education System</h3>
					<p><?php esc_html_e( 'Dünyaca ünlü Rus bale okulunun metodu ile teknik mükemmellik ve sanatsal duruş.', 'ragipsavas' ); ?></p>
					<a href="https://www.ragipsavassanat.com/vaganova-education-system" class="btn btn-outline"><?php esc_html_e( 'Programı İncele', 'ragipsavas' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<!-- İletişim -->
	<section id="iletisim" class="contact">
		<div class="container">
			<div class="section-head reveal">
				<span class="eyebrow"><?php esc_html_e( 'İletişim', 'ragipsavas' ); ?></span>
				<h2 class="section-title"><?php esc_html_e( 'Bizimle iletişime geçin', 'ragipsavas' ); ?></h2>
				<p class="section-lead"><?php esc_html_e( 'Kayıt, bilgi ve randevu için bize ulaşabilirsiniz.', 'ragipsavas' ); ?></p>
			</div>
			<div class="contact-grid">
				<div class="contact-item reveal">
					<div class="ico">📍</div>
					<h3><?php esc_html_e( 'Adres', 'ragipsavas' ); ?></h3>
					<p><?php echo nl2br( esc_html( ragip_opt( 'ragip_address' ) ) ); ?></p>
				</div>
				<div class="contact-item reveal">
					<div class="ico">📞</div>
					<h3><?php esc_html_e( 'Telefon', 'ragipsavas' ); ?></h3>
					<p><a href="<?php echo esc_attr( 'tel:' . preg_replace( '/[^0-9+]/', '', ragip_opt( 'ragip_phone_link' ) ) ); ?>"><?php echo esc_html( ragip_opt( 'ragip_phone' ) ); ?></a></p>
				</div>
				<div class="contact-item reveal">
					<div class="ico">💬</div>
					<h3><?php esc_html_e( 'WhatsApp', 'ragipsavas' ); ?></h3>
					<p><a href="<?php echo esc_url( ragip_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Mesaj gönderin', 'ragipsavas' ); ?></a></p>
				</div>
				<div class="contact-item reveal">
					<div class="ico">✉️</div>
					<h3><?php esc_html_e( 'E-posta', 'ragipsavas' ); ?></h3>
					<p><a href="<?php echo esc_attr( 'mailto:' . antispambot( ragip_opt( 'ragip_email' ) ) ); ?>"><?php echo esc_html( ragip_opt( 'ragip_email' ) ); ?></a></p>
				</div>
			</div>

			<div class="map-wrap reveal">
				<iframe
					class="map-frame"
					title="<?php esc_attr_e( 'Ragıp Savaş Sanat Akademisi konumu', 'ragipsavas' ); ?>"
					src="<?php echo esc_url( $map_src ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen></iframe>
				<div class="map-actions">
					<a class="btn btn-primary" target="_blank" rel="noopener" href="<?php echo esc_url( $dir_url ); ?>"><?php esc_html_e( 'Yol Tarifi Al', 'ragipsavas' ); ?></a>
					<a class="btn btn-outline" target="_blank" rel="noopener" href="<?php echo esc_url( $maps_url ); ?>"><?php esc_html_e( 'Google Maps\'te Aç', 'ragipsavas' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
