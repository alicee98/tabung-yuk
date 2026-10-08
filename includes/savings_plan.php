<?php
/** Multiple items remain part of one goal and share its balance. */
const MAX_GOAL_ITEMS = 5;
function plan_items($raw, array &$errors): array {
    if (!is_array($raw)) { $errors[] = 'Format daftar barang tidak valid.'; return []; }
    if (count($raw) > MAX_GOAL_ITEMS) { $errors[] = 'Maksimal 5 barang per jadwal.'; return []; }
    $items = [];
    foreach ($raw as $row) {
        if (!is_array($row) || !is_string($row['name'] ?? '') || !is_string($row['amount'] ?? '')) {
            $errors[] = 'Format barang tidak valid.'; continue;
        }
        $name = trim($row['name'] ?? ''); $value = trim($row['amount'] ?? '');
        if ($name === '' && $value === '') continue;
        $price = amount($value);
        if ($name === '' || preg_match_all('/./us', $name) === false || preg_match_all('/./us', $name) > 60 || !$price) {
            $errors[] = 'Isi nama setiap barang (maksimal 60 karakter) dan harga bulat Rp1–Rp1.000.000.000.'; continue;
        }
        $items[] = ['name'=>$name, 'amount'=>$price];
    }
    if (array_sum(array_column($items, 'amount')) > MAX_AMOUNT) $errors[] = 'Total harga barang tidak boleh melebihi Rp1.000.000.000.';
    return $items;
}
/** Assume one routine deposit on each future due date. Pending deposits do not count. */
function plan_estimate(array $goal, ?DateTimeImmutable $today = null): array {
    $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
    $remaining = max(0, $goal['target'] - saved($goal['id']));
    if ($remaining === 0) return ['remaining'=>0, 'payments'=>0, 'days'=>0, 'date'=>null];
    $payments = intdiv($remaining - 1, $goal['installment']) + 1;
    $period = $goal['frequency'] === 'Mingguan' ? 7 : 1;
    $first = new DateTimeImmutable($goal['start']);
    if ($first <= $today) {
        $elapsed = (int)$first->diff($today)->format('%a');
        $first = $first->modify('+'.((intdiv($elapsed, $period) + 1) * $period).' days');
    }
    $days = (int)$today->diff($first)->format('%a') + ($payments - 1) * $period;
    // Avoid unwieldy calendar dates for extremely small routine deposits.
    $date = $days <= 36500 ? $today->modify('+'.$days.' days')->format('Y-m-d') : null;
    return ['remaining'=>$remaining, 'payments'=>$payments, 'days'=>$days, 'date'=>$date];
}
