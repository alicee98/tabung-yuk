<?php $estimate = plan_estimate($g); ?>
<?php if (!empty($g['items'])): ?>
<ul class="plan-items" aria-label="Barang dalam jadwal <?=e($g['name'])?>">
    <?php foreach ($g['items'] as $item): ?><li><span><?=e($item['name'])?></span><strong><?=rp($item['amount'])?></strong></li><?php endforeach ?>
</ul>
<?php endif ?>
<div class="plan-estimate">
    <span class="plan-estimate__label"><?=icon('calendar')?> Estimasi target tercapai</span>
    <?php if (!$estimate['remaining']): ?>
        <strong>Target sudah tercapai!</strong>
        <p>Saldo tabunganmu sudah memenuhi target jadwal ini.</p>
    <?php else: ?>
        <strong><?=number_format($estimate['days'],0,',','.')?> hari lagi<?= $estimate['date'] ? ' · '.pretty_date($estimate['date']) : '' ?></strong>
        <p>Sisa <?=rp($estimate['remaining'])?> · <?=number_format($estimate['payments'],0,',','.')?> setoran <?= $g['frequency']==='Harian' ? 'harian' : 'mingguan' ?> lagi.</p>
        <small>Dengan <?=rp($g['installment'])?> setiap <?= $g['frequency']==='Harian' ? 'hari' : 'minggu' ?>, mulai jadwal berikutnya setelah hari ini. <?= !$estimate['date'] ? 'Estimasi lebih dari 100 tahun; pertimbangkan nominal rutin yang lebih besar. ' : '' ?>Perkiraan berubah mengikuti saldo dan keteraturan setoran.</small>
    <?php endif ?>
</div>
