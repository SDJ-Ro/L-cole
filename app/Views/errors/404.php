<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Page Not Found · L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: var(--cream, #F7F3EC);
      color: var(--midnight, #0F414A);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      text-align: center;
      box-sizing: border-box;
      padding: 24px;
    }
    .c-404-container {
      max-width: 520px;
      background: #ffffff;
      border: 1px solid rgba(15, 65, 74, 0.12);
      border-radius: 20px;
      padding: 48px 40px;
      box-shadow: 0 12px 32px rgba(15, 65, 74, 0.06);
    }
    .c-404-badge {
      display: inline-block;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: var(--terracotta, #AF5031);
      background: rgba(175, 80, 49, 0.1);
      padding: 6px 14px;
      border-radius: 999px;
      margin-bottom: 20px;
    }
    .c-404-code {
      font-size: 72px;
      font-weight: 900;
      line-height: 1;
      color: var(--midnight, #0F414A);
      margin: 0 0 16px 0;
      letter-spacing: -0.03em;
    }
    .c-404-title {
      font-size: 22px;
      font-weight: 700;
      color: var(--midnight, #0F414A);
      margin: 0 0 12px 0;
    }
    .c-404-desc {
      font-size: 15px;
      line-height: 1.6;
      color: rgba(15, 65, 74, 0.72);
      margin: 0 0 32px 0;
    }
    .c-404-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: var(--midnight, #0F414A);
      color: #ffffff;
      text-decoration: none;
      padding: 13px 28px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 14px;
      transition: background 0.18s ease, transform 0.18s ease;
    }
    .c-404-btn:hover {
      background: var(--deepsea, #092F33);
      transform: translateY(-1px);
    }
    .c-404-footer {
      margin-top: 28px;
      font-size: 12px;
      color: rgba(15, 65, 74, 0.5);
    }
  </style>
</head>
<body>
  <div class="c-404-container">
    <div class="c-404-badge">Error 404</div>
    <div class="c-404-code">404</div>
    <h1 class="c-404-title">Resource or Action Not Found</h1>
    <p class="c-404-desc">The page or endpoint you are attempting to reach does not exist or may have been relocated.</p>
    <a href="/" class="c-404-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Return to L'École Portal
    </a>
  </div>
  <div class="c-404-footer">
    &copy; <?= date('Y') ?> L'École International School. All rights reserved.
  </div>
</body>
</html>
