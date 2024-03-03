<?php
/*
Plugin Name: My Enhanced Poll Plugin
Description: An enhanced plugin to create and manage polls, and serve their content via REST API.
Version: 1.0
Author: Your Name
*/

// Create a custom post type for Polls
function create_poll_post_type() {
    register_post_type('poll',
        array(
            'labels' => array(
                'name' => __('Polls'),
                'singular_name' => __('Poll')
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true, // Enable Gutenberg editor
            'supports' => array('title', 'custom-fields'), // Enable title and custom fields for poll answers
        )
    );
}
add_action('init', 'create_poll_post_type');

// Register REST API endpoints for managing polls
function register_poll_routes() {
    register_rest_route('my-poll/v1', '/polls', array(
        'methods' => 'GET',
        'callback' => 'get_polls_data',
    ));
    register_rest_route('my-poll/v1', '/poll/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'update_poll_vote',
        'args' => array(
            'answer' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_string($param);
                }
            ),
        ),
    ));
}
add_action('rest_api_init', 'register_poll_routes');

// Get poll data
function get_polls_data() {
    $args = array(
        'post_type' => 'poll',
        'posts_per_page' => -1, // Get all polls
    );
    $posts = get_posts($args);
    $polls = array();

    foreach ($posts as $post) {
        $poll_answers = get_post_meta($post->ID, 'poll_answers', true);
        $polls[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'answers' => json_decode($poll_answers, true),
        );
    }

    return new WP_REST_Response($polls, 200);
}

// Update poll vote
function update_poll_vote($request) {
    $poll_id = $request['id'];
    $answer = $request['answer'];
    $poll_answers = get_post_meta($poll_id, 'poll_answers', true);
    $answers = json_decode($poll_answers, true);

    if (isset($answers[$answer])) {
        $answers[$answer] += 1;
        update_post_meta($poll_id, 'poll_answers', json_encode($answers));
        return new WP_REST_Response(array('success' => true, 'message' => 'Vote updated.'), 200);
    }

    return new WP_REST_Response(array('success' => false, 'message' => 'Answer not found.'), 404);
}
