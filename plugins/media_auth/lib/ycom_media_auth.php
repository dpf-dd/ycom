<?php

declare(strict_types=1);

class rex_ycom_media_auth extends rex_yform_manager_dataset
{
    /** @var string[] */
    public static array $perms = [
        '0' => 'translate:ycom_perm_all',
        '1' => 'translate:ycom_perm_only_logged_in',
    ];

    public static function checkPerm(rex_media_manager $media_manager): bool
    {
        // check if original media_path
        $media = $media_manager->getMedia();
        if (rex_path::media($media->getMediaFilename()) != $media_manager->getMedia()->getMediaPath()) {
            return true;
        }

        // is backend login
        if (rex_backend_login::hasSession()) {
            return true;
        }

        // is rex_media
        $rex_media = rex_media::get($media->getMediaFilename());
        if (!$rex_media) {
            return false;
        }

        return self::checkFrontendPerm($rex_media);
    }

    public static function checkFrontendPerm(rex_media $rex_media): bool
    {
        $authType = (int) $rex_media->getValue('ycom_auth_type');
        if (1 != $authType) {
            return true;
        }

        // from here only logged in Users

        $me = rex_ycom_user::getMe();

        if (!$me) {
            return false;
        }

        $group = rex_plugin::get('ycom', 'group')->isAvailable();

        if ($group) {
            $groupType = (int) $rex_media->getValue('ycom_group_type');

            $groups = [];
            if ('' != $rex_media->getValue('ycom_groups')) {
                $groups = explode(',', (string) $rex_media->getValue('ycom_groups'));
            }

            $userGroups = $me->getValue('ycom_groups');
            if (empty($userGroups)) {
                $userGroups = [];
            } else {
                $userGroups = explode(',', $userGroups);
            }

            return rex_ycom_group::hasGroupPerm($groupType, $groups, $userGroups);
        }

        return true;
    }
}
