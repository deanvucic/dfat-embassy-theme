<?php

/**
 * @file
 * node_hooks.inc
 *
 * Implements template level Node-related hooks.
 */

/**
 * Implements template_preprocess_field().
 *
 * @see template_preprocess_field()
 */
function dfus_theme_preprocess_field(&$variables, $hook) {

  // GOVCMS UI KIT.
  // Bean 'Image and Text' field 'Link To' to show 'Read [title]' text.
  if ($variables['element']['#field_name'] === 'field_link_to' && $variables['element']['#bundle'] === 'image_and_text') {
    if (!empty($variables['items'][0]) && !empty($variables['element']['#object']->title)) {
      // This only applies if field has a non-configurable title.
      if ($variables['items'][0]['#field']['settings']['title'] === 'none') {
        $variables['items'][0]['#element']['title'] = t('Read !title', array('!title' => $variables['element']['#object']->title));
      }
    }
  }
  if (theme_get_setting('dfus_theme_override_image_styles') == 1) {
    // Define custom image style for image banners on home page.
    if ($variables['element']['#field_name'] === 'field_slide_image') {
      if ($variables['items'][0]['#image_style'] === 'feature_article') {
        $variables['items'][0]['#image_style'] = 'dfus_theme_banner';
      }
    }
    // Define custom image style for thumbnails on news / blogs / etc.
    elseif ($variables['element']['#field_name'] === 'field_thumbnail') {
      $image_style = $variables['items'][0]['#image_style'];
      if ($image_style === 'medium' || $image_style === 'thumbnail') {
        $variables['items'][0]['#image_style'] = 'dfus_theme_thumbnail';
      }
    }
    // Define custom image style for views.
    elseif ($variables['element']['#field_name'] === 'field_image') {
      if ($variables['items'][0]['#image_style'] === 'medium') {
        $variables['items'][0]['#image_style'] = 'dfus_theme_thumbnail';
      }
    }
  }

  // DFUS THEME.
  $element = $variables['element'];
  $field_name = $element['#field_name'];
  $bundle = $element['#bundle'];
  $entity_type = $element['#entity_type'];
  $functions = [
    '_dfus_theme_preprocess_field_' . $field_name,
    '_dfus_theme_preprocess_field_' . $field_name . '_' . $entity_type,
    '_dfus_theme_preprocess_field_' . $field_name . '_' . $entity_type . '_' . $bundle,
  ];
  foreach ($functions as $function) {
    if (function_exists($function)) {
      $function($variables, $hook);
    }
  }
}

function _dfus_theme_preprocess_field_field_embedded_code(&$variables, $hook) {
  $element = $variables['element'];
  if (empty($element['#items'])) {
    return;
  }
  foreach ($element['#items'] as $delta => $item) {
    $variables['items'][$delta]['#markup'] = strip_tags($item['value'], '<iframe>');
  }
}
