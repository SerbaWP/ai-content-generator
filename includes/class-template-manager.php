<?php
class AI_Content_Template_Manager {
  public function get_templates() {
    return get_option('ai_content_templates', []);
  }

  public function save_template($template_id, $data) {
    if (empty($data['name']) || empty($data['prompt'])) {
      return new WP_Error('invalid_template', 'Template name and prompt are required.');
    }

    $templates = $this->get_templates();
    $templates[$template_id] = [
      'name' => sanitize_text_field($data['name']),
      'prompt' => sanitize_textarea_field($data['prompt']),
      'post_type' => sanitize_text_field($data['post_type']),
      'post_status' => sanitize_text_field($data['post_status'])
    ];
    
    update_option('ai_content_templates', $templates);
    return true;
  }

  public function delete_template($template_id) {
    $templates = $this->get_templates();
    if (!isset($templates[$template_id])) {
      return new WP_Error('invalid_template', 'Template does not exist');
    }
    unset($templates[$template_id]);
    update_option('ai_content_templates', $templates);
  }
}