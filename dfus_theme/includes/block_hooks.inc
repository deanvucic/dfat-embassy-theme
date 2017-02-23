<?php

/**
 * @file
 * block_hooks.inc
 *
 * Implement template level Block related hooks.
 */

/**
 * Implements hook_preprocess_bean().
 */
function dfus_theme_preprocess_entity_bean(&$variables) {
  $delta = $variables['bean']->delta;
  $type = $variables['bean']->type;
  $functions = [
    '_dfus_theme_preprocess_entity_bean_' . preg_replace('/\-+/', '_', $type),
    '_dfus_theme_preprocess_entity_bean_' . preg_replace('/\-+/', '_', $delta),
  ];
  foreach ($functions as $function) {
    if (function_exists($function)) {
      $function($variables);
    }
  }
}

/**
 * Replace [SUMMARY-TABLE] with real pivot table in National Register Summary.
 *
 * @param array $variables
 *   Variables.
 */
function _dfus_theme_preprocess_entity_bean_image_and_text(&$variables) {
  $elements = &$variables['elements'];
  // Render Promotional Image with Link [DFUS-67].
  if ($variables['view_mode'] == 'promotional_feature') {
    if (!empty($elements['#entity']->field_link_to[LANGUAGE_NONE][0]['url'])) {
      $path = ['path' => $elements['#entity']->field_link_to[LANGUAGE_NONE][0]['url']];
      $variables['content']['field_bean_image'][0]['#path'] = $path;
      $elements['field_bean_image'][0]['#path'] = $path;
    }
  }
}

/**
 * Implements hook_block_view_alter().
 * @param $data
 * @param $block
 */
function dfus_theme_block_view_alter(&$data, $block) {
  // Replace social links icon.
  if ($block->module == 'govcms_social_links' && $block->delta == 'services') {
    $posts = NULL;
    if ($term = menu_get_object('taxonomy_term', 2)) {
      if ($term->vocabulary_machine_name == 'locations' && taxonomy_term_is_page($term)) {
        $posts = $term;
      }
    }

    foreach (['facebook', 'twitter', 'youtube', 'vimeo', 'flickr', 'rss', 'email'] as $service) {
      $field_name = 'field_' . $service . '_url';
      if ($posts && !empty($posts->$field_name)) {
        $field = $posts->$field_name;
        if (!empty($field[LANGUAGE_NONE][0]['value'])) {
          if (isset($data['content'][$service])) {
            $data['content'][$service]['#url'] = $field[LANGUAGE_NONE][0]['value'];
          }
          else {
            $data['content'][$service] = [
              '#theme' => 'govcms_social_link',
              '#title' => ucfirst($service),
              '#icon' => drupal_get_path('module', 'govcms_social_links') . '/images/' . $service . '.png',
              '#url' => $field[LANGUAGE_NONE][0]['value'],
            ];
          }
        }
        else {
          unset($data['content'][$service]);
        }
      }
      else {
        unset($data['content'][$service]);
      }
    }

    foreach ($data['content'] as $service => $content) {
      $icon = drupal_get_path('theme', 'dfus_theme') . '/dist/images/svg/social-' . $service . '.svg';
      if (@file_exists($icon)) {
        $data['content'][$service]['#icon'] = $icon;
      }
    }
  }
}
