<?php
function layout_head(string $title): void
{
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . '</title><link rel="stylesheet" href="assets/style.css"></head><body>';
}

function layout_foot(): void
{
    echo '</body></html>';
}
