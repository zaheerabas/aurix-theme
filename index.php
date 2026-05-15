<?php get_header(); ?>
<div class="aurix-page-wrap">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; the_posts_navigation();
  else : ?>
    <div style="padding:60px 0;text-align:center;">
      <h2 style="font-family:'Cormorant Garamond',serif;color:#0d1b2a;">Nothing found</h2>
    </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
