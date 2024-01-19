<?php


namespace App;


class Constants
{
    const CURRENT_USER_ID_KEY = "___current_user_id";
    const CURRENT_EMAIL_KEY = "___current_email_key";
    const CURRENT_FIRST_NAME_KEY = "___current_first_name_key";
    const CURRENT_LAST_NAME_KEY = "___current_last_name_key";
    const CURRENT_ROLE_ID_KEY = "___current_role_id_key";
    const CURRENT_ROLE_NAME_KEY = "___current_role_name_key";
    const CURRENT_PROFILE_STATUS_KEY = "___current_profile_status_key";
    const CURRENT_PROFILE_KEY = "___current_profile_key";
    const CURRENT_FRIENDLY_NAME_KEY = "___current_friendly_name_key";
    const REFRESH_TOKEN_UUID_KEY = "___refresh_token_uuid_key";
    const OPEN = 0;
    const UPCOMING = 1;
    const COMPLETED = 2;
    const CANCELLED = 3;
    const ONGOING = 4;
    const ADMIN_USER = 1;
    const WEB_USER = 2;
    const MOBILE_USER = 3;
    const MSG_CANCELLED = 1;
    const MSG_CLOCK_IN = 2;
    const MSG_CLOCK_OUT = 3;
    const APPROVED_ACCOUNT = 4;
    const DENIED_ACCOUNT = 5;
    const ACCEPTED = 1;
    const DENIED = 0;
    const ACTIVE = 1;
    const INACTIVE = 0;
    const REJECTED = 2;
    const DELETED = 2;
    const TEST_EMAIL = "--test@test.com__";
    const TEST_FIRST_NAME = "--first name__";
    const TEST_LAST_NAME = "--last name__";
    const TEST_PHONE = "--0101092345__";
    const ONE_HOUR = 3600;
    const TWO_HOUR = 7200;
    const USER_NOT_ACTIVE = "Account is deactivated. Please contact your admin.";
    const USER_EMAIL_NOT_VERIFIED = "User email not verified";
    const USER_NOT_VERIFIED = "User is being reviewed by Admin";
    const TAKE = 20;
    const DEFAULT_PAGE = 1;
}
