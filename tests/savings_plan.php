<?php
// Run: php tests/savings_plan.php (isolated temporary session, no external service).
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__.'/../includes/app.php';
function check($actual, $expected, string $case): void {
    if ($actual !== $expected) throw new RuntimeException($case.': '.var_export($actual,true));
}
$today = new DateTimeImmutable('2026-10-09');
$g = ['id'=>'test','target'=>5000000,'installment'=>100000,'frequency'=>'Harian','start'=>'2026-10-09'];
$_SESSION['transactions'] = [
    ['goal_id'=>'test','total'=>3200000,'status'=>'success'],
    ['goal_id'=>'test','total'=>1000000,'status'=>'pending'],
    ['goal_id'=>'other','total'=>1000000,'status'=>'success'],
];
$e=plan_estimate($g,$today);
check($e['remaining'],1800000,'only successful deposits for this goal');
check($e['payments'],18,'18 routine deposits');
check($e['days'],18,'18 daily days');
check($e['date'],'2026-10-27','daily completion date');
$g['installment']=110000;
check(plan_estimate($g,$today)['payments'],17,'round up fractional deposits');
$g['installment']=100000; $g['frequency']='Mingguan';
check(plan_estimate($g,$today)['days'],126,'18 weekly deposits');
$g['start']='2026-10-05';
check(plan_estimate($g,$today)['date'],'2027-02-08','weekly due-date alignment');
$g['start']='2026-10-20';
check(plan_estimate($g,$today)['days'],130,'future start is included');
$g['target']=3000000;
check(plan_estimate($g,$today)['days'],0,'already achieved');
$g['target']=1000000000; $g['installment']=1;
check(plan_estimate($g,$today)['date'],null,'very long forecast avoids extreme calendar dates');
$errors=[];
$items=plan_items([['name'=>'Laptop','amount'=>'4000000'],['name'=>'Tas','amount'=>'1000000'],['name'=>'','amount'=>'']],$errors);
check(array_sum(array_column($items,'amount')),5000000,'combined target');
check($errors,[],'blank rows ignored');
$errors=[]; plan_items([['name'=>'Tas','amount'=>'0']],$errors); check(count($errors)>0,true,'invalid price');
$errors=[]; plan_items([['name'=>'','amount'=>'100']],$errors); check(count($errors)>0,true,'missing item name');
$errors=[]; plan_items([['name'=>'A','amount'=>'1000000000'],['name'=>'B','amount'=>'1']],$errors); check(count($errors)>0,true,'total overflow');
$errors=[]; plan_items(array_fill(0,6,['name'=>'A','amount'=>'1']),$errors); check(count($errors)>0,true,'too many items');
$errors=[]; plan_items([['name'=>['invalid'],'amount'=>'100']],$errors); check(count($errors)>0,true,'malformed item');
$errors=[]; check(plan_items([],$errors),[],'legacy single target without items');
session_destroy();
echo "PASS: item validation, target totals, daily/weekly forecasts, rounding, future dates, successful balances and legacy goals.\n";
