<?php
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

$template_manager = new AI_Content_Template_Manager();
?>
<div class="wrap">
    <h1>Content Templates</h1>

    <?php
    // Handle template deletion
    if (!empty($_GET['delete'])) {
        check_admin_referer('delete_template');
        $template_manager->delete_template(sanitize_text_field($_GET['delete']));
        echo '<div class="notice notice-success"><p>Template deleted.</p></div>';
    }

    // Handle form submission
    if (!empty($_POST['submit_template'])) {
        check_admin_referer('save_template', 'nonce');
        
        $data = [
            'name' => sanitize_text_field($_POST['name']),
            'prompt' => sanitize_textarea_field($_POST['prompt']),
        ];
        
        $template_id = sanitize_title($_POST['name']);
        $result = $template_manager->save_template($template_id, $data);
        
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>Template saved!</p></div>';
        }
    }
    
    // Get template for editing
    $template = [];
    if (!empty($_GET['edit'])) {
        $templates = $template_manager->get_templates();
        $template = $templates[sanitize_text_field($_GET['edit'])] ?? [];
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
            <p class="description">Use <code>{keywords}</code> as a placeholder for dynamic content.</p>
        </div>

        <button type="submit" name="submit_template" class="button button-primary">
            <?php echo empty($_GET['edit']) ? 'Create Template' : 'Update Template'; ?>
        </button>
        
        <?php if (!empty($_GET['edit'])): ?>
            <a href="?page=ai-content-templates" class="button">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>Saved Templates</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Template Name</th>
                <th>Prompt Preview</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($template_manager->get_templates() as $id => $tpl): ?>
                <tr>
                    <td><?php echo esc_html($tpl['name']); ?></td>
                    <td><?php echo esc_html(substr($tpl['prompt'], 0, 50)); ?>...</td>
                    <td>
                        <a href="?page=ai-content-templates&edit=<?php echo esc_attr($id); ?>" 
                           class="button button-small">
                            Edit
                        </a>
                        <a href="<?php echo wp_nonce_url('?page=ai-content-templates&delete=' . $id, 'delete_template'); ?>" 
                           class="button button-small button-danger" 
                           onclick="return confirm('Are you sure you want to delete this template?')">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>