<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'connect.php'; // الاتصال بقاعدة البيانات

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    $stmt = $conn->prepare("
        SELECT u.name, u.username, c.title
        FROM orders br
        JOIN users u ON br.user_id = u.id
        JOIN cars c ON br.car_id = c.id
        WHERE br.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();

        $mail = new PHPMailer(true);
        try {
            // إعدادات SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'saifnairat23@gmail.com';
            $mail->Password = 'Saif1234?'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('saifnairat23@gmail.com', 'Car Market');
            $mail->addAddress($data['username'], $data['name']); 

            $mail->Subject = 'طلب الشراء تم تأكيده';
            $mail->Body = "مرحبًا {$data['name']},\n\nلقد تم تأكيد طلبك لشراء السيارة: \"{$data['title']}\".\n\nشكرًا لاستخدامك موقعنا.";

            $mail->send();
            echo "✅ تم إرسال البريد الإلكتروني بنجاح.";
        } catch (Exception $e) {
            echo "❌ فشل في إرسال البريد: {$mail->ErrorInfo}";
        }
    } else {
        echo "❌ طلب غير صالح أو غير موجود.";
    }
} else {
    echo "❌ رقم الطلب غير محدد.";
}
?>
