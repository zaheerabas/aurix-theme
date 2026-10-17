<?php get_header(); ?>
<?php if ( function_exists( 'aurix_dir_page_is_embed' ) && aurix_dir_page_is_embed() ) : ?>
  <?php // Supplier directory shortcode: full-width sections with their own heading. ?>
  <?php while ( have_posts() ) : the_post(); ?>
    <div id="post-<?php the_ID(); ?>" <?php post_class( 'aurix-page-full' ); ?>><?php the_content(); ?></div>
  <?php endwhile; ?>
<?php else : ?>
<div class="aurix-page-wrap">
  <?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <h1 class="page-title"><?php the_title(); ?></h1>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; ?>
</div>
<?php endif; ?>
<?php get_footer(); ?>
