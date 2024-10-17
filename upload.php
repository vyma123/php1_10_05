<?php
$target_dir = "uploads/";
$uploadOk = 1;

// Kết nối đến cơ sở dữ liệu
$servername = "localhost"; // Thay đổi nếu cần
$username = "root"; // Thay đổi theo thông tin đăng nhập của bạn
$password = ""; // Thay đổi nếu cần
$dbname = "images"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kiểm tra nếu form đã được submit
if (isset($_POST["submit"])) {
    $overallUploadOk = 1;

    // Xử lý kiểm tra ảnh đơn lẻ
    if (isset($_FILES["singleFile"]) && $_FILES["singleFile"]["error"] == 0) {
        $single_target_file = $target_dir . basename($_FILES["singleFile"]["name"]);
        $single_imageFileType = strtolower(pathinfo($single_target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["singleFile"]["tmp_name"]);
        if ($check !== false) {
            echo "Single file is an image - " . $check["mime"] . ".<br>";
        } else {
            echo "Single file is not an image.<br>";
            $overallUploadOk = 0;
        }

        if ($_FILES["singleFile"]["size"] > 500000) {
            echo "Sorry, single file is too large.<br>";
            $overallUploadOk = 0;
        }

        if (!in_array($single_imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed for the single file.<br>";
            $overallUploadOk = 0;
        }
    } else {
        echo "No single file was uploaded or an error occurred.<br>";
        $overallUploadOk = 0;
    }

    // Xử lý kiểm tra nhiều ảnh
    if (isset($_FILES["multipleFiles"])) {
        $total_files = count($_FILES["multipleFiles"]["name"]);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES["multipleFiles"]["error"][$i] == 0) {
                $multiple_target_file = $target_dir . basename($_FILES["multipleFiles"]["name"][$i]);
                $multiple_imageFileType = strtolower(pathinfo($multiple_target_file, PATHINFO_EXTENSION));

                $check = getimagesize($_FILES["multipleFiles"]["tmp_name"][$i]);
                if ($check !== false) {
                    echo "File " . ($i + 1) . " is an image - " . $check["mime"] . ".<br>";
                } else {
                    echo "File " . ($i + 1) . " is not an image.<br>";
                    $overallUploadOk = 0;
                }

                if ($_FILES["multipleFiles"]["size"][$i] > 500000) {
                    echo "Sorry, file " . ($i + 1) . " is too large.<br>";
                    $overallUploadOk = 0;
                }

                if (!in_array($multiple_imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed for file " . ($i + 1) . ".<br>";
                    $overallUploadOk = 0;
                }
            } else {
                echo "Error with file " . ($i + 1) . ".<br>";
                $overallUploadOk = 0;
            }
        }
    } else {
        echo "No multiple files were uploaded.<br>";
        $overallUploadOk = 0;
    }

    // Nếu không có lỗi nào xảy ra, tiến hành upload
    if ($overallUploadOk == 1) {
        // Upload file đơn lẻ
        if (move_uploaded_file($_FILES["singleFile"]["tmp_name"], $single_target_file)) {
            echo "The single file " . htmlspecialchars(basename($_FILES["singleFile"]["name"])) . " has been uploaded.<br>";

            // Thêm vào cơ sở dữ liệu
            $singleFileName = $_FILES["singleFile"]["name"];
            $sql = "INSERT INTO imagess (singleFile) VALUES ('$singleFileName')";
            if ($conn->query($sql) === TRUE) {
                echo "Record for single file created successfully.<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading the single file.<br>";
        }

        // Upload nhiều ảnh
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES["multipleFiles"]["error"][$i] == 0) {
                $multiple_target_file = $target_dir . basename($_FILES["multipleFiles"]["name"][$i]);
                if (move_uploaded_file($_FILES["multipleFiles"]["tmp_name"][$i], $multiple_target_file)) {
                    echo "The file " . htmlspecialchars(basename($_FILES["multipleFiles"]["name"][$i])) . " has been uploaded.<br>";

                    // Thêm vào cơ sở dữ liệu
                    $multipleFileName = $_FILES["multipleFiles"]["name"][$i];
                    $sql = "INSERT INTO imagess (multipleFiles) VALUES ('$multipleFileName')";
                    if ($conn->query($sql) === TRUE) {
                        echo "Record for file " . ($i + 1) . " created successfully.<br>";
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                } else {
                    echo "Sorry, there was an error uploading file " . ($i + 1) . ".<br>";
                }
            }
        }
    } else {
        echo "Upload failed due to errors in one or more files.";
    }

    // Đóng kết nối
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images</title>
</head>
<body>
<form action="upload.php" method="post" enctype="multipart/form-data">
    Select a single image to upload:
    <input type="file" name="singleFile" id="singleFile"><br><br>

    Select multiple images to upload:
    <input type="file" name="multipleFiles[]" id="multipleFiles" multiple><br><br>

    <input type="submit" value="Upload Images" name="submit">
</form>
</body>
</html>
