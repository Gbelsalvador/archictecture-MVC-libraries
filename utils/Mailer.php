<?php
namespace Utils;

class Mailer
{
    /**
     * Send an email. Uses PHPMailer if available, otherwise falls back to mail().
     * $options: ['from' => '...', 'reply_to' => '...', 'is_html' => true, 'attachments' => []]
     */
    public static function send(string $to, string $subject, string $body, array $options = []): bool
    {
        // Prefer PHPMailer if installed
        if (class_exists('\PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $class = '\\PHPMailer\\PHPMailer\\PHPMailer';
                $mail = new $class(true);

                // SMTP config via env (optional)
                $smtpHost = $_ENV['SMTP_HOST'] ?? null;
                if ($smtpHost) {
                    $mail->isSMTP();
                    $mail->Host = $smtpHost;
                    $mail->SMTPAuth = ($_ENV['SMTP_AUTH'] ?? '1') === '1';
                    $mail->Username = $_ENV['SMTP_USER'] ?? '';
                    $mail->Password = $_ENV['SMTP_PASS'] ?? '';
                    $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? '';
                    $mail->Port = (int)($_ENV['SMTP_PORT'] ?? 587);
                }

                $from = $options['from'] ?? ($_ENV['MAIL_FROM'] ?? 'no-reply@localhost');
                $fromName = $options['from_name'] ?? ($_ENV['MAIL_FROM_NAME'] ?? 'No Reply');

                $mail->setFrom($from, $fromName);
                $mail->addAddress($to);

                if (!empty($options['reply_to'])) {
                    $mail->addReplyTo($options['reply_to']);
                }

                $mail->Subject = $subject;
                $mail->isHTML(!empty($options['is_html']));
                $mail->Body = $body;

                if (!empty($options['attachments']) && is_array($options['attachments'])) {
                    foreach ($options['attachments'] as $path) {
                        if (is_file($path)) {
                            $mail->addAttachment($path);
                        }
                    }
                }

                return $mail->send();
            } catch (\Throwable $e) {
                error_log('Mailer error: ' . $e->getMessage());
                return false;
            }
        }

        // Fallback to PHP mail()
        $headers = [];
        $from = $options['from'] ?? ($_ENV['MAIL_FROM'] ?? 'no-reply@localhost');
        $headers[] = 'From: ' . $from;
        if (!empty($options['reply_to'])) {
            $headers[] = 'Reply-To: ' . $options['reply_to'];
        }
        if (!empty($options['is_html'])) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=utf-8';
        } else {
            $headers[] = 'Content-type: text/plain; charset=utf-8';
        }

        $headerStr = implode("\r\n", $headers);
        return mail($to, $subject, $body, $headerStr);
    }
}

?>