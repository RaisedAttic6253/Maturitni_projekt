<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Maturitní projekt' ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22><?= $icon ?? '📊' ?></text></svg>">
    <?= $this->include('layout/assets') ?>
</head>
<body class="bg-light">

    <?= $this->include('layout/navbar') ?>

    <div class="container mt-4">
        <?= $this->renderSection('content') ?>
    </div>

</body>
</html>

