<?php
class AI_Content_Scheduler {
  // ... (activation/deactivation methods remain the same)

  public static function generate_scheduled_content() {
    $templates = (new AI_Content_Template_Manager())->get_templates();
    $api = new AI_Content_API_Handler();

    foreach ($templates as $template_id => $template) {
      $content = $api->generate_content($template['prompt']);
      
      if (is_wp_error($content)) {
        error_log("[Scheduler] Failed to generate content for template $template_id: " . $content->get_error_message());
        continue;
      }

      wp_insert_post([
        'post_type' => $template['post_type'],
        'post_status' => $template['post_status'],
        'post_title' => 'AI Content - ' . $template_id . ' - ' . uniqid(),
        'post_content' => wp_kses_post($content),
      ]);
    }
  }
}
add_action('ai_content_auto_generate', ['AI_Content_Scheduler', 'generate_scheduled_content']);