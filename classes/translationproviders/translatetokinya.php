<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package filter_translations
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

namespace filter_translations\translationproviders;

use admin_setting_configcheckbox;
use admin_setting_configtext;
use curl;
use DOMDocument;
use DOMXPath;
use filter_translations\translation;
use moodle_url;

/**
 * Translation provider to fetch and then retain translations from Translate to Kinya API.
 */
class translatetokinya extends translationprovider
{
    /**
     * If translate to kinya api is enabled and configured return config, else return false.
     *
     * @return false|mixed|object|string|null
     * @throws \dml_exception
     */
    private static function config()
    {
        static $config = null;

        if (!isset($config)) {
            $config = get_config('filter_translations');
            // print_r($config);

            /* if (!empty($config->google_backoffonerror) && $config->google_backoffonerror_time < time() - HOURSECS) {
                $config->google_backoffonerror = false;
                set_config('google_backoffonerror', false, 'filter_translations');
                $cache = \filter_translations::cache();
                $cache->purge();
            } */

            if (empty($config->languages)) {
                $config = false;
            }
        }
        // print_r($config);

        return $config;
    }

    /**
     * Get a piece of text translated into a specific language.
     * The language of the source text is auto-detected by Google.
     *
     * Either the translated text or if there is an error start backing off from the API and return null.
     *
     * @param $text
     * @param $targetlanguage
     * @return string|null
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function generate_translation($text, $targetlanguage)
    {
        $config = self::config();
        // print_r(['target' => $targetlanguage]);

        if (empty($config)) {
            return null;
        }

        global $CFG;
        require_once($CFG->libdir . "/filelib.php");

        $targetlanguage = str_replace('_wp', '', $targetlanguage);
        $curl = new curl();

        $curl->setHeader(array('Content-Type: application/json'));

        // Look for any base64 encoded files, create an md5 of their content,
        // use the md5 as a placeholder while we send the text to translate to kinya api.
        $base64s = [];
        if (strpos($text, 'base64') !== false) {
            $text = preg_replace_callback(
                '/(data:[^;]+\/[^;]+;base64)([^"]+)/i',
                function ($m) use (&$base64s) {
                    $md5 = md5($m[2]);
                    $base64s[$md5] = $m[2];

                    return $m[1] . $md5;
                },
                $text
            );
        }

        $texts = [];

        if (strstr($text, '.')) {
            $texts = explode('.', $text);
            $texts = $this->remove_empty_text_strings($texts);
            // print_r($texts);
        }

        // echo($text);
        //Make a new DomDocument object.
        $dom = new DOMDocument;
        //Load the html into the object.
        $dom->loadHTML($text);
        //Discard white space.
        $dom->preserveWhiteSpace = false;

        // echo "<script>console.log('$text')</script>";

        // print_r($texts);
        /* $xpath = new DOMXPath($dom);
        if (count($xpath->document->childNodes) > 1) {
            // print_r($xpath->document->childNodes);
            foreach ($xpath->document->childNodes as $key => $child) {
                foreach ($child->childNodes as $c) {
                    if (count($c->childNodes) > 1) {
                        foreach ($c->childNodes as $a) {
                            $texts[] = $a->nodeValue;
                        }
                    }
                }
            };
        } */

        // print_r($texts);

        // $url = new moodle_url($config->google_apiendpoint, ['key' => $config->google_apikey]);
        if (count($texts) > 0) {
            $url = new moodle_url("https://nmt-api.umuganda.digital/api/v1/translate/batch");
        } else {
            $url = new moodle_url("https://nmt-api.umuganda.digital/api/v1/translate/");
        }

        try {
            // print_r($targetlanguage);
            $params = [
                'src' => current_language(),
                'tgt' => $targetlanguage,
                'alt' => 'education',
                'use_multi' => 'multi'
            ];
            // print_r($params);
            if (count($texts) > 0) {
                $params['texts'] = $texts;
            } else {
                $params['text'] = $text;
            }
            $resp = $curl->post($url->out(false), json_encode($params));
        } catch (\Exception $ex) {
            error_log("Error calling Translate to Kinya API: \n" . $ex->getMessage());
            $this->backoff();
            return null;
        }

        $info = $curl->get_info();
        if ($info['http_code'] != 200) {
            error_log("Error calling Translate to Kinya API: \n" . $info['http_code'] . "\nFailed Text:\n" . substr($text, 0, 1000) . "\n" . print_r($curl->get_raw_response(), true));
            $this->backoff();
            return null;
        }

        $resp = json_decode($resp);

        if (empty($resp->translation)) {
            return null;
        }

        if (is_array($resp->translation)) {
            $text = implode('.', $resp->translation);
        } else {
            $text = $resp->translation;
        }

        // print_r($text);

        // Swap the base 64 encoded images back in.
        foreach ($base64s as $md5 => $base64) {
            $text = str_replace($md5, $base64, $text);
        }

        // print_r(['text' => $text]);

        return $text;
    }

    /**
     * Back off from API - used when errors are getting returned.
     *
     * @return void
     */
    private function backoff()
    {
        set_config('google_backoffonerror', true, 'filter_translations');
        set_config('google_backoffonerror_time', time(), 'filter_translations');
    }

    private function remove_empty_text_strings(array $texts): array
    {
        foreach ($texts as $index => $text) {
            if (strlen(trim($text)) == 0) {
                unset($texts[$index]);
            }
        }
        return $texts;
    }
}
