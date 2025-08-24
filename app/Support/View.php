<?php
declare(strict_types=1);

namespace App\Support;

final class View {
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../../views/layout/header.php';
        require __DIR__ . '/../../views/' . $template . '.php';
        require __DIR__ . '/../../views/layout/footer.php';
    }
}