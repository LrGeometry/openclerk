<?php

/**
 * Summary job: total NMC.
 */

$total = 0;

// get the most recent blockchain balances
$q = db()->prepare("SELECT * FROM address_balances
	JOIN addresses ON address_balances.address_id=addresses.id
	WHERE address_balances.user_id=? AND is_recent=1 AND currency=?");
$q->execute(array($job['user_id'], 'nmc'));
while ($balance = $q->fetch()) {
	$total += $balance['balance'];
}

crypto_log("Total NMC balance for user " . $job['user_id'] . ": " . $total);

// update old summaries
$q = db()->prepare("UPDATE summary_instances SET is_recent=0 WHERE is_recent=1 AND user_id=? AND summary_type=?");
$q->execute(array($job['user_id'], $summary['summary_type']));

// insert new summary
$q = db()->prepare("INSERT INTO summary_instances SET is_recent=1, user_id=:user_id, summary_type=:summary_type, balance=:balance");
$q->execute(array(
	"user_id" => $job['user_id'],
	"summary_type" => $summary['summary_type'],
	"balance" => $total,
));
crypto_log("Inserted new summary_instances id=" . db()->lastInsertId());
