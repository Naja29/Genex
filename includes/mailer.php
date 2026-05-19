<?php
require_once __DIR__ . '/functions.php';

class SmtpMailer
{
    private $sock = null;

    public function deliver(array $cfg, string $to, string $toName, string $subject, string $html): bool
    {
        if (!$this->connect($cfg['host'], (int)$cfg['port'], $cfg['encryption'])) return false;
        if (!$this->auth($cfg['username'], $cfg['password']))  { $this->quit(); return false; }
        $ok = $this->sendMessage($cfg['from_email'], $cfg['from_name'], $to, $toName, $subject, $html);
        $this->quit();
        return $ok;
    }

    private function connect(string $host, int $port, string $enc): bool
    {
        $addr = ($enc === 'ssl' ? 'ssl://' : '') . $host;
        $this->sock = @fsockopen($addr, $port, $errno, $errstr, 15);
        if (!$this->sock) return false;

        $this->read();
        $this->cmd('EHLO ' . (gethostname() ?: 'localhost'));

        if ($enc === 'tls') {
            $this->cmd('STARTTLS');
            stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->cmd('EHLO ' . (gethostname() ?: 'localhost'));
        }
        return true;
    }

    private function auth(string $u, string $p): bool
    {
        $this->cmd('AUTH LOGIN');
        $this->cmd(base64_encode($u));
        $r = $this->cmd(base64_encode($p));
        return str_starts_with($r, '235');
    }

    private function sendMessage(
        string $from, string $fromName,
        string $to,   string $toName,
        string $subject, string $html
    ): bool {
        $this->cmd("MAIL FROM:<$from>");
        $this->cmd("RCPT TO:<$to>");
        $this->cmd('DATA');

        $boundary = uniqid('gnx_', true);
        $plain    = wordwrap(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html)), 76, "\n", true);

        $msg  = 'From: =?UTF-8?B?' . base64_encode($fromName) . "?= <$from>\r\n";
        $msg .= 'To: =?UTF-8?B?' . base64_encode($toName ?: $to) . "?= <$to>\r\n";
        $msg .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $msg .= 'Date: ' . date('r') . "\r\n";
        $msg .= "X-Mailer: GenexMailer/1.0\r\n\r\n";

        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($plain)) . "\r\n";

        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($html)) . "\r\n";

        $msg .= "--$boundary--\r\n.";

        $r = $this->cmd($msg);
        return str_starts_with($r, '250');
    }

    private function quit(): void
    {
        if ($this->sock) {
            @fwrite($this->sock, "QUIT\r\n");
            fclose($this->sock);
            $this->sock = null;
        }
    }

    private function cmd(string $c): string
    {
        fwrite($this->sock, $c . "\r\n");
        return $this->read();
    }

    private function read(): string
    {
        $data = '';
        while ($line = fgets($this->sock, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }
}

// Public helper 
function sendMail(string $to, string $toName, string $subject, string $html): bool
{
    $cfg = [
        'host'       => getSetting('smtp_host'),
        'port'       => getSetting('smtp_port', '587'),
        'encryption' => getSetting('smtp_encryption', 'tls'),
        'username'   => getSetting('smtp_username'),
        'password'   => getSetting('smtp_password'),
        'from_email' => getSetting('smtp_from_email'),
        'from_name'  => getSetting('smtp_from_name', getSetting('store_name', 'Genex Store')),
    ];

    if (!$cfg['host'] || !$cfg['username'] || !$cfg['from_email']) return false;

    try {
        return (new SmtpMailer())->deliver($cfg, $to, $toName, $subject, $html);
    } catch (Throwable) {
        return false;
    }
}
