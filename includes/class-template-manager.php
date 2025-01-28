<?php
class AI_Content_Template_Manager {
  public function get_templates() {
    return get_option('ai_content_templates', []);
  }

  public function save_template($template_id, $data) {
    $templates = $this->get_templates();
    
    if (empty($data['name']) || empty($data['prompt'])) {
      return new WP_Error('validation', 'Name and prompt are required');
    }

    $templates[$template_id] = [
      'name' => sanitize_text_field($data['name']),
      'prompt' => sanitize_textarea_field($data['prompt'])
    ];
    
    update_option('ai_content_templates', $templates);
    return true;
  }

  public function delete_template($template_id) {
    $templates = $this->get_templates();
    unset($templates[$template_id]);
    update_option('ai_content_templates', $templates);
  }
}