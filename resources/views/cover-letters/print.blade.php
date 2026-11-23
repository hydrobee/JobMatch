<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $coverLetter->title }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; line-height: 1.5; color: #000; margin: 2.5cm; }
        .letter { white-space: pre-wrap; }
        .bar { margin-bottom: 1.5em; padding: 0.75em 1em; background: #f3f4f6; font-size: 10pt; }
        @media print { .bar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="bar">
        To make a PDF, press Ctrl+P and choose "Save as PDF".
        <button onclick="window.print()">Print</button>
    </div>
    <div class="letter">{{ $coverLetter->content }}</div>
</body>
</html>
