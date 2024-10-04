<?php

require __DIR__ . '/../lib/autoload.php';

head("Subscribe to a group");
echo '<section class="content">';

// No error found yet
$error = "";

// Check email address
if (
    empty($_POST['email']) ||
    $_POST['email'] == 'user@example.com' ||
    $_POST['email'] == 'fake@from.net' ||
    !is_emailable_address($_POST['email'])
) {
    $error = "You forgot to specify an email address to be added to the list, or specified an invalid address." .
             "<br>Please go back and try again.";

// Check if any mailing list was selected
} elseif (empty($_POST['group'])) {
    $error = "You need to select a group subscribe to." .
             "<br>Please go back and try again.";

// Check if type of subscription makes sense
} elseif (!in_array($_POST['type'], [ '', 'digest', 'nomail' ])) {
    $error = "The subscription type you specified is not valid." .
             "<br>Please go back and try again.";

// Seems to be a valid email address
} else {
    $remote_addr = i2c_realip();
    $maillist = get_list_address($_POST['group']);
    if ($_POST['type'] != '') {
        $maillist .= '-' . $_POST['type'];
    }

    if ($maillist) {
        // Get in contact with main server to subscribe the user
        $result = posttohost(
            "https://main.php.net/entry/subscribe.php",
            [
                "request" => 'subscribe',
                "email" => $_POST['email'],
                "maillist" => $maillist,
                "remoteip" => $remote_addr,
                "referer"
                    => 'http' . (@$_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] .
                    '/' . $_POST['group'],
            ],
        );

        // Provide error if unable to subscribe
        if ($result) {
            $error = "We were unable to subscribe you due to some technical problems.<br>" .
                     "Please try again later.";
        }
    } else {
            $error = "That's not a group that we can handle.<br>" .
                     "Please try again later.";
    }
}

// Give error information or success report
if (!empty($error)) {
    echo "<p class=\"formerror\">$error</p>";
} else {
    ?>
        <p>
            A request has been entered into the mailing list processing queue.
            You should receive an email at <?php echo clean($_POST['email']); ?> shortly
            describing how to complete your request.
        </p>
    <?php
}
echo '</section>';
foot();
