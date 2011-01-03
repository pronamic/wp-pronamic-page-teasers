<?php 

$teasers = PronamicPageTeasers::getTeasers();

?>

<ol class="teasers-list">

	<?php foreach($teasers as $teaser): ?>

	<li>
		<input name="<?php echo PronamicPageTeasers::NONCE; ?>[]" value="<?php echo $teaser->ID; ?>" type="checkbox" checked="checked" />

		<span><?php echo $teaser->post_title; ?></span>
	</li>

	<?php endforeach; ?>

</ol>

<?php 

wp_dropdown_pages(array(
	'depth' => 3 , 
	'selected' => 1 
));

?>

<input type="button" value="<?php _e('Add', PronamicPageTeasers::NONCE); ?>" class="button" /> 