<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div>
				<h4>Ragıp Savaş Sanat Akademisi</h4>
				<p>2007 yılından bu yana Göktürk'te sanat eğitimi.</p>
				<div class="socials">
					<?php if ( ragip_opt( 'ragip_instagram' ) ) : ?>
						<a href="<?php echo esc_url( ragip_opt( 'ragip_instagram' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram">IG</a>
					<?php endif; ?>
					<?php if ( ragip_opt( 'ragip_facebook' ) ) : ?>
						<a href="<?php echo esc_url( ragip_opt( 'ragip_facebook' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook">FB</a>
					<?php endif; ?>
					<?php if ( ragip_opt( 'ragip_youtube' ) ) : ?>
						<a href="<?php echo esc_url( ragip_opt( 'ragip_youtube' ) ); ?>" target="_blank" rel="noopener" aria-label="YouTube">YT</a>
					<?php endif; ?>
				</div>
			</div>
			<div>
				<h4>Eğitimler</h4>
				<ul>
					<li><a href="#egitimler">Klasik Bale</a></li>
					<li><a href="#egitimler">Piyano</a></li>
					<li><a href="#egitimler">Resim</a></li>
					<li><a href="#egitimler">Dans</a></li>
					<li><a href="#egitimler"><?php esc_html_e( 'Tüm Eğitimler', 'ragipsavas' ); ?></a></li>
				</ul>
			</div>
			<div>
				<h4>Kurumsal</h4>
				<ul>
					<li><a href="#kurumsal">Hakkımızda</a></li>
					<li><a href="#programlar">London College of Music</a></li>
					<li><a href="#programlar">Vaganova</a></li>
					<li><a href="#iletisim">İletişim</a></li>
				</ul>
			</div>
		</div>
		<div class="footer-bottom">
			<span>© 2007 – <?php echo esc_html( date_i18n( 'Y' ) ); ?> Ragıp Savaş Sanat Akademisi. Tüm hakları saklıdır.</span>
			<span>Göktürk, İstanbul</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
