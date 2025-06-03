<?php

namespace App\Helpers;

class ResponseCode
{
  public static $CREATED = 'Created';
    public static $UPDATED = 'Updated';
    public static $DELETED = 'Deleted';
    public static $SUCCESS = 'Success';
    public static $BAD_REQUEST = 'BadRequest';
    public static $UNAUTHORIZED = 'Unauthorized';
    public static $ACCESS_DENIED = 'AccessDenied';
    public static $NOT_FOUND = 'NotFound';
    public static $CONFLICT = 'Conflict';
    public static $INTERNAL_ERROR = 'InternalError';
    public static $TOKEN_INVALID = 'TokenInvalid';
    public static $TOKEN_ABSENT = 'TokenAbsent';
    public static $TOKEN_EXPIRED = 'TokenExpired';
    public static $USER_NOT_FOUND = 'UserNotFound';
    public static $LOGIN_EMAIL_NOT_FOUND = 'EmailNotFound';
    public static $VALIDATE = 'Validate';
    public static $USER_DE_ACTIVE = 'UserNotActive';
    public static $INCORRECT_PASSWORD = 'IncorrectPassword';
    public static $CANCEL_BOOKING_NOT_ACCEPT = 'CancelBookingNotAccept';
    public static $CUSTOMER_BOOKING = 'CustomerHasBooking';
    public static $CUSTOMER_NOT_ACTIVE = 'CustomerNotActive';
    public static $MQTT_PUBLISH_FAILED = 'MqttPublishFailed';
    public static $OLD_PASSWORD_INVALID = 'OldPasswordInvalid';
}
