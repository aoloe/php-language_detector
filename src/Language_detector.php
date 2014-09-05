<?php
/**
 * if the request language is defined, use it.
 * else, if the language cookie is defined, use it.
 * else get the language from the browser settings.
 *
 * if the current language is different than the browser setting, store it in the
 * cookie.
 */

namespace Aoloe;

class Language_detector {
    private $valid = array();
    public function set_valid($language) {$this->valid = $language;}
    private function is_valid($language) {return empty($this->valid) || in_array($language, $this->valid);}
    private $default = "en";
    public function set_default($language) {$this->default = $language;}
    private $current = null;
    public function get() {
        if (!isset($this->current)) {
            $this->read();
        }
        return $this->current;
    }
    private $request_language = null;
    public function set_request_language($language) { if ($this->is_valid($language)) $this->request_language = $language; }
    private $cookie = null; // the cookie manager
    public function set_cookie_manager($cookie) {$this->cookie = $cookie;}
    public function read() {
        $cookie_language = null;
        if ($this->cookie->is('language')) {
            $cookie_language = $this->cookie->get('language');
            $cookie_language = $this->is_valid($cookie_language) ? $cookie_language : null;
        }
        $browser_language = null;
        // [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.8,de;q=0.6,fr;q=0.4
        // debug('HTTP_ACCEPT_LANGUAGE', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $item) {
                $browser_language = array_slice(explode(';', $item), 0 , 1);
                $browser_language = reset($browser_language);
                if ($this->is_valid($browser_language)) {
                    break;
                } else {
                    $browser_language = null;
                }
            }
        }

        if (isset($this->request_language)) {
            $this->current = $this->request_language;
        } elseif (isset($cookie_language)) {
            $this->current = $cookie_language;
        } elseif (isset($browser_language)) {
            $this->current = $browser_language;
        }

        if (isset($this->current)) {
            if (is_null($cookie_language)) {
                if ($this->current != $browser_language) {
                    $this->cookie->set('language', $this->current);
                }
            } elseif ($cookie_language != $this->current) {
                if ($this->current == $browser_language) {
                    $this->cookie->delete('language');
                } else {
                    $this->cookie->set('language', $this->current);
                }
            }
        } else {
            $this->current = $this->default;
        }
        // debug('current', $this->current);
    }
}
