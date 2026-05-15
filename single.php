<?php get_header(); ?>
<div class="aurix-page-wrap" style="max-width:860px;">
  <?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <h1 class="page-title"><?php the_title(); ?></h1>
      <div style="font-size:.8rem;color:#8a9ab0;margin-bottom:24px;display:flex;align-items:center;gap:14px;">
        <span><i class="fas fa-calendar" style="color:#b8925a;margin-right:5px;"></i><?php echo get_the_date(); ?></span>
        <span><i class="fas fa-user" style="color:#b8925a;margin-right:5px;"></i><?php the_author(); ?></span>
      </div>
      <?php if ( has_post_thumbnail() ) : ?>
        <div style="margin-bottom:28px;border-radius:12px;overflow:hidden;">
          <?php the_post_thumbnail('large',['style'=>'width:100%;height:auto;display:block;']); ?>
        </div>
      <?php endif; ?>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; ?>
</div>
<?php get_footer(); ?>
