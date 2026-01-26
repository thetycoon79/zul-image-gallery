<?php
/**
 * Nonce handling utility
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Support;

class Nonce
{
    private string $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function create(): string
    {
        return wp_create_nonce($this->action);
    }

    public function field(string $name = '_wpnonce', bool $referer = true): string
    {
        return wp_nonce_field($this->action, $name, $referer, false);
    }

    public function url(string $url): string
    {
        return wp_nonce_url($url, $this->action);
    }

    public function verify(?string $nonce, string $name = '_wpnonce'): bool
    {
        if ($nonce === null) {
            $nonce = $_REQUEST[$name] ?? '';
        }
        return wp_verify_nonce($nonce, $this->action) !== false;
    }

    public function verifyRequest(string $name = '_wpnonce'): bool
    {
        $nonce = $_REQUEST[$name] ?? '';
        return $this->verify($nonce);
    }

    public static function verifyAjax(string $action, string $name = 'nonce'): bool
    {
        return check_ajax_referer($action, $name, false) !== false;
    }
}
