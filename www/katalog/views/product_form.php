<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat produkt</title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="page-padded">
    <div class="form-container">
        <h1 class="page-title">Přidat produkt</h1>
        <?php if (isset($error) && $error): ?>
            <p class="alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="form-group">
            <div>
                <label class="form-label">Jméno produktu:</label>
                <input type="text" name="name" required
                    class="form-input">
            </div>

            <div>
                <label class="form-label">Detail:</label>
                <textarea name="description" rows="4"
                    class="form-input"></textarea>
            </div>

            <div>
                <label class="form-label">Cena:</label>
                <input type="number" step="0.01" name="price" required
                    class="form-input">
            </div>

            <div>
                <label class="form-label">Kategorie:</label>
                <select name="category_id"
                    class="form-select">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['display_name'] ?? $cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Obrázky:</label>
                <label for="imageInput"
                    class="file-upload-label">
                    Vyber obrázky
                </label>
                <input type="file" name="images[]" multiple id="imageInput" class="hidden">
                <p id="fileSizeInfo" class="file-size-info hidden"></p>
                <p id="fileSizeError" class="file-size-error hidden"></p>
            </div>

            <div class="form-actions">
                <button type="submit" id="submitBtn"
                    class="btn btn-primary">
                    Přidat produkt
                </button>
                <a href="index.php" class="link-muted">Zpět</a>
            </div>
        </form>
    </div>

    <script>
        const maxUploadSize = <?= isset($maxTotalImageSize) ? (int) $maxTotalImageSize : 20 * 1024 * 1024 ?>;
        const maxImageCount = <?= isset($maxImageCount) ? (int) $maxImageCount : 5 ?>;
        const imageInput = document.getElementById('imageInput');
        const fileSizeInfo = document.getElementById('fileSizeInfo');
        const fileSizeError = document.getElementById('fileSizeError');
        const submitBtn = document.getElementById('submitBtn');

        function formatSize(bytes) {
            if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return bytes + ' B';
        }

        function toggleSubmit(hasError) {
            submitBtn.disabled = hasError;
            submitBtn.classList.toggle('opacity-50', hasError);
            submitBtn.classList.toggle('cursor-not-allowed', hasError);
        }

        imageInput.addEventListener('change', function() {
            let totalSize = 0;
            for (const file of this.files) {
                totalSize += file.size;
            }

            if (this.files.length === 0) {
                fileSizeInfo.classList.add('hidden');
                fileSizeError.classList.add('hidden');
                toggleSubmit(false);
                return;
            }

            fileSizeInfo.textContent = 'Celková velikost: ' + formatSize(totalSize) + ' (' + this.files.length + ' soubor' + (this.files.length > 1 ? 'ů' : '') + ')';
            fileSizeInfo.classList.remove('hidden');

            if (this.files.length > maxImageCount) {
                fileSizeError.textContent = 'Můžeš nahrát maximálně ' + maxImageCount + ' obrázků.';
                fileSizeError.classList.remove('hidden');
                toggleSubmit(true);
            } else if (totalSize > maxUploadSize) {
                fileSizeError.textContent = 'Celková velikost překračuje limit ' + formatSize(maxUploadSize) + '. Vyber prosím menší soubory.';
                fileSizeError.classList.remove('hidden');
                toggleSubmit(true);
            } else {
                fileSizeError.classList.add('hidden');
                toggleSubmit(false);
            }
        });
    </script>
</body>

</html>