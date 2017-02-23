<?php

/**
 * @file
 * node_hooks.inc
 *
 * Implements template level Node-related hooks.
 */

/**
 * Implements hook_preprocess_node().
 */
function dfus_theme_preprocess_node(&$variables) {

  // GOVCMS UI KIT.
  if ($variables['view_mode'] === 'teaser' || $variables['view_mode'] === 'compact') {
    // Apply thumbnail class to node teaser view if image exists.
    $has_thumb = !empty($variables['content']['field_thumbnail']);
    $has_image = !empty($variables['content']['field_image']);
    $has_featured_image = !empty($variables['content']['field_feature_image']);
    if ($has_thumb || $has_image || $has_featured_image) {
      $variables['classes_array'][] = 'has-thumbnail';
    }
  }

  if ($variables['type'] === 'webform') {
    // Hide submitted date on webforms.
    $variables['display_submitted'] = FALSE;
  }

  // DFUS THEME.
  $node = $variables['node'];

  $preprocess_function = '_dfus_theme_preprocess_node_' . $node->type;
  if (function_exists($preprocess_function)) {
    $preprocess_function($variables, $node);
  }

  $theme_hook = 'node__' . $variables['type'] . '__' . $variables['view_mode'];
  $theme_hook = strtr(strtolower($theme_hook), array(' ' => '_', '-' => '_'));
  $variables['theme_hook_suggestions'][] = $theme_hook;
}
