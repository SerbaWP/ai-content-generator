<?php
if (!current_user_can('manage_options')) {
  wp_die('You do not have sufficient permissions to access this page.');
}
?>
<div class="wrap">
  <h1>Templates</h1>
  <?php
  if (!empty($_GET['delete'])) {
    check_admin_referer('delete_template');
    (new AI_Content_Template_Manager())->delete_template(sanitize_text_field($_GET['delete']));
    echo '<div class="notice notice-success"><p>Template deleted.</p></div>';
  }

  if (!empty($_POST['submit_template'])) {
    check_admin_referer('save_template', 'nonce');
    
    $data = [
      'name' => sanitize_text_field($_POST['name']),
      'prompt' => sanitize_textarea_field($_POST['prompt']),
      'post_type' => sanitize_text_field($_POST['post_type']),
      'post_status' => sanitize_text_field($_POST['post_status']),
    ];
    
    $template_id = sanitize_title($_POST['name']);
    $result = (new AI_Content_Template_Manager())->save_template($template_id, $data);
    
    if (is_wp_error($result)) {
      echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
    } else {
      echo '<div class="notice notice-success"><p>Template saved!</p></div>';
    }
  }
  
  $template = [];
  if (!empty($_GET['edit'])) {
    $template = (new AI_Content_Template_Manager())->get_templates()[sanitize_text_field($_GET['edit'])] ?? [];
  }
  ?>
    <form method="post">
    <?php wp_nonce_field('save_template', 'nonce'); ?>
    <div class="form-field">
      <label>Template Name:</label>
      <input type="text" name="name" 
             value="<?php echo esc_attr($template['name'] ?? ''); ?>" 
             required>
    </div>

    <div class="form-field">
      <label>Prompt:</label>
      <textarea name="prompt" rows="5" required><?php 
        echo esc_textarea($template['prompt'] ?? 'Generate content about {keywords}'); 
      ?></textarea>
      <p class="description">Use <code>{keywords}</code> as a placeholder.</p>
    </div>

    <div class="form-field">
      <label>Post Type:</label>
      <select name="post_type">
        <?php foreach (get_post_types(['public' => true], 'objects') as $post_type): 
          if ($post_type->name === 'attachment') continue;
        ?>
          <option value="<?php echo esc_attr($post_type->name); ?>" 
            <?php selected($template['post_type'] ?? 'post', $post_type->name); ?>>
            <?php echo esc_html($post_type->labels->singular_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-field">
      <label>Post Status:</label>
      <select name="post_status">
        <option value="draft" <?php selected($template['post_status'] ?? 'draft', 'draft'); ?>>Draft</option>
        <option value="publish" <?php selected($template['post_status'] ?? 'draft', 'publish'); ?>>Publish</option>
      </select>
    </div>

    <button type="submit" name="submit_template" class="button button-primary">
      <?php echo empty($_GET['edit']) ? 'Create Template' : 'Update Template'; ?>
    </button>
  </form>

  <h2>Existing Templates</h2>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Post Type</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ((new AI_Content_Template_Manager())->get_templates() as $id => $tpl): ?>
        <tr>
          <td><?php echo esc_html($tpl['name']); ?></td>
          <td><?php echo esc_html($tpl['post_type']); ?></td>
          <td><?php echo esc_html($tpl['post_status']); ?></td>
          <td>
            <a href="?page=ai-content-templates&edit=<?php echo esc_attr($id); ?>" class="button">Edit</a>
            <a href="<?php echo wp_nonce_url('?page=ai-content-templates&delete=' . $id, 'delete_template'); ?>" 
               class="button"
               onclick="return confirm('Are you sure you want to delete this template?')">
              Delete
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>