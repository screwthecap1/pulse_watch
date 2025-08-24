<?php

use App\Support\Session;

$uid = Session::id();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PulseWatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            margin: 24px
        }

        nav a {
            margin-right: 12px
        }

        table {
            border-collapse: collapse
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px 10px
        }

        .muted {
            color: #666
        }

        .container {
            max-width: 980px;
            margin: 0 auto
        }

        code {
            background: #f6f6f6;
            padding: 2px 4px;
            border-radius: 4px
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><a href="/" style="text-decoration:none;color:inherit">PulseWatch</a></h1>
        <nav>
            <a href="/monitors">Monitors</a>
            <a href="/monitors/new">New monitor</a>
            <a href="/healthz" class="muted">/healthz</a>
            <?php if ($uid): ?>
                <span class="muted">|</span>
                <a href="/logout">Logout</a>
            <?php else: ?>
                <span class="muted">|</span>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </nav>
        <hr>
    </header>
