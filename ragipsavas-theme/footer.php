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

<?php if ( ragip_whatsapp_url() ) : ?>
<a class="whatsapp-float" href="<?php echo esc_url( ragip_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'WhatsApp ile iletişime geçin', 'ragipsavas' ); ?>">
	<svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true" fill="#fff"><path d="M16 3C8.8 3 3 8.8 3 16c0 2.3.6 4.5 1.8 6.5L3 29l6.7-1.8C11.6 28.4 13.8 29 16 29c7.2 0 13-5.8 13-13S23.2 3 16 3zm0 23.6c-2 0-3.9-.5-5.6-1.5l-.4-.2-4 1.1 1.1-3.9-.3-.4C5.7 19.9 5.1 18 5.1 16 5.1 9.9 10 5 16 5s10.9 4.9 10.9 11S22 26.6 16 26.6zm6-8.2c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5 2.5 1 3 .8 3.6.7.5-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>
</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
