<?php

namespace App\Http\Traits;

use App\Mail\UserEmailChangeNotificationMail;
use App\Mail\UserPhoneChangeNotificationMail;
use Mail;

/**
 * Trait ResetUsersPassword.
 *
 * @package App\Http\Traits
 */
trait NotifyUserContactInfoChange
{
    /**
     * Notify user if his email has been changed.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $oldEmail
     * @param string $newEmail
     */
    public function sendEmailChangeNotification($firstName, $lastName, $oldEmail, $newEmail)
    {
        Mail::to($oldEmail)->queue(
            new UserEmailChangeNotificationMail($firstName, $lastName, $newEmail)
        );
    }

    /**
     * Notify user if his email has been changed.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $userEmail
     * @param string $safePhoneNumber
     */
    public function sendPhoneChangeNotification($firstName, $lastName, $userEmail, $safePhoneNumber)
    {
        Mail::to($userEmail)->queue(
            new UserPhoneChangeNotificationMail($firstName, $lastName, $userEmail, $safePhoneNumber)
        );
    }
}
