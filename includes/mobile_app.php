<?php
// Shared Android download card. Keep the APK address consistent across pages.
$mobileCompact = $mobileCompact ?? false;
$mobileHeadingId = $mobileCompact ? 'login-mobile-title' : 'home-mobile-title';
?>
<section class="mobile-app<?= $mobileCompact ? ' mobile-app--compact' : '' ?>" aria-labelledby="<?= e($mobileHeadingId) ?>">
    <div class="mobile-app__intro">
        <span class="mobile-app__logo" aria-hidden="true">ty.</span>
        <div class="mobile-app__copy">
            <span class="mobile-app__eyebrow">TABUNG YUK MOBILE</span>
            <h<?= $mobileCompact ? '3' : '2' ?> id="<?= e($mobileHeadingId) ?>"><?= $mobileCompact ? 'Tabunganmu, dalam genggaman.' : 'Langkah kecil, langsung dari HP.' ?></h<?= $mobileCompact ? '3' : '2' ?>>
            <p><?= $mobileCompact ? 'Buka Tabung Yuk lebih praktis lewat aplikasi Android.' : 'Atur jadwal dan pantau progres tabungan lewat aplikasi Android yang ringan.' ?></p>
            <span class="mobile-app__meta">Android <span aria-hidden="true">·</span> APK v1.1</span>
        </div>
    </div>
    <div class="mobile-app__action">
        <a class="mobile-app__button" href="https://github.com/alicee98/tabung-yuk-android/releases/download/1.1/Tabung_Yuk.apk" target="_blank" rel="noopener noreferrer" aria-label="Unduh APK Tabung Yuk untuk Android">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 16v4a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-4"/></svg>
            Unduh APK
        </a>
        <small>Unduhan langsung dari GitHub</small>
    </div>
</section>
