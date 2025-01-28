<?php
/*
Plugin Name: AI Content Generator
Description: Generate SEO content using AI (DeepSeek). Includes templates, scheduling, and keyword management.
Version: 1.2.1
Author: SerbaWP
*/

if (!defined('ABSPATH')) exit;

// Constants
define('AI_CONTENT_PATH', plugin_dir_path(__FILE__));
define('AI_CONTENT_URL', plugin_dir_url(__FILE__));

// Core classes
require_once AI_CONTENT_PATH . 'includes/class-api-handler.php';
require_once AI_CONTENT_PATH . 'includes/class-template-manager.php';
require_once AI_CONTENT_PATH . 'includes/class-keyword-manager.php';
require_once AI_CONTENT_PATH . 'includes/class-scheduler.php';

class AI_Content_Plugin {
  private static $instance;
  
  public static function init() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }
  
  private function __construct() {
    // Admin menu
    add_action('admin_menu', [$this, 'admin_menu']);
    
    // Admin styles
    add_action('admin_enqueue_scripts', [$this, 'admin_styles']);
    
    // Activation hooks
    register_activation_hook(__FILE__, ['AI_Content_Scheduler', 'activate']);
    register_deactivation_hook(__FILE__, ['AI_Content_Scheduler', 'deactivate']);
  }
  
  public function admin_menu() {
    add_menu_page(
      'AI Content',
      'AI Content',
      'manage_options',
      'ai-content',
      [$this, 'settings_page'],
      'dashicons-edit'
    );
    
    add_submenu_page(
      'ai-content',
      'Generate Content',
      'Generate Content',
      'manage_options',
      'ai-content',
      [$this, 'settings_page']
    );
    
    add_submenu_page(
      'ai-content',
      'Content Templates',
      'Content Templates',
      'manage_options',
      'ai-content-templates',
      [$this, 'template_editor']
    );
    
    add_submenu_page(
      'ai-content',
      'Keyword Templates',
      'Keyword Templates',
      'manage_options',
      'ai-content-keywords',
      [$this, 'keyword_templates']
    );
    
    add_submenu_page(
      'ai-content',
      'API Settings',
      'API Settings',
      'manage_options',
      'ai-content-api',
      [$this, 'api_settings']
    );
  }
  
  public function settings_page() { include AI_CONTENT_PATH . 'admin/settings-page.php'; }
  public function template_editor() { include AI_CONTENT_PATH . 'admin/template-editor.php'; }
  public function keyword_templates() { include AI_CONTENT_PATH . 'admin/keyword-templates.php'; }
  public function api_settings() { include AI_CONTENT_PATH . 'admin/api-settings.php'; }
  
  public function admin_styles() {
    wp_enqueue_style('ai-content-admin', AI_CONTENT_URL . 'assets/css/admin.css');
  }
}

// Initialize plugin
AI_Content_Plugin::init();