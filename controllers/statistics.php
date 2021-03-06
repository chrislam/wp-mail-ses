<?php

class WP_Mail_SES_Statistics
{

    protected static $instance;

    public static function get_instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new self;
        }

        return static::$instance;
    }

    public function index()
    {
        $WPSES = WP_Mail_SES::get_instance();

        if (!$WPSES->is_statistics_enabled()) {
            throw new Exception('Access denied');
        }

        /* Send Quota */

        try {
            $quota = $WPSES->ses->getSendQuota();
            $quota['SendRemaining'] = $quota['Max24HourSend'] - $quota['SentLast24Hours'];

            if ($quota['Max24HourSend'] > 0) {
                $quota['SendUsage'] = sprintf("%0.3f", $quota['SentLast24Hours'] / $quota['Max24HourSend'] * 100);
            } else {
                $quota['SendUsage'] = 0;
            }
        } catch (Exception $e) {

        }

        /* Send Statistics */
        try {
            $stats = $WPSES->ses->getSendStatistics();
            usort($stats['SendDataPoints'], array($this, 'sort_timestamp'));
        } catch (Exception $e) {

        }

        include __DIR__ . '/../views/statistics.php';
    }

    public function sort_timestamp($a, $b)
    {
        return ($a['Timestamp'] < $b['Timestamp']) ? -1 : 1;
    }
}
