<?php
$uploadDirectory = 'C:\xampp\htdocs\web_profile_v1\assets\file\/'; // replace with your desired destination folder on Server B

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_cv_file_1']) && $_FILES['profile_cv_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['profile_cv_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['profile_cv_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    }
}