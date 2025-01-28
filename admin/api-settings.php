<?php
if (!current_user_can('manage_options')) {
  wp_die('You do not have sufficient permissions to access this page.');
}
?>
<div class="wrap">
  <h1>API Settings</h1>
  <?php
  if (!empty($_POST['save_api_key'])) {
    check_admin_referer('save_api_key', 'nonce');
    update_option('deepseek_api_key', sanitize_text_field($_POST['api_key']));
    update_option('ai_schedule_interval', sanitize_text_field($_POST['schedule_interval']));
    AI_Content_Scheduler::update_schedule();
    echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
  }
  ?>
  <form method="post">
    <?php wp_nonce_field('save_api_key', 'nonce'); ?>
    <div class="form-field">
      <label>DeepSeek API Key:</label>
      <input type="password" name="api_key" value="<?php echo esc_attr(get_option('deepseek_api_key')); ?>" required>
    </div>
    <div class="form-field">
      <label>Scheduling Interval:</label>
      <select name="schedule_interval">
        <option value="hourly" <?php selected(get_option('ai_schedule_interval', 'hourly'), 'hourly'); ?>>Hourly</option>
        <option value="daily" <?php selected(get_option('ai_schedule_interval', 'hourly'), 'daily'); ?>>Daily</option>
      </select>
    </div>
    <button type="submit" name="save_api_key" class="button button-primary">Save</button>
  </form>
</div>