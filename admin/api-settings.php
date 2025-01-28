<?php
if (!current_user_can('manage_options')) {
  wp_die('You do not have sufficient permissions to access this page.');
}

$scheduler = new AI_Content_Scheduler();
?>
<div class="wrap">
  <h1>API & Scheduling Settings</h1>
  <?php
  if (!empty($_POST['save_settings'])) {
    check_admin_referer('save_api_key', 'nonce');
    
    // Save API key and schedule settings
    update_option('deepseek_api_key', sanitize_text_field($_POST['api_key']));
    update_option('ai_schedule_mode', sanitize_text_field($_POST['schedule_mode']));
    update_option('ai_schedule_interval', sanitize_text_field($_POST['schedule_interval']));
    update_option('ai_auto_post_status', sanitize_text_field($_POST['auto_post_status']));
    
    // Update the schedule
    $scheduler::update_schedule();
    echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
  }
  ?>
  <form method="post">
    <?php wp_nonce_field('save_api_key', 'nonce'); ?>
    
    <div class="form-field">
      <label>DeepSeek API Key:</label>
      <input type="password" name="api_key" value="<?php echo esc_attr(get_option('deepseek_api_key')); ?>" required>
    </div>

    <h2>Content Generation Mode</h2>
    
    <div class="form-field">
      <label>Generation Mode:</label>
      <select name="schedule_mode" id="schedule_mode" onchange="toggleModeSettings()">
        <option value="manual" <?php selected(get_option('ai_schedule_mode', 'manual'), 'manual'); ?>>Manual Generation</option>
        <option value="auto" <?php selected(get_option('ai_schedule_mode', 'manual'), 'auto'); ?>>Automatic Scheduling</option>
      </select>
    </div>

    <div id="auto_settings" style="<?php echo (get_option('ai_schedule_mode', 'manual') === 'auto') ? 'display:block' : 'display:none'; ?>">
      <div class="form-field">
        <label>Schedule Interval:</label>
        <select name="schedule_interval">
          <?php 
          $intervals = [
            'hourly' => 'Every Hour',
            '2hours' => 'Every 2 Hours',
            '3hours' => 'Every 3 Hours',
            '6hours' => 'Every 6 Hours',
            '12hours' => 'Every 12 Hours',
            'daily' => 'Every Day',
            '2days' => 'Every 2 Days',
            'weekly' => 'Every Week'
          ];
          foreach ($intervals as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected(get_option('ai_schedule_interval', 'hourly'), $value, false) . '>' . esc_html($label) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="form-field">
        <label>Auto Post Status:</label>
        <select name="auto_post_status">
          <option value="draft" <?php selected(get_option('ai_auto_post_status', 'draft'), 'draft'); ?>>Save as Draft</option>
          <option value="publish" <?php selected(get_option('ai_auto_post_status', 'draft'), 'publish'); ?>>Publish Immediately</option>
        </select>
      </div>
    </div>

    <button type="submit" name="save_settings" class="button button-primary">Save Settings</button>
  </form>

  <script>
  function toggleModeSettings() {
    var mode = document.getElementById('schedule_mode').value;
    document.getElementById('auto_settings').style.display = (mode === 'auto') ? 'block' : 'none';
  }
  </script>
</div>