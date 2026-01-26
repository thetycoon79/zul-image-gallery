<?php
/**
 * Plugin capabilities management
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery;

class Capabilities
{
    public const MANAGE = 'zul_gallery_manage';
    public const VIEW_ADMIN = 'zul_gallery_view_admin';

    public static function getAll(): array
    {
        return [
            self::MANAGE,
            self::VIEW_ADMIN,
        ];
    }

    public static function userCanManage(): bool
    {
        return current_user_can(self::MANAGE);
    }

    public static function userCanViewAdmin(): bool
    {
        return current_user_can(self::VIEW_ADMIN);
    }

    public static function addToRole(string $roleName): void
    {
        $role = get_role($roleName);
        if (!$role) {
            return;
        }

        foreach (self::getAll() as $cap) {
            $role->add_cap($cap);
        }
    }

    public static function removeFromRole(string $roleName): void
    {
        $role = get_role($roleName);
        if (!$role) {
            return;
        }

        foreach (self::getAll() as $cap) {
            $role->remove_cap($cap);
        }
    }
}
