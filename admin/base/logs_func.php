<?php

function action_log($user_id, $action, $conn)
{

    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, action) VALUES (?,?)");
    $stmt->bind_param('is', $user_id, $action);
    $stmt->execute();
    $stmt->close();

}

?>