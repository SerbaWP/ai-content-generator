<?php
class AI_Content_API_Handler {
  public function generate_content($prompt) {
    $api_key = get_option('deepseek_api_key');
    if (!$api_key) return new WP_Error('api_key_missing', 'DeepSeek API key not found.');

    $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', [
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'deepseek-r1',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.7,
      ]),
      'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
      error_log('[AI Content Generator] DeepSeek API Error: ' . $response->get_error_message());
      return $response;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['choices'][0]['message']['content'] ?? '';
  }
}