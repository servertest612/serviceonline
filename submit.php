<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $job = $_POST["job"];
    $exp = $_POST["experience"];

    if (!file_exists("uploads")) {
        mkdir("uploads", 0777, true);
    }

    // رفع صورة الوجه
    if (isset($_FILES["id_card_front"]) && $_FILES["id_card_front"]["error"] == 0) {
        $ext1 = pathinfo($_FILES["id_card_front"]["name"], PATHINFO_EXTENSION);
        $frontName = uniqid("card_front_") . "." . $ext1;
        $frontPath = "uploads/" . $frontName;
        move_uploaded_file($_FILES["id_card_front"]["tmp_name"], $frontPath);
    } else {
        die("يجب رفع صورة الوجه للبطاقة!");
    }

    // رفع صورة الظهر
    if (isset($_FILES["id_card_back"]) && $_FILES["id_card_back"]["error"] == 0) {
        $ext2 = pathinfo($_FILES["id_card_back"]["name"], PATHINFO_EXTENSION);
        $backName = uniqid("card_back_") . "." . $ext2;
        $backPath = "uploads/" . $backName;
        move_uploaded_file($_FILES["id_card_back"]["tmp_name"], $backPath);
    } else {
        die("يجب رفع صورة ظهر البطاقة!");
    }

    // دمج الصور
    function loadImage($path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext == "png") return imagecreatefrompng($path);
        else return imagecreatefromjpeg($path);
    }

    $imgFront = loadImage($frontPath);
    $imgBack  = loadImage($backPath);

    $width  = max(imagesx($imgFront), imagesx($imgBack));
    $height = imagesy($imgFront) + imagesy($imgBack);

    $merged = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($merged, 255, 255, 255);
    imagefill($merged, 0, 0, $white);

    imagecopy($merged, $imgFront, 0, 0, 0, 0, imagesx($imgFront), imagesy($imgFront));
    imagecopy($merged, $imgBack, 0, imagesy($imgFront), 0, 0, imagesx($imgBack), imagesy($imgBack));

    $mergedPath = "uploads/card_merged_" . uniqid() . ".jpg";
    imagejpeg($merged, $mergedPath, 90);

    imagedestroy($imgFront);
    imagedestroy($imgBack);
    imagedestroy($merged);

    // حفظ البيانات
    $file = "applications.txt";
    $data  = "=============================\n";
    $data .= "الاسم: $name\n";
    $data .= "العنوان: $address\n";
    $data .= "رقم الهاتف: $phone\n";
    $data .= "الوظيفة المطلوبة: $job\n";
    $data .= "الخبرات: $exp\n";
    $data .= "صورة البطاقة (وجه+ظهر): $mergedPath\n";
    $data .= "تاريخ التقديم: ". date("Y-m-d H:i:s") ."\n";
    $data .= "=============================\n\n";

    file_put_contents($file, $data, FILE_APPEND);

    // التحويل لصفحة النجاح
    header("Location: success.html");
    exit();
}
?>
