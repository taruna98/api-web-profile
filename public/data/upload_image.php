<?php
$uploadDirectory = 'C:\xampp\htdocs\web_profile_v1\assets\img\/'; // replace with your desired destination folder on Server B

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['portfolio_file_1']) && $_FILES['portfolio_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['portfolio_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['portfolio_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['portfolio_file_2']) && $_FILES['portfolio_file_2']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['portfolio_file_2']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['portfolio_file_2']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['portfolio_file_3']) && $_FILES['portfolio_file_3']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['portfolio_file_3']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['portfolio_file_3']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['portfolio_file_4']) && $_FILES['portfolio_file_4']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['portfolio_file_4']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['portfolio_file_4']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['portfolio_file_5']) && $_FILES['portfolio_file_5']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['portfolio_file_5']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['portfolio_file_5']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['article_file_1']) && $_FILES['article_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['article_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['article_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['profile_file_1']) && $_FILES['profile_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['profile_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['profile_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['background_home_file_1']) && $_FILES['background_home_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['background_home_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['background_home_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['background_service_file_1']) && $_FILES['background_service_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['background_service_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['background_service_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['background_article_file_1']) && $_FILES['background_article_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['background_article_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['background_article_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    } else if (isset($_FILES['background_detail_portfolio_file_1']) && $_FILES['background_detail_portfolio_file_1']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['background_detail_portfolio_file_1']['tmp_name']; // path temporary file upload
        $destinationPath = $uploadDirectory . $_FILES['background_detail_portfolio_file_1']['name']; // path file destination
        // move file uploaded to destination directory
        if (!move_uploaded_file($tempPath, $destinationPath)) {
            echo 'failed';
            die();
        }
    }
}