<?php

/**
 * @file
 * template.php
 */

include_once 'includes/block_hooks.inc';
include_once 'includes/field_hooks.inc';
include_once 'includes/node_hooks.inc';

/**
 * Implements hook_html_head_alter().
 */
function dfus_theme_html_head_alter(&$head_elements) {
  // Mobile Viewport.
  $head_elements['viewport'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'),
  );
  // IE Latest Browser.
  $head_elements['ie_view'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array('http-equiv' => 'x-ua-compatible', 'content' => 'ie=edge'),
  );
}

/**
 * Implements hook_js_alter().
 */
function dfus_theme_js_alter(&$javascript) {
  $javascript['misc/jquery.js']['data'] = drupal_get_path('theme', 'dfus_theme') . '/vendor/jquery/jquery-3.1.1.min.js';
}

/**
 * Implements hook_views_pre_render().
 */
function dfus_theme_views_pre_render(&$variables) {
  if (theme_get_setting('dfus_theme_override_image_styles') == 1) {
    if ($variables->name === 'footer_teaser') {
      $len = count($variables->result);
      for ($i = 0; $i < $len; $i++) {
        if (!empty($variables->result[$i]->field_field_image)) {
          // Define custom image style for thumbnails on footer_teaser.
          if ($variables->result[$i]->field_field_image[0]['rendered']['#image_style'] == 'blog_teaser_thumbnail') {
            $variables->result[$i]->field_field_image[0]['rendered']['#image_style'] = 'dfus_theme_thumbnail';
          }
        }
      }
    }
  }
}

/**
 * Implements hook_image_styles_alter().
 */
function dfus_theme_image_styles_alter(&$styles) {
  if (theme_get_setting('dfus_theme_override_image_styles') == 1) {
    $styles['dfus_theme_banner'] = array(
      'label' => 'govCMS UI-KIT - Banner',
      'name' => 'dfus_theme_banner',
      'storage' => IMAGE_STORAGE_NORMAL,
      'effects' => array(
        array(
          'label' => 'Scale and crop',
          'name' => 'image_scale_and_crop',
          'data' => array(
            'width' => 1650,
            'height' => 440,
            'upscale' => 1,
          ),
          'effect callback' => 'image_scale_and_crop_effect',
          'dimensions callback' => 'image_resize_dimensions',
          'form callback' => 'image_resize_form',
          'summary theme' => 'image_resize_summary',
          'module' => 'image',
          'weight' => 0,
        ),
      ),
    );
    $styles['dfus_theme_thumbnail'] = array(
      'label' => 'govCMS UI-KIT - Thumbnail',
      'name' => 'dfus_theme_thumbnail',
      'storage' => IMAGE_STORAGE_NORMAL,
      'effects' => array(
        array(
          'label' => 'Scale and crop',
          'name' => 'image_scale_and_crop',
          'data' => array(
            'width' => 370,
            'height' => 275,
            'upscale' => 1,
          ),
          'effect callback' => 'image_scale_and_crop_effect',
          'dimensions callback' => 'image_resize_dimensions',
          'form callback' => 'image_resize_form',
          'summary theme' => 'image_resize_summary',
          'module' => 'image',
          'weight' => 0,
        ),
      ),
    );
  }
  return $styles;
}

/**
 * Implements theme_breadcrumb().
 */
function dfus_theme_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $output = '';

  if (!empty($breadcrumb)) {
    // Build the breadcrumb trail.
    $output = '<nav class="breadcrumbs--inverted" role="navigation" aria-label="breadcrumb">';
    $output .= '<ul><li>' . implode('</li><li>', $breadcrumb) . '</li></ul>';
    $output .= '</nav>';
  }

  return $output;
}

/**
 * Implements hook_form_alter().
 */
function dfus_theme_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id === 'search_api_page_search_form_default_search') {
    // Global header form.
    $form['keys_1']['#attributes']['placeholder'] = t('Type search term here');
    $form['keys_1']['#title'] = t('Search field');
  }
  elseif ($form_id === 'search_api_page_search_form') {
    // Search page (above results) form.
    $form['form']['keys_1']['#title'] = t('Type search term here');
  }
  if ($form_id === 'search_form') {
    // Search form on page not found (404 page).
    $form['basic']['keys']['#title'] = t('Type search term here');
  }
}

/**
 * Implements theme_preprocess_search_api_page_result().
 */
function dfus_theme_preprocess_search_api_page_result(&$variables) {
  // Strip out HTML tags from search results.
  $variables['snippet'] = strip_tags($variables['snippet']);
  // Remove the author / date from the result display.
  $variables['info'] = '';
}

/**
 * Implements theme_preprocess_search_result().
 */
function dfus_theme_preprocess_search_result(&$variables) {
  // Strip out HTML tags from search results (404 page).
  $variables['snippet'] = strip_tags($variables['snippet']);
  // Remove the author / date from the result display (404 page).
  $variables['info'] = '';
}

/**
 * Implements hook_ds_pre_render_alter().
 */
function dfus_theme_ds_pre_render_alter(&$layout_render_array, $context, &$variables) {
  switch ($context['entity_type']) {
    case 'node':
      $node = $variables['node'];
      /** @var EntityDrupalWrapper $wrapper */
      try {
        $node_wrapper = entity_metadata_wrapper('node', $node);
      }
      catch (Exception $exception) {
        watchdog_exception('DFUS', $exception);
        return;
      }
      if ($context['bundle'] == 'news_article' && $context['view_mode'] == 'slider') {
        // Set defaults.
        $attributes = array('class' => array('read-more'));
        $link = url('node/' . $node->nid);
        // Check for Link To field and overwrite the link if it's not empty.
        $field_link_to = !empty($node_wrapper->field_link_to) ? $node_wrapper->field_link_to->value() : array();
        if (!empty($field_link_to) && !empty($field_link_to['url'])) {
          $link = url($field_link_to['url'], $field_link_to);
          $attributes += $field_link_to['attributes'];
        }
        // Set title from read more link.
        $layout_render_array['ds_content'][1]['title'][0]['#markup'] = '<h3>' . l($node->title, $link) . '</h3>';
        $attributes['target'] = "_self";
        // Set read more button.
        $ds_field_settings = ds_get_field_settings($context['entity_type'], $context['bundle'], $context['view_mode']);
        $news_read_more_weight = !empty($ds_field_settings['news_read_more']['weight']) ? $ds_field_settings['news_read_more']['weight'] : 3;
        $layout_render_array['ds_content'][1]['news_read_more'] = array(
          '#markup' => l(t('Read more'), $link, array('external' => TRUE, 'attributes' => $attributes)),
          '#weight' => $news_read_more_weight,
        );
      }
      break;
  }
}

/**
 * Implements hook_node_view_alter().
 */
function dfus_theme_node_view_alter(&$build) {
  if ($build['#node']->type == 'event' && $build['#view_mode'] == 'tile') {
    $node = $build['#node'];
    $links = array();
    // Construct Read more link markup.
    $node_title_stripped = strip_tags($node->title);
    $links['node-readmore'] = array(
      'title' => t('Read more<span class="element-invisible"> about @title</span>', array('@title' => $node_title_stripped)),
      'href' => 'node/' . $node->nid,
      'html' => TRUE,
      'attributes' => array('rel' => 'tag', 'title' => $node_title_stripped),
    );
    // Assign links array.
    $build['links']['node']['#links'] = $links;
  }
}

/**
 * Implements hook_preprocess_entity().
 */
function dfus_theme_preprocess_entity(&$variables) {
  if ($variables['entity_type'] == 'bean') {
    // The hook_preprocess_bean() isn't fired automatically.
    // See https://www.drupal.org/node/1361756.
    dfus_theme_preprocess_entity_bean($variables);
  }
}

/**
 * Implements template_preprocess_page().
 *
 * @see template_preprocess_page()
 */
function dfus_theme_preprocess_page(&$variables) {
  $variables['page']['posts_page'] = FALSE;
  if ($term = menu_get_object('taxonomy_term', 2)) {
    if ($term->vocabulary_machine_name == 'locations' && taxonomy_term_is_page($term)) {
      $variables['page']['posts_page'] = TRUE;
      if (!empty($term->field_posts_heading1[LANGUAGE_NONE][0]['value'])) {
        $heading1 = $term->field_posts_heading1[LANGUAGE_NONE][0]['value'];
      }
      else {
        $heading1 = theme_get_setting('dfus_theme_header_title');
      }
      $heading2 = $term->name;
      if (!empty($term->field_posts_heading2[LANGUAGE_NONE][0]['value'])) {
        $heading2 = $term->field_posts_heading2[LANGUAGE_NONE][0]['value'];
      }
      $variables['page']['posts_page_heading1'] = $heading1;
      $variables['page']['posts_page_heading2'] = $heading2;
    }
  }
}

/**
 * Returns HTML for a menu link and submenu.
 *
 * @param array $variables
 *   Variables.
 *
 * @return string
 *   HTML.
 *
 * @see theme_menu_link()
 */
function dfus_theme_menu_link($variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }

  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  if (!empty($element['#localized_options']['attributes']['class'])
    && is_array($element['#localized_options']['attributes']['class'])
    && in_array('no-link', $element['#localized_options']['attributes']['class'])
  ) {
    $output = '<span' . drupal_attributes($element['#localized_options']['attributes']) . '>' . $element['#title'] . '</span>';
  }

  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}

/**
 * Returns HTML for a menu item.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: Structured array data for a menu item.
 *   - properties: Various properties of a menu item.
 *
 * @return string
 *   HTML.
 *
 * @ingroup themeable
 */
function dfus_theme_superfish_menu_item($variables) {
  $element = $variables['element'];
  $properties = $variables['properties'];
  $sub_menu = '';

  if ($element['below']) {
    $sub_menu .= isset($variables['wrapper']['wul'][0]) ? $variables['wrapper']['wul'][0] : '';
    $sub_menu .= ($properties['megamenu']['megamenu_content']) ? '<ol>' : '<ul>';
    $sub_menu .= $element['below'];
    $sub_menu .= ($properties['megamenu']['megamenu_content']) ? '</ol>' : '</ul>';
    $sub_menu .= isset($variables['wrapper']['wul'][1]) ? $variables['wrapper']['wul'][1] : '';
  }

  $output = '<li' . drupal_attributes($element['attributes']) . '>';
  $output .= ($properties['megamenu']['megamenu_column']) ? '<div class="sf-megamenu-column">' : '';
  $output .= isset($properties['wrapper']['whl'][0]) ? $properties['wrapper']['whl'][0] : '';
  if ($properties['use_link_theme']) {
    $link_variables = array(
      'menu_item' => $element['item'],
      'link_options' => $element['localized_options'],
    );
    $output .= theme('superfish_menu_item_link', $link_variables);
  }
  else {
    $output .= l($element['item']['link']['title'], $element['item']['link']['href'], $element['localized_options']);
    if (!empty($element['localized_options']['attributes']['class'])
      && is_array($element['localized_options']['attributes']['class'])
      && in_array('no-link', $element['localized_options']['attributes']['class'])
    ) {
      $output = '<span' . drupal_attributes($element['localized_options']['attributes']) . '>' . $element['item']['link']['title'] . '</span>';
    }
  }
  $output .= isset($properties['wrapper']['whl'][1]) ? $properties['wrapper']['whl'][1] : '';
  $output .= ($properties['megamenu']['megamenu_wrapper']) ? '<ul class="sf-megamenu"><li class="sf-megamenu-wrapper ' . $element['attributes']['class'] . '">' : '';
  $output .= $sub_menu;
  $output .= ($properties['megamenu']['megamenu_wrapper']) ? '</li></ul>' : '';
  $output .= ($properties['megamenu']['megamenu_column']) ? '</div>' : '';
  $output .= '</li>';

  return $output;
}

/**
 * Theme a menu item link.
 *
 * @param array $variables
 *   An array of variables containing:
 *    - menu_item: The menu item array.
 *    - link_options: An array of link options.
 *
 * @return string
 *   Menu Item HTML.
 *
 * @ingroup themeable
 */
function dfus_theme_superfish_menu_item_link($variables) {
  $menu_item = $variables['menu_item'];
  $link_options = $variables['link_options'];
  $output = l($menu_item['link']['title'], $menu_item['link']['href'], $link_options);
  if (!empty($link_options['attributes']['class'])
    && is_array($link_options['attributes']['class'])
    && in_array('no-link', $link_options['attributes']['class'])
  ) {
    $output = '<span' . drupal_attributes($link_options['attributes']) . '>' . $menu_item['link']['title'] . '</span>';
  }

  return $output;
}

/**
 * Returns an array of links for a navigation menu.
 *
 * @param string $menu_name
 *   The name of the menu.
 * @param int $level
 *   Optional, the depth of the menu to be returned.
 *
 * @return array
 *   An array of links of the specified menu and level.
 */
function dfus_theme_menu_navigation_links($menu_name, $level = 0) {
  // Don't even bother querying the menu table if no menu is specified.
  if (empty($menu_name)) {
    return array();
  }

  // Get the menu hierarchy for the current page.
  $tree = menu_tree_page_data($menu_name, 2);

  // Create a single level of links.
  $links = array();
  foreach ($tree as $item) {
    if ($l = _dfus_theme_menu_navigation_link($item)) {
      $links += $l;
      if (!empty($item['below'])) {
        $key = array_keys($l);
        $key = reset($key);
        $links[$key]['children'] = [];
        foreach ($item['below'] as $child) {
          if ($child_l = _dfus_theme_menu_navigation_link($child)) {
            $links[$key]['children'] += $child_l;
          }
        }
      }
    }
  }
  return $links;
}

/**
 * Render Menu navigation link.
 *
 * @param array $item
 *   Item.
 *
 * @return array|null
 *   Item Link.
 */
function _dfus_theme_menu_navigation_link($item) {
  if (!$item['link']['hidden']) {
    $router_item = menu_get_item();

    $class = '';
    $l = $item['link']['localized_options'];
    $l['href'] = $item['link']['href'];
    $l['title'] = $item['link']['title'];
    if ($item['link']['in_active_trail']) {
      $class = ' active-trail';
      $l['attributes']['class'][] = 'active-trail';
    }
    // Normally, l() compares the href of every link with $_GET['q'] and sets
    // the active class accordingly. But local tasks do not appear in menu
    // trees, so if the current path is a local task, and this link is its
    // tab root, then we have to set the class manually.
    if ($item['link']['href'] == $router_item['tab_root_href'] && $item['link']['href'] != $_GET['q']) {
      $l['attributes']['class'][] = 'active';
    }

    $output = l($l['title'], $l['href'], $l);
    if (!empty($l['attributes']['class']) && in_array('no-link', $l['attributes']['class'])) {
      $output = '<span' . drupal_attributes($l['attributes']) . '>' . $l['title'] . '</span>';
    }

    // Keyed with the unique mlid to generate classes in theme_links().
    return [
      'menu-' . $item['link']['mlid'] . $class => [
        'data' => $output,
      ],
    ];
  }

  return NULL;
}

/**
 * Implements template_preprocess_html().
 *
 * @see template_preprocess_html()
 */
function dfus_theme_preprocess_html(&$variables) {
  // GOVCMS UI KIT HTML.
  drupal_add_js("(function(h) {h.className = h.className.replace('no-js', '') })(document.documentElement);", array('type' => 'inline', 'scope' => 'header'));
  drupal_add_js('jQuery.extend(Drupal.settings, { "pathToTheme": "' . path_to_theme() . '" });', 'inline');
  // Drupal forms.js does not support new jQuery. Migrate library needed.
  drupal_add_js(drupal_get_path('theme', 'dfus_theme') . '/vendor/jquery/jquery-migrate-1.2.1.min.js');

  // DFUS THEME HTML.
  if (arg(0) == 'news') {
    $variables['classes_array'][] = 'page-news-media';
    $variables['classes_array'][] = 'page-news-media-news';
  }
}
