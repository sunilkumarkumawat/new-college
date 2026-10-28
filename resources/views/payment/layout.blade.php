<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') | {{ config('app.name') }}</title>
    <style>
        :root { --ink:#1F2A37; --muted:#5B6776; --line:#D5DDDB; --bg:#EEF2F1; --accent:#0F766E; --ok:#067647; --bad:#B42318; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:var(--bg);
               color:var(--ink); font:16px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        main { width:100%; max-width:420px; background:#fff; border:1px solid var(--line); border-radius:14px; padding:28px; }
        .brand { margin:0 0 20px; font-weight:700; color:var(--accent); }
        h1 { margin:0 0 8px; font-size:1.5rem; line-height:1.25; }
        p { margin:0 0 16px; color:var(--muted); }
        label { display:block; margin:14px 0 6px; font-size:.9rem; font-weight:600; }
        input { width:100%; padding:12px; border:1px solid var(--line); border-radius:8px; font:inherit; color:inherit; }
        input[readonly] { background:var(--bg); }
        input:focus, button:focus-visible, a:focus-visible { outline:3px solid #0F766E55; outline-offset:1px; border-color:var(--accent); }
        button, .btn { display:block; width:100%; margin-top:22px; padding:13px; border:0; border-radius:8px; background:var(--accent);
                       color:#fff; font:600 1rem/1.4 system-ui, sans-serif; text-align:center; text-decoration:none; cursor:pointer; }
        .err { margin:6px 0 0; color:var(--bad); font-size:.9rem; }
        .ok { color:var(--ok); } .bad { color:var(--bad); }
        dl { display:grid; grid-template-columns:auto 1fr; gap:6px 16px; margin:18px 0 0; }
        dt { color:var(--muted); } dd { margin:0; text-align:right; font-weight:600; overflow-wrap:anywhere; }
    </style>
</head>
<body>
    <main>
        <p class="brand">{{ config('app.name') }}</p>
        @yield('content')
    </main>
</body>
</html>
