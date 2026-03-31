<?php
$to = "testvd@bvcline.com.sg";
$subject = "Test mail";
$message = "Mail is working!";
$headers = "From: k6tkeyan@gmail.com";

if(mail($to, $subject, $message, $headers)){
    echo "Mail sent successfully!";
} else {
    echo "Mail failed!";
}
?>