<?php
require __DIR__.'/includes/app.php'; require_login(); $errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $name=is_string($_POST['name']??null)?trim($_POST['name']):'';
 $items=plan_items($_POST['items']??[], $errors);
 $target=$items ? array_sum(array_column($items,'amount')) : amount($_POST['target']??null);
 $installment=amount($_POST['installment']??null); $frequency=is_string($_POST['frequency']??null)?$_POST['frequency']:''; $start=is_string($_POST['start']??null)?$_POST['start']:'';
 if(!valid_form('goal')) { notice('Formulir ini sudah diproses. Jadwal tidak dibuat dua kali.','info'); go('jadwal.php'); }
 if($name==='' || preg_match_all('/./us', $name)===false || preg_match_all('/./us', $name)>100) $errors[]='Nama tujuan wajib diisi, maksimal 100 karakter.';
 if(!$target || $target>MAX_AMOUNT) $errors[]='Target harus berupa angka bulat antara Rp1 dan Rp1.000.000.000.';
 if(!$installment || ($target && $installment>$target)) $errors[]='Nominal rutin harus positif dan tidak melebihi target.';
 if(!in_array($frequency,['Harian','Mingguan'],true)) $errors[]='Pilih frekuensi harian atau mingguan.';
 if(!date_ok($start) || $start<date('Y-m-d') || $start>date('Y-m-d',strtotime('+5 years'))) $errors[]='Tanggal mulai harus hari ini hingga 5 tahun ke depan.';
 if(count($_SESSION['goals'])>=MAX_GOALS) $errors[]='Maksimal '.MAX_GOALS.' jadwal dalam satu sesi demo.';
 if(!$errors) { $id=bin2hex(random_bytes(8)); $_SESSION['goals'][$id]=['id'=>$id,'name'=>$name,'target'=>$target,'installment'=>$installment,'frequency'=>$frequency,'start'=>$start,'items'=>$items]; unset($_SESSION['forms']['goal']); notice('Jadwal berhasil dibuat. Sekarang Anda bisa mulai deposit.'); go('jadwal.php'); }
}
page_start('Jadwal tabungan','jadwal','Satu jadwal, beberapa kebutuhan. Rencanakan target dan waktu mencapainya.'); errors($errors);
?>
<div class="two-col">
<section class="panel">
    <div class="section-title"><h2>Buat jadwal baru</h2><span class="icon-box soft"><?=icon('calendar')?></span></div>
    <form method="post" action="jadwal.php">
        <?=csrf()?><?=form_id('goal')?>
        <label for="name">Nama tujuan</label><input id="name" name="name" maxlength="100" placeholder="Contoh: Perlengkapan sekolah" value="<?=old('name')?>" required>
        <fieldset class="item-editor" data-item-editor>
            <legend>Barang yang ingin ditabung <span class="muted">(opsional)</span></legend>
            <p class="small muted">Tambahkan hingga 5 barang. Target jadwal mengikuti total harga barang.</p>
            <div data-item-rows>
            <?php for ($i=0; $i<MAX_GOAL_ITEMS; $i++): $draft = is_array($_POST['items'][$i]??null) ? $_POST['items'][$i] : []; ?>
                <div class="item-editor__row" data-item-row>
                    <div><label for="item-name-<?=$i?>">Nama barang <?=$i+1?></label><input id="item-name-<?=$i?>" name="items[<?=$i?>][name]" maxlength="60" placeholder="Contoh: Tas sekolah" value="<?=e(is_string($draft['name']??null)?$draft['name']:'')?>" data-item-name></div>
                    <div><label for="item-amount-<?=$i?>">Harga (Rp)</label><input id="item-amount-<?=$i?>" name="items[<?=$i?>][amount]" type="number" min="1" max="1000000000" step="1" placeholder="250000" value="<?=e(is_string($draft['amount']??null)?$draft['amount']:'')?>" data-item-amount></div>
                    <button type="button" class="item-editor__remove" data-remove-item hidden aria-label="Hapus barang <?=$i+1?>">Hapus</button>
                </div>
            <?php endfor ?>
            </div>
            <button type="button" class="btn secondary" data-add-item hidden><?=icon('plus')?> Tambah barang</button>
            <span class="small muted" data-item-count hidden></span>
            <p class="small muted" data-item-status role="status" aria-live="polite"></p>
        </fieldset>
        <div class="form-grid">
            <div><label for="target">Target tabungan (Rp)</label><input id="target" name="target" type="number" min="1" max="1000000000" step="1" placeholder="500000" value="<?=old('target')?>" aria-describedby="target-help"><small id="target-help" class="muted">Isi target jika tidak menambahkan barang. Total barang dihitung kembali saat disimpan.</small></div>
            <div><label for="installment">Nominal rutin (Rp)</label><input id="installment" name="installment" type="number" min="1" max="1000000000" step="1" placeholder="5000" value="<?=old('installment')?>" required></div>
        </div>
        <div class="form-grid">
            <div><label for="frequency">Frekuensi</label><select id="frequency" name="frequency"><option value="Harian" <?=($_POST['frequency']??'')==='Harian'?'selected':''?>>Setiap hari</option><option value="Mingguan" <?=($_POST['frequency']??'')==='Mingguan'?'selected':''?>>Setiap minggu</option></select></div>
            <div><label for="start">Tanggal mulai</label><input id="start" name="start" type="date" min="<?=date('Y-m-d')?>" max="<?=date('Y-m-d',strtotime('+5 years'))?>" value="<?=old('start',date('Y-m-d'))?>" required></div>
        </div>
        <div class="info-note">Semua barang dalam satu jadwal berbagi saldo dan progres yang sama. Jadwal menjadi panduan; setoran dilakukan sendiri.</div>
        <button class="btn primary full" type="submit">Simpan jadwal</button>
    </form>
</section>
<section>
    <div class="section-title"><h2>Tujuanmu</h2><span class="pill"><?=count($_SESSION['goals'])?> jadwal</span></div>
    <?php if(!$_SESSION['goals']): ?>
    <div class="panel empty"><span class="icon-box"><?=icon('target')?></span><h3>Setiap tujuan punya awal</h3><p>Buat jadwal pertama, lalu lihat perkiraan waktu mencapai targetmu.</p></div>
    <?php else: foreach(array_reverse($_SESSION['goals']) as $g): $p=progress($g); ?>
    <article class="panel goal-card">
        <div class="section-title"><h3><?=e($g['name'])?></h3><span class="pill"><?=e($g['frequency'])?></span></div>
        <div class="goal-amount"><strong><?=rp(saved($g['id']))?></strong><span> / <?=rp($g['target'])?></span></div>
        <progress value="<?=$p?>" max="100" aria-label="Progres <?=e($g['name'])?>"><?=$p?>%</progress>
        <div class="progress-caption"><span><?=$p?>% tercapai</span><span><?=rp($g['installment'])?> / <?=$g['frequency']==='Harian'?'hari':'minggu'?></span></div>
        <?php require __DIR__.'/includes/plan_summary.php'; ?>
        <p class="small muted"><?=$p>=100?'Target tercapai. Kerja bagus!':'Jadwal berikutnya: '.pretty_date(next_due($g))?></p>
        <a class="btn secondary full" href="deposit.php?goal=<?=e($g['id'])?>">Tambah tabungan</a>
    </article>
    <?php endforeach; endif ?>
</section>
</div>
<?php page_end(); ?>
