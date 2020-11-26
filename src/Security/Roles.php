<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 24.10.20
 */

namespace App\Security;

interface Roles
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_OV_MEMBER = 'ROLE_OV';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const LOGGED_IN = 'IS_AUTHENTICATED_FULLY';
}