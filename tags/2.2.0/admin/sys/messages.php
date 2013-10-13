<?php
/**
 * CMS error messages while logging in.
 * @global array $_loginMsg
 * @date 27/March/2009
 * @rev 20/December/2009
 */
$_loginMsg = array(
                    "NOT_LOGGED"  =>  "Not logged in",
                    "AUTH_FAILED" =>  "Authentication failed",
                    "NOT_ALLOWED" =>  "Not allowed",
                    "RESET_PASS"  =>  "Password reset",
                    "MAIL_SENT"   =>  "Email sent",
                    "MAIL_ERROR"  =>  "PHP Mailer error",
                    "USER_ERROR"  =>  "User not found",
                    "UNDEFINED"   =>  "Undefined error"
                  );

/**
 * Types of CMS notifications.
 * A CSS class will be applied to each message type.
 * @global array $_displayType
 * @date 20/December/2009
 */
$_displayType = array(
                        "SUCCESS" =>  "success",
                        "WARNING" =>  "warning",
                        "ERROR"   =>  "error"
                     );


/**
 * Types of CMS text messages.
 * @global array $_notifyMsg
 * @date 20/December/2009
 */
$_notifyMsg = array(
                      "SAVED"     =>  "Data were processed successfully.",
                      "ERROR"     =>  "An error occurred while processing your request.",
                      "NOSCRIPT"  =>  "Please enable JavaScript in order to work on this section."
                   );
?>