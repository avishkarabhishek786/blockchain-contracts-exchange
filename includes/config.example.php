<?php
/*Please make a config.php file with correct values same like below*/
/** NOTE: The session values must match DB values of users table */

/*Change these values according to your configurations*/

define("DB_HOST", "localhost");
define("DB_NAME", "YOUR-DB");
define("DB_USER", "root");
define("DB_PASS", "");
define("MESSAGE_DATABASE_ERROR", "Failed to connect to database.");

define("EMAIL_USE_SMTP", true);
define("EMAIL_SMTP_HOST", "YOUR HOSTING");
define("EMAIL_SMTP_AUTH", true);
define("EMAIL_SMTP_USERNAME", "USERNAME");
define("EMAIL_SMTP_PASSWORD", "PASSWORD");
define("EMAIL_SMTP_PORT", 587);  //587
define("EMAIL_SMTP_ENCRYPTION", "ssl");

/*EMAILS*/
define("RT", "");
define("RM", "");
define("PI", "");
define("AB", "");
define("RMGM", "");
define("FINANCE", "");

/*YOUR CRYPTOCURRENCIES*/
define("RMT", "RMT");
define("REBC", "REBC");
define("IBC", "IBC");
define("FLOBC", "FLOBC");
define("RSBC", "RSBC");
define("INTBC", "INTBC");
define("IHWBC", "IHWBC");
define("DBC", "DBC");
define("RBC", "RBC");
define("PBC", "PBC");
define("ARTBC", "ARTBC");

define("EMAIL_SENDER_NAME", "Ranchi Mall");
//define("EMAIL_SUBJECT", "Ranchi Mall Fund Transfer Request.");
//define("EMAIL_SUBJECT_RTM_TRANSFER", "Ranchi Mall RMT Transfer Request.");
//define("EMAIL_SUBJECT_BTC_TO_CASH", "Ranchi Mall BTC To CASH exchange Request.");

/*YOUR TABLES IN DB*/
define("TOP_BUYS_TABLE", "BUYS TABLE");
define("TOP_SELLS_TABLE", "SELLS TABLE");
define("USERS_TABLE", "USER TABLE");
define("CREDITS_TABLE", "BALANCE TABLE");
define("ORDERS_TABLE", "ORDERS TABLE");
define("TX_TABLE", "TRANSACTION TABLE");

/*FACEBOOK DETAILS*/
define("APP_ID", 'YOUR FB APP ID');
define("APP_SECRET", 'YOUR FB APP PASSWORD');

/*ADMIN DETAILS*/
define("ADMIN_FB_ID", "ADMIN FB APP ID");
define("ADMIN_ID", "ADMIN ID NUMBER IN USER TABLE");
define("ADMIN_UNAME", "ADMIN USERNAME IN USER TABLE IN DB");