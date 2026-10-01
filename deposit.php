<?php
require __DIR__.'/includes/app.php'; require_login(); $errors=[];
$selected=(string)($_POST['goal_id']??$_GET['goal']??'');
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if(!valid_form('deposit')) { notice('Permintaan deposit sudah diproses. Periksa riwayat untuk membukanya.','info'); go('riwayat.php'); }
 $nominal=amount($_POST['amount']??null); $note=trim((string)($_POST['note']??''));
 if(!isset($_SESSION['goals'][$selected])) $errors[]='Pilih tujuan tabungan yang tersedia.';
 if(!$nominal) $errors[]='Nominal harus berupa angka bulat antara Rp1 dan Rp1.000.000.000.';
 if(strlen($note)>300) $errors[]='Keterangan terlalu panjang. Gunakan maksimal 100 karakter.';
 if(count_status('pending')>=20) $errors[]='Selesaikan atau batalkan deposit yang menunggu (maksimal 20).';
 if(count($_SESSION['transactions'])>=MAX_TRANSACTIONS) $errors[]='Batas '.MAX_TRANSACTIONS.' transaksi per sesi demo tercapai. Keluar untuk memulai sesi baru.';
 if(!$errors) {
  // Acak baru per transaksi, berbeda dari transaksi sebelumnya. Bukan penggabungan string.
  $used=[]; foreach($_SESSION['transactions'] as $t) if($t['status']==='pending') $used[$t['total']]=true;
  $available=[]; for($i=1;$i<=999;$i++) if($i!==($_SESSION['last_code']??0) && !isset($used[$nominal+$i])) $available[]=$i;
  $code=$available[random_int(0,count($available)-1)]; $id=bin2hex(random_bytes(8));
  $_SESSION['transactions'][$id]=['id'=>$id,'goal_id'=>$selected,'amount'=>$nominal,'code'=>$code,'total'=>$nominal+$code,'note'=>$note,'created'=>time(),'expires'=>time()+1800,'paid_at'=>null,'status'=>'pending'];
  $_SESSION['last_code']=$code; unset($_SESSION['forms']['deposit']); go('pembayaran.php?id='.$id);
 }
}
page_start('Deposit','deposit','Sisihkan uang untuk tujuanmu, mulai dari nominal yang nyaman.'); errors($errors);
if(!$_SESSION['goals']): ?><section class="panel"><?php empty_state('Buat tujuan terlebih dahulu','Setiap deposit akan dicatat pada satu tujuan tabungan.','jadwal.php','Buat jadwal tabungan'); ?></section><?php else: ?><div class="two-col"><section class="panel"><div class="section-title"><h2>Tambah tabungan</h2><span class="icon-box soft"><?=icon('wallet')?></span></div><form method="post" action="deposit.php"><?=csrf()?><?=form_id('deposit')?><label for="goal_id">Tujuan tabungan</label><select id="goal_id" name="goal_id" required><option value="">Pilih tujuan</option><?php foreach($_SESSION['goals'] as $g): ?><option value="<?=e($g['id'])?>" <?=$selected===$g['id']?'selected':''?>><?=e($g['name'])?></option><?php endforeach ?></select><label for="amount">Nominal deposit (Rp)</label><input type="number" id="amount" name="amount" min="1" max="1000000000" step="1" placeholder="5000" value="<?=old('amount',isset($_SESSION['goals'][$selected])?(string)$_SESSION['goals'][$selected]['installment']:'')?>" required><div class="amount-options" aria-label="Pilihan nominal"><?php foreach([5000,10000,20000,50000] as $n): ?><button type="button" data-amount="<?=$n?>"><?=rp($n)?></button><?php endforeach ?></div><label for="note">Keterangan <span class="muted">(opsional)</span></label><input id="note" name="note" maxlength="100" placeholder="Contoh: Sisa uang jajan" value="<?=old('note')?>"><div class="info-note">Total pembayaran = nominal deposit + kode acak 001–999. Seluruh total masuk ke saldo simulasi.</div><button class="btn primary full" type="submit">Buat pembayaran</button></form></section><aside class="panel info-panel"><span class="eyebrow">CARA DEPOSIT</span><h2>Satu langkah kecil,<br>tetap berarti.</h2><ol class="number-list"><li><b>Pilih tujuan dan nominal</b><span>Nominal bisa berbeda dari jadwal rutin.</span></li><li><b>Dapatkan angka pembayaran</b><span>Contoh Rp5.000 + kode 123 = Rp5.123.</span></li><li><b>Coba konfirmasi simulasi</b><span>Saldo dan riwayat akan diperbarui.</span></li></ol><p class="small muted">Kode baru dibuat setiap transaksi. Memuat ulang halaman pembayaran tidak mengubah kode.</p></aside></div><?php endif ?><?php page_end(); ?>
