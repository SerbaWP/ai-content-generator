<?php
class AI_Content_Scheduler {
    public static function activate() {
        $interval = get_option('ai_schedule_interval', 'hourly');
        if (!wp_next_scheduled('ai_content_auto_generate')) {
            wp_schedule_event(time(), $interval, 'ai_content_auto_generate');
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('ai_content_auto_generate');
    }

    public static function update_schedule() {
        self::deactivate();
        self::activate();
    }

    public static function generate_scheduled_content() {
        if (get_option('ai_schedule_mode', 'manual') !== 'auto') return;
        
        $templates = (new AI_Content_Template_Manager())->get_templates();
        $keywords = (new AI_Content_Keyword_Manager())->get_keyword_templates();
        $api = new AI_Content_API_Handler();
        $post_status = get_option('ai_auto_post_status', 'draft');

        foreach ($templates as $template_id => $template) {
            foreach ($keywords as $keyword_id => $keyword_data) {
                $keywords_list = array_map('trim', explode("\n", $keyword_data['keywords']));
                
                foreach ($keywords_list as $keyword) {
                    if (empty($keyword)) continue;
                    
                    $prompt = str_replace('{keywords}', $keyword, $template['prompt']);
                    $content = $api->generate_content($prompt);
                    
                    if (!is_wp_error($content) && !empty($content)) {
                        wp_insert_post([
                            'post_type' => 'post',
                            'post_status' => $post_status,
                            'post_title' => 'Generated: ' . substr($keyword, 0, 50),
                            'post_content' => wp_kses_post($content),
                        ]);
                    }
                }
            }
        }
    }

    // Add custom intervals
    public static function add_cron_intervals($schedules) {
        $schedules['2hours'] = [
            'interval' => 2 * HOUR_IN_SECONDS,
            'display'  => __('Every 2 Hours')
        ];
        $schedules['3hours'] = [
            'interval' => 3 * HOUR_IN_SECONDS,
            'display'  => __('Every 3 Hours')
        ];
        $schedules['6hours'] = [
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => __('Every 6 Hours')
        ];
        $schedules['12hours'] = [
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => __('Every 12 Hours')
        ];
        $schedules['2days'] = [
            'interval' => 2 * DAY_IN_SECONDS,
            'display'  => __('Every 2 Days')
        ];
        $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display'  => __('Weekly')
        ];
        return $schedules;
    }
}
add_filter('cron_schedules', ['AI_Content_Scheduler', 'add_cron_intervals']);
add_action('ai_content_auto_generate', ['AI_Content_Scheduler', 'generate_scheduled_content']);