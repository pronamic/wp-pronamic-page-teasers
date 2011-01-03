=== Pronamic Page Teasers ===
Contributors: pronamic, remcotolsma 
Tags: pronamic, page, pages, pages on page, teasers on page, teasers, teaser, related pages
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.0

This plugin makes it easy to bind pages (teasers) to a page.

== Description ==

With this plugin a user can easily bind pages to a page. This way a developer can easily show 
teasers of pages on a page. Editors can easily define the page teasers on the page editor screen.  


== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your 
WordPress installation and then activate the Plugin from Plugins page.

The teasers will automatically be added to [the_content()](http://codex.wordpress.org/Function_Reference/the_content) 
using [the_content() filter](http://codex.wordpress.org/Plugin_API/Filter_Reference/the_content). If you want 
to change the markup of the teasers, you should create a "pronamic-page-teasers.php" file  within your theme directory. 

**pronamic-page-teasers.php**

	<?php 
	
	$teasers = pronamic_get_page_teasers();
	
	$h = is_front_page() ? 3 : 2;
	
	if($teasers): ?>
	
	<ul>
		
		<?php foreach($teasers as $post): setup_postdata($post); ?>
		
		<li>
			<article id="teaser-<?php the_ID(); ?>" <?php post_class('teaser'); ?>>
				<h<?php echo $h; ?> class="entry-title">
					<a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s'), the_title_attribute('echo=0')); ?>" rel="bookmark">
						<?php the_title(); ?>
					</a>
				</h<?php echo $h; ?>>
		
				<?php the_excerpt(); ?>
			</article>
		</li>
		
		<?php endforeach; ?>
		
	</ul>
	
	<?php endif; ?>

= Functions =

**is_pronamic_page_teasers()**

Returns true if the loop is rendering Pronamic page teasers.


**pronamic_page_teasers_the_content()**

Extends the_content() with the Pronamic page teasers. Can be disabled by removing the filter:

	remove_filter('the_content', 'pronamic_page_teasers_the_content');


**pronamic_get_page_teasers()**

This functions can be used to get an array of all the teaser pages
that are defined for the current global page. This function must 
be within [The Loop](http://codex.wordpress.org/The_Loop). This
function is based on the [get_pages()](http://codex.wordpress.org/Function_Reference/get_pages) 
function.


**pronamic_page_teasers()**

This functions renders the Pronamic page teasers. It is normally used within 
the pronamic_page_teasers_the_content() function. If you don't want to 
display the teasers right after the_content(), you can disable the pronamic_page_teasers_the_content()
filter. You can then manually add the function pronamic_page_teasers() to one of your template files.


== Screenshots ==

1. Pronamic Page Teasers meta box

2. Pronamic Page Teasers on the frontend of the Twentyten WordPress theme

3. Default template for the Pronamic Page Teasers


== Changelog ==

= 1.0 =
*	Initial release


== Links ==

*	[Pronamic](http://pronamic.eu/)
*	[Remco Tolsma](http://remcotolsma.nl/)
*	[Markdown's Syntax Documentation][markdown syntax]

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"