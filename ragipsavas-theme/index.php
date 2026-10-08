<?php
/**
 * Yedek şablon: ana sayfa dışındaki sayfalar ve arşiv görünümleri
 */
get_header();
?>
<main id="top">
	<section>
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'reveal visible' ); ?>>
						<h2 class="section-title"><?php the_title(); ?></h2>
						<div class="section-lead"><?php the_content(); ?></div>
					</article>
				<?php endwhile; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'İçerik bulunamadı.', 'ragipsavas' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
