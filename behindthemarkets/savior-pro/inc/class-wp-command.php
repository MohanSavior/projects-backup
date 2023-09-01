<?php
if ( ! class_exists( 'WP_Webhook_Command' ) && class_exists( 'WP_CLI_Command' ) ) :
    class WP_Webhook_Command extends WP_CLI_Command {
        public function list() 
        {
            $start_time = microtime(true);
            $endpoint = 'https://btmnew.saviormarketing.com/wp-json/recurly/v1/webhookqueuelist';
            $response = wp_remote_get($endpoint);
            $elapsed_time = microtime(true) - $start_time;
            $ticks = intval($elapsed_time * 10);

            // Create a progress bar
            $progress = \WP_CLI\Utils\make_progress_bar('Webhook Queue List', $ticks);

            for ($i = 0; $i < $ticks; $i++) {
                usleep(intval(1000000 / $ticks));
                $progress->tick();
            }

            $progress->finish();
            if (is_wp_error($response)) {
                WP_CLI::error("Error: " . $response->get_error_message());
                return;
            }
            if(isset($response['body']) && $data = json_decode($response['body']) )
            {
                // WP_CLI::line($data->data->no_of_data);
                WP_CLI::success( $data->data->no_of_data." Queues found." );
            }else{
                WP_CLI::error("Error: " . print_r($response['body']));
            }
        }

        public function process($args, $assoc_args) {
            $endpoint = 'https://btmnew.saviormarketing.com/wp-json/recurly/v1/webhookqueue';
            $process_queue = isset($assoc_args['process_queue']) ? $assoc_args['process_queue'] : 2;

            $start_time = microtime(true);
            $response = wp_remote_get($endpoint, array('process_queue' => $process_queue));
            $elapsed_time = microtime(true) - $start_time;
            $ticks = intval($elapsed_time);

            // Create a progress bar
            $progress = \WP_CLI\Utils\make_progress_bar('Queues Process', $ticks);

            // Simulate a task with the progress bar
            for ($i = 0; $i < $ticks; $i++) {
                usleep(intval(1000000 / $ticks)); // Simulate some work
                $progress->tick();
            }

            $progress->finish();
            if (is_wp_error($response)) {
                WP_CLI::error("Error: " . $response->get_error_message());
                return;
            }
            // Display the endpoint, parameter, and elapsed time
            // WP_CLI::line("Endpoint: $endpoint");
            WP_CLI::line("No. of process: $process_queue");
            WP_CLI::line("Elapsed Time: " . round($elapsed_time, 2) . " seconds");

            if(isset($response['body']) && $data = json_decode($response['body']) )
            {
                WP_CLI::success( $data->data->message);
            }else{
                WP_CLI::error("Error: " . print_r($response));
            }
        }
    }
    WP_CLI::add_command( 'webhook', 'WP_Webhook_Command' ); 
endif;