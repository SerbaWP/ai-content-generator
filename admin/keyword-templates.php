<?php
if (!current_user_can('manage_options')) {
  wp_die('Unauthorized access');
}
?>
<div class="wrap">
  <h1>Keyword Templates</h1>
  <?php
  if (!empty($_GET['delete'])) {
    check_admin_referer('delete_keyword_template');
    (new AI_Content_Keyword_Manager())->delete_keyword_template(sanitize_text_field($_GET['delete']));
    echo '<div class="notice notice-success"><p>Template deleted.</p></div>';
  }

  if (!empty($_POST['submit_keywords'])) {
    check_admin_referer('save_keyword_template', 'nonce');
    
    $data = [
      'name' => sanitize_text_field($_POST['name']),
      'keywords' => sanitize_textarea_field($_POST['keywords'])
    ];
    
    $template_id = sanitize_title($_POST['name']);
    (new AI_Content_Keyword_Manager())->save_keyword_template($template_id, $data);
    echo '<div class="notice notice-success"><p>Template saved!</p></div>';
  }
  ?>

  <form method="post">
    <?php wp_nonce_field('save_keyword_template', 'nonce'); ?>
    <div class="form-field">
      <label>Template Name:</label>
      <input type="text" name="name" required>
    </div>
    <div class="form-field">
      <label>Keywords (one per line):</label>
      <textarea name="keywords" rows="5" required></textarea>
    </div>
    <button type="submit" name="submit_keywords" class="button button-primary">Save Template</button>
  </form>

  <h2>Saved Keyword Templates</h2>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Keywords</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ((new AI_Content_Keyword_Manager())->get_keyword_templates() as $id => $tpl): ?>
        <tr>
          <td><?php echo esc_html($tpl['name']); ?></td>
          <td><?php echo nl2br(esc_html($tpl['keywords'])); ?></td>
          <td>
            <a href="?page=ai-content-keywords&delete=<?php echo esc_attr($id); ?>" 
               class="button"
               onclick="return confirm('Delete this keyword template?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>