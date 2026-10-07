<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; }
        .card { width: 100%; text-align: center; }
        .card img { width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ $image }}" alt="{{ $card->name }}">
    </div>
</body>
</html>
