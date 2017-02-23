<?php

/**
 * @file
 * Returns the HTML for a single Drupal page.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728148
 */
?>

<header class="header" id="header" role="banner">
  <div class="header__inner">
    <?php if ($secondary_menu): ?>
      <nav class="header__secondary-menu" id="secondary-menu" role="navigation">
        <?php print theme('links__system_secondary_menu', array(
          'links' => $secondary_menu,
          'attributes' => array(
            'class' => array(
              'links',
              'inlineLinks--bordered--double',
              'clearfix',
            ),
          ),
          'heading' => array(
            'text' => isset($secondary_menu_heading) ? $secondary_menu_heading : '',
            'level' => 'h2',
            'class' => array('element-invisible'),
          ),
        )); ?>
      </nav>
    <?php endif; ?>
    <div class="logo-and-search">
      <div class="logo-wrapper">
        <?php
          $logo_alt = theme_get_setting('dfus_theme_header_logo_alt');
          $logo_alt = !empty($logo_alt) ? $logo_alt : variable_get('site_name', 'Home');
          $logo_img = theme_image(array(
            'path' => path_to_theme() . '/dist/images/svg/crest.svg',
            'alt' => $logo_alt,
            'attributes' => array('class' => array('header__logo-image')),
          ));
          $logo_class = array('header__logo');
          if (empty(theme_get_setting('dfus_theme_header_title'))) {
            $logo_class[] = 'no-title';
          }

          print l($logo_img, $front_page, array(
            'html' => TRUE,
            'attributes' => array(
              'id' => 'logo',
              'title' => t('Back to Homepage'),
              'rel' => 'home',
              'class' => $logo_class,
            ),
          ));
        ?>
        <?php if (!empty($page['posts_page'])): ?>
          <div class="header-title"><?php print $page['posts_page_heading1']; ?>
          <?php if (!empty($page['posts_page_heading2'])): ?>
            <div class="header-subtitle"><?php print $page['posts_page_heading2']; ?></div>
          <?php endif; ?>
          </div>
        <?php elseif (theme_get_setting('dfus_theme_header_title')): ?>
          <div class="header-title"><?php print l(decode_entities(theme_get_setting('dfus_theme_header_title')), $front_page); ?></div>
        <?php endif; ?>
      </div>
      <?php print render($page['header']); ?>
    </div>
  </div>
</header>

<div id="nav">
  <?php print render($page['navigation']); ?>
  <nav class="mobile-nav" id="mobile-nav">
    <?php
      $menu = dfus_theme_menu_navigation_links('main-menu');
      print theme('item_list', array('items' => $menu, 'attributes' => ['class' => ['links']]));
    ?>
  </nav>
</div>

<div id="page">

  <?php
    // Render the sidebars to see if there's anything in them.
    $sidebar_first  = render($page['sidebar_first']);
    $sidebar_second = render($page['sidebar_second']);
  ?>

  <?php print render($page['highlighted']); ?>

  <div id="main">
    <div id="content" class="column" role="main">
      <div class="content-header">
        <div class="content-header-inner">
          <?php print $breadcrumb; ?>
          <a href="#skip-link" id="skip-content" class="element-invisible" tabindex="-1">Go to top of page</a>
          <a id="main-content"></a>
          <?php print render($title_prefix); ?>
          <?php if ($title): ?>
            <h1 class="page__title title" id="page-title"><?php print $title; ?></h1>
          <?php endif; ?>
          <?php print render($title_suffix); ?>
        </div>
      </div>
      <div class="content-body">
        <?php print $messages; ?>
        <?php print render($tabs); ?>
        <?php print render($page['help']); ?>
        <?php if ($action_links): ?>
          <ul class="action-links"><?php print render($action_links); ?></ul>
        <?php endif; ?>
        <div class="content-body-inner<?php print $sidebar_first ? ' has-sidebar' : ' no-sidebar'; ?>">
          <?php print render($page['content']); ?>
          <?php print $feed_icons; ?>
          <?php if ($sidebar_first): ?>
            <aside class="content-sidebar-first">
              <?php print $sidebar_first; ?>
            </aside>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if ($sidebar_second): ?>
    <aside class="content-sidebar-second" role="complementary">
      <?php print $sidebar_second; ?>
    </aside>
  <?php endif; ?>
  
  <div id="footer">
    <?php print render($page['footer']); ?>
  </div>
</div>
<?php print render($page['bottom']); ?>
