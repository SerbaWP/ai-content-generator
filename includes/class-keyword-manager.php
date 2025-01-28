<?php
class AI_Content_Keyword_Manager {
  public function get_keyword_templates() {
    return get_option('ai_keyword_templates', []);
  }

  public function save_keyword_template($template_id, $data) {
    $templates = $this->get_keyword_templates();
    $templates[$template_id] = [
      'name' => sanitize_text_field($data['name']),
      'keywords' => sanitize_textarea_field($data['keywords']),
    ];
    update_option('ai_keyword_templates', $templates);
  }

  public function delete_keyword_template($template_id) {
    $templates = $this->get_keyword_templates();
    unset($templates[$template_id]);
    update_option('ai_keyword_templates', $templates);
  }
}