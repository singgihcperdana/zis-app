<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\AuthService;
use App\Services\ReportService;
use RuntimeException;

final class ReportController
{
    private AuthService $auth;
    private ReportService $reports;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->reports = new ReportService();
    }

    public function kwitansiApi(string $paymentId): void
    {
        try {
            Response::json($this->reports->kwitansi($paymentId));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function kwitansiTemplatePrint(string $paymentId): void
    {
        $pdfRoute = '/api/reports/kwitansi/' . rawurlencode($paymentId) . '/template.pdf';
        $src = '/index.php?__route=' . rawurlencode($pdfRoute);
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Print Kwitansi</title>
  <style>
    html, body {
      margin: 0;
      height: 100%;
      background: #111;
      overflow: hidden;
      font-family: Arial, sans-serif;
    }
    iframe {
      border: 0;
      width: 100%;
      height: 100%;
      display: block;
    }
    .print-status {
      position: fixed;
      right: 16px;
      bottom: 16px;
      z-index: 9999;
      padding: 10px 14px;
      border-radius: 6px;
      background: rgba(0, 0, 0, 0.72);
      color: #fff;
      font-size: 13px;
      line-height: 1.4;
      transition: opacity 0.2s ease;
    }
    .print-status.is-hidden {
      opacity: 0;
      pointer-events: none;
    }
  </style>
</head>
<body>
  <iframe id="pdfFrame" src="{$src}"></iframe>
  <div id="printStatus" class="print-status">Menyiapkan preview kwitansi...</div>
  <script>
    const frame = document.getElementById('pdfFrame');
    const statusBox = document.getElementById('printStatus');
    let printStarted = false;
    let loadDetected = false;

    function setStatus(message, hidden) {
      statusBox.textContent = message;
      statusBox.classList.toggle('is-hidden', hidden === true);
    }

    function runPrint() {
      if (printStarted) {
        return;
      }

      printStarted = true;
      setStatus('Membuka dialog print...', false);

      const attempts = [200, 600, 1200];

      attempts.forEach(function (delay, index) {
        setTimeout(function () {
          try {
            if (frame.contentWindow) {
              frame.contentWindow.focus();
              frame.contentWindow.print();
            } else if (index === attempts.length - 1) {
              window.focus();
              window.print();
            }
          } catch (error) {
            if (index === attempts.length - 1) {
              window.focus();
              window.print();
            }
          }
        }, delay);
      });

      setTimeout(function () {
        setStatus('Jika dialog print belum muncul, gunakan Ctrl/Cmd+P.', false);
      }, 1800);

      window.addEventListener('focus', function onFocus() {
        setTimeout(function () {
          setStatus('Preview kwitansi siap.', true);
        }, 300);
        window.removeEventListener('focus', onFocus);
      });
    }

    frame.addEventListener('load', function () {
      loadDetected = true;
      setStatus('Preview kwitansi siap.', false);
      setTimeout(runPrint, 350);
    });

    setTimeout(function () {
      if (!loadDetected) {
        setStatus('Masih memuat PDF kwitansi...', false);
      }
    }, 800);

    setTimeout(function () {
      if (!printStarted) {
        runPrint();
      }
    }, 2200);
  </script>
</body>
</html>
HTML;

        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    public function kwitansiTemplatePdf(string $paymentId): void
    {
        try {
            $bytes = $this->reports->kwitansiTemplatePdf($paymentId);
            http_response_code(200);
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="kwitansi-' . rawurlencode($paymentId) . '.pdf"');
            echo $bytes;
            exit;
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
        }
    }
}
