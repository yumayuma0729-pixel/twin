<?php
declare(strict_types=1);
function run_notifications(): void {
 $to=(string)option('notification_to','');$from=(string)option('notification_from','');
 if(!enabled('mail_enabled')||!filter_var($to,FILTER_VALIDATE_EMAIL)||!filter_var($from,FILTER_VALIDATE_EMAIL)||preg_match('/[\r\n]/',$to.$from)){echo "メール通知は未設定・停止中です。受付データは保持されています。\n";return;}
 $count=0;
 // Reset interrupted attempts, then claim one row at a time. No user-supplied text in email headers/body.
 query("UPDATE notifications SET state='failed',last_error='interrupted' WHERE state='processing' AND updated_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE)");
 foreach(query("SELECT id,inquiry_id FROM notifications WHERE state IN('pending','failed') AND attempts<5 ORDER BY id LIMIT 20")->fetchAll() as $n){$claim=query("UPDATE notifications SET state='processing',attempts=attempts+1,updated_at=UTC_TIMESTAMP() WHERE id=? AND state IN('pending','failed') AND attempts<5",[$n['id']]);if(!$claim->rowCount())continue;
  $accepted=mail($to,'[Twin Assist] New inquiry #'.(int)$n['inquiry_id'],"A new inquiry was received. Sign in to the admin panel to review it.\n".base_url().'/admin/inquiries/?id='.(int)$n['inquiry_id'],['From'=>$from,'Content-Type'=>'text/plain; charset=UTF-8']);
  query('UPDATE notifications SET state=?,last_error=?,updated_at=UTC_TIMESTAMP() WHERE id=?',[$accepted?'accepted':'failed',$accepted?'':'mail() rejected',$n['id']]);$count++;
 }
 echo $count."件の通知処理を実行しました。acceptedは配送処理の受付で、配達確認ではありません。\n";
}
