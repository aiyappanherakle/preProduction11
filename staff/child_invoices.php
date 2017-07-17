<?PHP
require_once './../functions/config.php';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1') {
	$sql1 = "SELECT *  FROM " . DB_PREFIX . "child_invoices group by child_invoices having count(id)>1";
	$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result1) > 0) {
		while ($line1 = $ilance->db->fetch_array($result1)) {
			$sql2 = "update " . DB_PREFIX . "child_invoices set is_duplicate = true WHERE child_invoices = '" . $line1['child_invoices'] . "'";
			$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		}
	}
} else {
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function process_init() {
	global $ilance;
	$sql1 = "SELECT invoiceid as parent_invoices,user_id,combine_project FROM " . DB_PREFIX . "invoices WHERE combine_project != ''";
	$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result1) > 0) {
		while ($line1 = $ilance->db->fetch_array($result1)) {
			$ter = explode(",", $line1['combine_project']);
			foreach ($ter as $i) {
				$sql2 = "SELECT user_id,invoiceid as child_invoices FROM " . DB_PREFIX . "invoices WHERE invoiceid = '" . $i . "'";
				$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($result2) > 0) {
					while ($line2 = $ilance->db->fetch_array($result2)) {
						if ($line1['user_id'] == $line2['user_id']) {
							$is_problem = false;
						} else {
							$is_problem = true;
						}
						$sql3 = "insert into " . DB_PREFIX . "child_invoices (parent_invoices, child_invoices, is_problem)
						 values ('" . $line1['parent_invoices'] . "',
						 	'" . $line2['child_invoices'] . "',
						 	'" . $is_problem . "')";
						$result3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
					}
				}

			}
		}
	}
}
?>
