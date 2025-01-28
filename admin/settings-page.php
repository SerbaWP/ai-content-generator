<?php
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}
?>
<div class="wrap">
  <h1>Generate Content</h1>
  <?php
  if (!empty($_POST['generate'])) {
    check_admin_referer('ai_generate_content', 'nonce');
    
    $template_id = sanitize_text_field($_POST['template_id']);
    $post_type = sanitize_text_field($_POST['post_type']);
    $post_status = sanitize_text_field($_POST['post_status']);
    
    // Get keyword source
    if ($_POST['keyword_source'] === 'template') {
      $keyword_template_id = sanitize_text_field($_POST['keyword_template_id']);
      $keyword_templates = (new AI_Content_Keyword_Manager())->get_keyword_templates();
      $keywords = $keyword_templates[$keyword_template_id]['keywords'] ?? '';
    } else {
      $keywords = sanitize_textarea_field($_POST['keywords']);
    }
    
    $templates = (new AI_Content_Template_Manager())->get_templates();
    $template = $templates[$template_id] ?? false;
    
    if ($template) {
      $api = new AI_Content_API_Handler();
      $keyword_list = array_map('trim', explode("\n", $keywords));
      $success_count = 0;

      foreach ($keyword_list as $kw) {
        if (empty($kw)) continue;
        
        $prompt = str_replace('{keywords}', $kw, $template['prompt']);
        $content = $api->generate_content($prompt);
        
        if (!is_wp_error($content) && !empty($content)) {
          wp_insert_post([
            'post_type' => $post_type,
            'post_status' => $post_status,
            'post_title' => 'Generated: ' . substr($kw, 0, 50),
            'post_content' => wp_kses_post($content),
          ]);
          $success_count++;
        }
      }
      
      echo '<div class="notice notice-success"><p>Generated '.$success_count.' posts!</p></div>';
    }
  }
  ?>
  
  <form method="post">
    <?php wp_nonce_field('ai_generate_content', 'nonce'); ?>
    
    <div class="form-field">
      <label>Content Template:</label>
      <select name="template_id" required>
        <?php foreach ((new AI_Content_Template_Manager())->get_templates() as $id => $template): ?>
          <option value="<?php echo esc_attr($id); ?>">
            <?php echo esc_html($template['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-field">
      <label>Keyword Source:</label>
      <select id="keyword_source" onchange="toggleKeywordInput()">
        <option value="manual">Enter Manually</option>
        <option value="template">Use Keyword Template</option>
      </select>
    </div>

    <div class="form-field" id="manual_keywords">
      <label>Keywords (one per line):</label>
      <textarea name="keywords" rows="5" required></textarea>
    </div>

    <div class="form-field" id="keyword_template" style="display:none">
      <label>Keyword Template:</label>
      <select name="keyword_template_id">
        <?php foreach ((new AI_Content_Keyword_Manager())->get_keyword_templates() as $id => $tpl): ?>
          <option value="<?php echo esc_attr($id); ?>">
            <?php echo esc_html($tpl['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if (get_option('ai_schedule_mode', 'manual') === 'manual'): ?>
      <div class="form-field">
        <label>Post Type:</label>
        <select name="post_type">
          <?php foreach (get_post_types(['public' => true], 'objects') as $post_type): 
            if ($post_type->name === 'attachment') continue;
          ?>
            <option value="<?php echo esc_attr($post_type->name); ?>">
              <?php echo esc_html($post_type->labels->singular_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-field">
        <label>Post Status:</label>
        <select name="post_status">
          <option value="draft">Draft</option>
          <option value="publish">Publish</option>
        </select>
      </div>
    <?php else: ?>
      <div class="notice notice-info">
        <p>Automatic mode is active. Content will be generated according to the schedule set in API Settings.</p>
      </div>
    <?php endif; ?>

    <button type="submit" name="generate" class="button button-primary">
      <?php echo (get_option('ai_schedule_mode', 'manual') === 'manual') ? 'Generate Now' : 'Test Generation'; ?>
    </button>
  </form>

  <script>
  function toggleKeywordInput() {
    const source = document.getElementById('keyword_source').value;
    document.getElementById('manual_keywords').style.display = 
      (source === 'manual') ? 'block' : 'none';
    document.getElementById('keyword_template').style.display = 
      (source === 'template') ? 'block' : 'none';
    
    // Toggle required attribute
    document.querySelector('#manual_keywords textarea').required = (source === 'manual');
    document.querySelector('#keyword_template select').required = (source === 'template');
  }
  
  // Initial state
  document.addEventListener('DOMContentLoaded', function() {
    toggleKeywordInput();
  });
  </script>

</div>