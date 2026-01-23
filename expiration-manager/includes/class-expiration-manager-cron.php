<?php

class Expiration_Manager_Cron {
    public static function check_expirations() {
      // Check if expiration is enabled
    $enabled = (bool) get_option('expiration_manager_enabled', true);
    if (!$enabled) {
      return;
    }

      // AG: Get current time
    $now = current_time('timestamp');


    // AG: Query expired posts that haven't been processed yet
    $expired = new WP_Query([
      'post_type' => ['post', 'page'],
      'post_status' => 'publish',
      'posts_per_page' => 50,
      'fields' => 'ids',
      'meta_query' => [
        'relation' => 'AND',
        [
          'key' => '_em_expiration_date',
          'value' => $now,
          'compare' => '<=',
          'type' => 'NUMERIC',
        ],
        // AG: Exclude posts that have already been processed
        [
          'key' => '_em_expiration_processed',
          'compare' => 'NOT EXISTS',
        ],
      ],
    ]);

      // AG: Loop through each expired post
    foreach ($expired->posts as $post_id) {
      // AG: Get the action for this post (notice or unpublish)
      $action = get_post_meta($post_id, '_em_expiration_action', true);

      // AG: If no action is set, default to 'notice'
      if (empty($action)) {
        $action = 'notice';
      }

      // AG: Perform the action based on what's configured
      if ($action === 'unpublish') {
        // AG: Action 1 - Unpublish the post by setting status to draft
        wp_update_post([
          'ID' => $post_id,
          'post_status' => 'draft',
        ]);

        // AG: Add a meta flag to track that this post was unpublished by cron
        update_post_meta($post_id, '_em_was_unpublished_by_cron', current_time('mysql'));

      } elseif ($action === 'notice') {
        // AG: Action 2 - Show notice (prepare the notice text)
        $custom_notice = get_post_meta($post_id, '_em_expiration_notice', true);

        // AG: If no custom notice, use the global default
        if (empty($custom_notice)) {
          $custom_notice = get_option('expiration_manager_default_notice', 'This content is outdated.');
        }

        // AG: Store the notice that will be displayed on frontend
        update_post_meta($post_id, '_em_notice_active', $custom_notice);

        // AG: Mark that the notice has been processed
        update_post_meta($post_id, '_em_notice_processed', current_time('mysql'));
      }

      // AG: Mark this post as processed so it doesn't get handled again on next cron run
      update_post_meta($post_id, '_em_expiration_processed', current_time('mysql'));
    }
    }
}
