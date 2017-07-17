<?php
$phrase['groups'] = array(
	'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'jquery',
	'jquery_custom_ui'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
$sql = "select p.project_id,u.username,date(p.date_end) as date,a.attachid,p.status,count(al.attachid) attachments_count from  
" . DB_PREFIX . "projects p 
left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto' 
left join " . DB_PREFIX . "attachment al on al.project_id=p.project_id 
left join " . DB_PREFIX . "users u on u.user_id=p.user_id 
where  p.status='open'  and (a.attachid is Null or al.attachid is Null)
group by p.project_id
ORDER BY date,u.user_id,p.project_id ASC";

"SELECT project_id,user_id,date(date_end) as date FROM " . DB_PREFIX . "projects where status = 'open' order by date,user_id,project_id asc";
$matchin_project = $ilance->db->query($sql);

$notmatching.= '<table border="1"><tr><td>Missing Images</td></tr><tr><td>Projectid</td><td>Consignor Name</td><td>End Date</td></tr>';

if($ilance->db->num_rows($matchin_project)>0)

{
      while($totallist=$ilance->db->fetch_array($matchin_project))

        {
            $add='';
            if($totallist['attachid']==Null and $totallist['attachments_count']>0)
            {
                $add='*';
            }
            $notmatching.= '<tr>
                            <td>'.$totallist['project_id'].$add.'</td>
                            <td>'.$totallist['username'].'</td>
                            <td>'.$totallist['date'].'</td></tr>';
	}
}		

$notmatching.='</table>';

define('FPDF_FONTPATH','../font/');
require('pdftable_1.9/lib/pdftable.inc.php');
$p = new PDFTable();
$p->AddPage();
$p->setfont('times','',10);
$p->htmltable($notmatching);
$p->output('missing_image_live.pdf','D');
?>