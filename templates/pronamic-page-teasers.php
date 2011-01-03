<?php 

$teasers = pronamic_get_page_teasers();

$h = is_front_page() ? 3 : 2;

if($teasers): ?>

<div class="<?php echo PronamicPageTeasers::NONCE; ?>">
	<ul>
	
		<?php foreach($teasers as $post): setup_postdata($post); ?>
	
		<li>
			<article id="<?php echo PronamicPageTeasers::NONCE; ?>-<?php the_ID(); ?>" <?php post_class(PronamicPageTeasers::NONCE); ?>>
				<h<?php echo $h; ?> class="entry-title">
					<a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', PronamicPageTeasers::NONCE), the_title_attribute('echo=0')); ?>" rel="bookmark">
						<?php the_title(); ?>
					</a>
				</h<?php echo $h; ?>>
	
				<?php if(has_post_thumbnail()): ?>
	
				<a href="<?php the_permalink(); ?>">
					<?php the_post_thumbnail(); ?>
				</a>
	
				<?php endif; ?>
	
				<?php the_excerpt(); ?>
			</article>
		</li>
	
		<?php endforeach; ?>
	
	</ul>
</div>

<?php endif;
