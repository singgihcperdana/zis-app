<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
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

    public function rekapPage(): void
    {
        View::render('reports/rekap', [
            'title' => 'Rekap ZIS',
            'pageTitle' => 'Rekap ZIS',
            'breadcrumbs' => ['Report', 'Rekap ZIS'],
            'csrfToken' => \App\Core\Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function rekapZisApi(): void
    {
        $from = isset($_GET['from']) ? trim((string) $_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string) $_GET['to']) : '';

        try {
            Response::json($this->reports->rekapZis($from, $to));
        } catch (RuntimeException $exception) {
            $status = str_contains(strtolower($exception->getMessage()), 'wajib')
                || str_contains(strtolower($exception->getMessage()), 'tidak boleh')
                || str_contains(strtolower($exception->getMessage()), 'format')
                ? 422
                : 500;

            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    public function muzakkiDetailPage(): void
    {
        View::render('reports/muzakki-detail', [
            'title' => 'Data Muzakki',
            'pageTitle' => 'Data Muzakki',
            'breadcrumbs' => ['Report', 'Data Muzakki'],
            'csrfToken' => \App\Core\Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function muzakkiDetailApi(): void
    {
        $from = isset($_GET['from']) ? trim((string) $_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string) $_GET['to']) : '';

        try {
            Response::json($this->reports->muzakkiDetail($from, $to));
        } catch (RuntimeException $exception) {
            $status = str_contains(strtolower($exception->getMessage()), 'wajib')
                || str_contains(strtolower($exception->getMessage()), 'tidak boleh')
                || str_contains(strtolower($exception->getMessage()), 'format')
                ? 422
                : 500;

            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    public function muzakkiDetailCsv(): void
    {
        $from = isset($_GET['from']) ? trim((string) $_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string) $_GET['to']) : '';

        try {
            $report = $this->reports->muzakkiDetail($from, $to);
            $csv = $this->reports->muzakkiDetailCsv($report);
            $filename = sprintf('muzakki-detail_%s_%s.csv', $report['fromDate'] ?? $from, $report['toDate'] ?? $to);

            http_response_code(200);
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo "\xEF\xBB\xBF" . $csv;
            exit;
        } catch (RuntimeException $exception) {
            Response::abort(422, $exception->getMessage());
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
