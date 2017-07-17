<?php
require_once('../functions/config.php');
error_reporting(E_ALL);
$time_slot=' DATE_SUB(DATE(NOW()), INTERVAL DAYOFWEEK(NOW())+1 DAY) ';
$time_slot1=' DATE_SUB(DATE(NOW()), INTERVAL DAYOFWEEK(NOW())-1 DAY) ';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
//friday to friday-total number of registred user with in this week
$sql="SELECT  count(user_id) as registered_users , DATE_FORMAT(min(date(date_added)),'%d %b %y') as from_date ,DATE_FORMAT(max(date(date_added)),'%d %b %y') as to_date   FROM " . DB_PREFIX . "users where date_added>=".$time_slot."";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
	while($line= $ilance->db->fetch_array($result))
	{
		$registered_users=$line['registered_users'];

		echo "<br>registered_users,".$registered_users;
		$from_date=$line['from_date'];
		$to_date=$line['to_date'];
	}
}
//users with out usernames

$sql1="SELECT count(user_id) as blank_usernames FROM " . DB_PREFIX . "users WHERE username = ''";
$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result1)>0)
{
	while($line1= $ilance->db->fetch_array($result1))
	{
		$blank_usernames=$line1['blank_usernames'];
		echo "<br>blank_usernames,".$blank_usernames;
	}
}

//users with duplicate usernames
$sql2="select count(*) as duplicate_usernames from (SELECT count(user_id) as duplicate_usernames FROM " . DB_PREFIX . "users group by username having count(user_id)>1) p";
$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result2)>0)
{
	while($line2= $ilance->db->fetch_array($result2))
	{
		$duplicate_usernames=$line2['duplicate_usernames'];
		echo "<br>duplicate_usernames,".$duplicate_usernames;
	}
}

//users with duplicate emails
$sql2="select count(*) as duplicate_emails from (SELECT count(user_id) as duplicate_usernames FROM " . DB_PREFIX . "users group by email having count(user_id)>1) p";
$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result2)>0)
{
	while($line2= $ilance->db->fetch_array($result2))
	{
		$duplicate_emails=$line2['duplicate_emails'];
		echo "<br>duplicate_emails,".$duplicate_emails;
	}
}


//users with blank emails
$sql3="SELECT count(user_id) as blank_email  FROM " . DB_PREFIX . "users where email=''";
$result3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result3)>0)
{
	while($line3= $ilance->db->fetch_array($result3))
	{
		$blank_email=$line3['blank_email'];
		echo "<br>blank_email,".$blank_email;
	}
}

//number of live coins
$sql4="SELECT count(project_id) as live_coins FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id  where p.status='open' and u.status='active'";
$result4 = $ilance->db->query($sql4, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result4)>0)
{
	while($line4= $ilance->db->fetch_array($result4))
	{
		$live_coins=$line4['live_coins'];
		echo "<br>live_coins,".$live_coins;
	}
}

//number of auction coins
$sql5="SELECT count(project_id) as live_auction_coins FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id  where p.status='open' and u.status='active'  and filtered_auctiontype='regular' ";
$result5 = $ilance->db->query($sql5, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result5)>0)
{
	while($line5= $ilance->db->fetch_array($result5))
	{
		$live_auction_coins=$line5['live_auction_coins'];
		echo "<br>live_auction_coins,".$live_auction_coins;
	}
}
//number of buynow coins
$sql6="SELECT count(project_id) as live_buynow_coins FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id  where p.status='open' and u.status='active' and filtered_auctiontype='fixed' ";
$result6 = $ilance->db->query($sql6, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result6)>0)
{
	while($line6= $ilance->db->fetch_array($result6))
	{
		$live_buynow_coins=$line6['live_buynow_coins'];
		echo "<br>live_buynow_coins,".$live_buynow_coins;
	}
}

//number coins listed in gc this week
$sql7="SELECT count(project_id) as gc_listed_coins  FROM " . DB_PREFIX . "projects where date_added>=".$time_slot1."";
$result7 = $ilance->db->query($sql7, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result7)>0)
{
	while($line7= $ilance->db->fetch_array($result7))
	{
		$gc_listed_coins=$line7['gc_listed_coins'];
		echo "<br>gc_listed_coins,".$gc_listed_coins;
	}
}

//number of coins listed in ebay this week
$sql8="SELECT count(coin_id) as ebay_listed_coins  FROM " . DB_PREFIX . "ebay_listing where listedon>=".$time_slot1."";
$result8 = $ilance->db->query($sql8, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result8)>0)
{
	while($line8= $ilance->db->fetch_array($result8))
	{
		$ebay_listed_coins=$line8['ebay_listed_coins'];
		echo "<br>ebay_listed_coins,".$ebay_listed_coins;
	}
}

//number of fresh listed coins from last friday

$sql9="SELECT count(project_id)  as fresh_coins FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "coin_relist r on r.coin_id=p.project_id where p.status='open' and r.coin_id is null and p.date_added>=".$time_slot."";
$result9 = $ilance->db->query($sql9, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result9)>0)
{
	while($line9= $ilance->db->fetch_array($result9))
	{
		$fresh_coins=$line9['fresh_coins'];
		echo "<br>fresh_coins,".$fresh_coins;
	}
}

//fresh regular coins
$sql10="SELECT count(project_id)  as fresh_auction_coins FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "coin_relist r on r.coin_id=p.project_id where p.status='open' and  p.filtered_auctiontype='regular'  and r.coin_id is null and p.date_added>=".$time_slot."";
$result10 = $ilance->db->query($sql10, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result10)>0)
{
	while($line10= $ilance->db->fetch_array($result10))
	{
		$fresh_auction_coins=$line10['fresh_auction_coins'];
		echo "<br>fresh_auction_coins,".$fresh_auction_coins;
	}
}

//fresh buynow coins
$sql10="SELECT count(project_id)  as fresh_buynow_coins FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "coin_relist r on r.coin_id=p.project_id where p.status='open' and  p.filtered_auctiontype='fixed'  and r.coin_id is null and p.date_added>=".$time_slot."";
$result10 = $ilance->db->query($sql10, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result10)>0)
{
	while($line10= $ilance->db->fetch_array($result10))
	{
		$fresh_buynow_coins=$line10['fresh_buynow_coins'];
		echo "<br>fresh_buynow_coins,".$fresh_buynow_coins;
	}
}

//total relisted 
$sql111="SELECT count(project_id) as relisted_coins1  FROM " . DB_PREFIX . "projects p  join " . DB_PREFIX . "coin_relist r on r.coin_id=p.project_id where p.status='open' and r.coin_id is not null and p.date_added>=".$time_slot." group by p.project_id ORDER BY p.project_id  DESC";
$sql11="select count(*) as relisted_coins  from ($sql111) p";
$result11 = $ilance->db->query($sql11, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result11)>0)
{
	while($line11= $ilance->db->fetch_array($result11))
	{
		$relisted_coins=$line11['relisted_coins'];
		echo "<br>relisted_coins,".$relisted_coins;
	}
}
//total relisted auction
$sql112="SELECT count(project_id)  as relisted_auction_coins1  FROM " . DB_PREFIX . "projects p  join " . DB_PREFIX . "coin_relist r on r.coin_id=p.project_id where p.status='open' and  p.filtered_auctiontype='regular'  and r.coin_id is not null and p.date_added>=".$time_slot." group by p.project_id ORDER BY p.project_id  DESC";
$sql11="select count(*) as relisted_auction_coins from ($sql112) p";
$result11 = $ilance->db->query($sql11, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result11)>0)
{
	while($line11= $ilance->db->fetch_array($result11))
	{
		$relisted_auction_coins=$line11['relisted_auction_coins'];
		echo "<br>relisted_auction_coins,".$relisted_auction_coins;
	}
}

//total relisted buynow
$sql112="SELECT count(project_id)  as relisted_buynow_coins  FROM " . DB_PREFIX . "projects p  join " . DB_PREFIX . "coin_relist r on r.coin_id=p.project_id where p.status='open' and  p.filtered_auctiontype='fixed'  and r.coin_id is not null and p.date_added>=".$time_slot." group by p.project_id ORDER BY p.project_id  DESC";
$sql11="select count(*) as relisted_buynow_coins from ($sql112) p";
$result11 = $ilance->db->query($sql11, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result11)>0)
{
	while($line11= $ilance->db->fetch_array($result11))
	{
		$relisted_buynow_coins=$line11['relisted_buynow_coins'];
		echo "<br>relisted_buynow_coins,".$relisted_buynow_coins;
	}
}

//number of coins won
$sql12="SELECT count(project_id)  as coin_wins FROM " . DB_PREFIX . "project_bids where date(date_awarded)= ".$time_slot1." ORDER BY " . DB_PREFIX . "project_bids.bidamount ASC";
$result12 = $ilance->db->query($sql12, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result12)>0)
{
	while($line12= $ilance->db->fetch_array($result12))
	{
		$coin_wins=$line12['coin_wins'];
		echo "<br>coin_wins,".$coin_wins;
	}
}

//number of inhouse wins
$sql13="SELECT  count(project_id)  as inhouse_coin_wins FROM " . DB_PREFIX . "project_bids where date(date_awarded)=".$time_slot1." and user_id in (28,82) ORDER BY " . DB_PREFIX . "project_bids.bidamount ASC";
$result13 = $ilance->db->query($sql13, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result13)>0)
{
	while($line13= $ilance->db->fetch_array($result13))
	{
		$inhouse_coin_wins=$line13['inhouse_coin_wins'];
		echo "<br>inhouse_coin_wins,".$inhouse_coin_wins;
	}
}

//number of daily deal
$sql14="SELECT count(b.orderid)  as daily_deal_orders_count FROM " . DB_PREFIX . "buynow_orders b left join " . DB_PREFIX . "coins c on c.coin_id=b.project_id where  b.amount<c.buy_it_now and date(b.item_end_date)>=".$time_slot1." ORDER BY b.item_end_date  DESC";
$result14 = $ilance->db->query($sql14, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result14)>0)
{
	while($line14= $ilance->db->fetch_array($result14))
	{
		$daily_deal_orders_count=$line14['daily_deal_orders_count'];
		echo "<br>daily_deal_orders_count,".$daily_deal_orders_count;
	}
}

//number of buynow_orders_count
$sql14="SELECT count(b.orderid)  as buynow_orders_count FROM " . DB_PREFIX . "buynow_orders b left join " . DB_PREFIX . "coins c on c.coin_id=b.project_id where  date(b.item_end_date)>=".$time_slot1." ORDER BY b.item_end_date  DESC";
$result14 = $ilance->db->query($sql14, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result14)>0)
{
	while($line14= $ilance->db->fetch_array($result14))
	{
		$buynow_orders_count=$line14['buynow_orders_count'];
		echo "<br>buynow_orders_count,".$buynow_orders_count;
	}
}


//number of invoice generated
$sql15="SELECT count(invoiceid) as number_invoices FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=0 and isif=0 and createdate>=".$time_slot1."";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$number_invoices=$line15['number_invoices'];
		echo "<br>number_invoices,".$number_invoices;
	}
}

//number of listing generated
$sql15="SELECT count(invoiceid) as number_listing_invoices FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=0 and isif=1 and createdate>=".$time_slot1."";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$number_listing_invoices=$line15['number_listing_invoices'];
		echo "<br>number_listing_invoices,".$number_listing_invoices;
	}
}

//number of fvf generated
$sql15="SELECT count(invoiceid) as number_fvf_invoices FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=1 and isbuyerfee=0 and isenhancementfee=0 and isif=0 and createdate>=".$time_slot1."";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$number_fvf_invoices=$line15['number_fvf_invoices'];
		echo "<br>number_fvf_invoices,".$number_fvf_invoices;
	}
}

//number of enhancement generated
$sql15="SELECT count(invoiceid) as number_enhancement_invoices FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=1 and isif=0 and createdate>=".$time_slot1."";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$number_enhancement_invoices=$line15['number_enhancement_invoices'];
		echo "<br>number_enhancement_invoices,".$number_enhancement_invoices;
	}
}

//number of buyer fee generated
$sql15="SELECT count(invoiceid) as number_buyerfee_invoices FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=1 and isenhancementfee=0 and isif=0 and createdate>=".$time_slot1."";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$number_buyerfee_invoices=$line15['number_buyerfee_invoices'];
		echo "<br>number_buyerfee_invoices,".$number_buyerfee_invoices;
	}
}

//number of paid invoices
$sql15="SELECT count(invoiceid) as paid_invoices FROM " . DB_PREFIX . "invoices where paiddate>=".$time_slot1." and status='paid'";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$paid_invoices=$line15['paid_invoices'];
		echo "<br>paid_invoices,".$paid_invoices;
	}
}

//number of checkedout invoices
$sql15="SELECT count(invoiceid) as checkedout_invoices FROM " . DB_PREFIX . "invoices where scheduled_date>=".$time_slot1."";
$result15 = $ilance->db->query($sql15, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result15)>0)
{
	while($line15= $ilance->db->fetch_array($result15))
	{
		$checkedout_invoices=$line15['checkedout_invoices'];
		echo "<br>checkedout_invoices,".$checkedout_invoices;
	}
}

$sql16="SELECT count(bid_id) as total_inhouse_bids  FROM " . DB_PREFIX . "project_bids WHERE user_id IN (28,82) and isproxybid=0 and date_added>".$time_slot1."";
$result16 = $ilance->db->query($sql16, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result16)>0)
{
	while($line16= $ilance->db->fetch_array($result16))
	{
		$total_inhouse_bids=$line16['total_inhouse_bids'];
		echo "<br>total_inhouse_bids,".$total_inhouse_bids;
	}
}

$sql16="SELECT count(bid_id) as total_bids  FROM " . DB_PREFIX . "project_bids WHERE  isproxybid=0 and date_added>".$time_slot1."";
$result16 = $ilance->db->query($sql16, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result16)>0)
{
	while($line16= $ilance->db->fetch_array($result16))
	{
		$total_bids=$line16['total_bids'];
		echo "<br>total_bids,".$total_bids;
	}
}

$sql16="SELECT sum(bidamount) as total_winning_bid_amount  FROM " . DB_PREFIX . "project_bids WHERE  bidstatus='awarded' and date_added>".$time_slot1."";
$result16 = $ilance->db->query($sql16, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result16)>0)
{
	while($line16= $ilance->db->fetch_array($result16))
	{
		$total_winning_bid_amount=$line16['total_winning_bid_amount'];
		echo "<br>total_winning_bid_amount,".$total_winning_bid_amount;
	}
}

$sql17="SELECT count(emaillogid) as total_outbid_email FROM " . DB_PREFIX . "emaillog where subject like 'You have been outbid on %' and date(date)>=".$time_slot1."";
$result17 = $ilance->db->query($sql17, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result17)>0)
{
	while($line17= $ilance->db->fetch_array($result17))
	{
		$total_outbid_email=$line17['total_outbid_email'];
		echo "<br>total_outbid_email,".$total_outbid_email;
	}
}

$sql17="SELECT count(emaillogid) as total_email FROM " . DB_PREFIX . "emaillog where date(date)>=".$time_slot1."";
$result17 = $ilance->db->query($sql17, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result17)>0)
{
	while($line17= $ilance->db->fetch_array($result17))
	{
		$total_email=$line17['total_email'];
		echo "<br>total_email,".$total_email;
	}
}

$sql18="SELECT sum(totalamount) as Total_buyerfee FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=1 and isenhancementfee=0 and isif=0 and combine_project = '' and createdate>=".$time_slot1."";
$result18 = $ilance->db->query($sql18, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result18)>0)
{
	while($line18= $ilance->db->fetch_array($result18))
	{
		$Total_buyerfee=$line18['Total_buyerfee'];
		echo "<br>Buyer's Fees,".$Total_buyerfee;
	}
}




$sql19c="SELECT sum(totalamount) as Total_enhancement FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=1 and isif=0 and combine_project = '' and createdate>=".$time_slot1."";
$result19c = $ilance->db->query($sql19c, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result19c)>0)
{
		while($line19c= $ilance->db->fetch_array($result19c))
	{
		$Total_enhancement=$line19c['Total_enhancement'];
 	}

}
$sql19="SELECT sum(totalamount) as Total_listingfee FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=0 and isif=1 and combine_project = '' and createdate>=".$time_slot1."";
$result19 = $ilance->db->query($sql19, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result19)>0)
{
	while($line19= $ilance->db->fetch_array($result19))
	{
		$Total_listingfee=$line19['Total_listingfee'] + $Total_enhancement;
		echo "<br>Listing Fees,".$Total_listingfee;
	}
}

$sql20="SELECT sum(totalamount) as Total_sellerfee FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=1 and isbuyerfee=0 and isenhancementfee=0 and isif=0 and combine_project = '' and createdate>=".$time_slot1."";

$result20 = $ilance->db->query($sql20, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result20)>0)
{
	while($line20= $ilance->db->fetch_array($result20))
	{
		$Total_sellerfee=$line20['Total_sellerfee'];
		echo "<br>Seller's Fees,".$Total_sellerfee;
	}
}

$sql21="SELECT sum(totalamount) as Total_sales FROM " . DB_PREFIX . "invoices where projectid!='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=0 and isif=0 and combine_project = '' and invoicetype!='subscription' and createdate>=".$time_slot1."";

$result21 = $ilance->db->query($sql21, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result21)>0)
{
	while($line21= $ilance->db->fetch_array($result21))
	{
		$Total_sales=$line21['Total_sales'];
		echo "<br>Total Sales,".$Total_sales;
	}
}

 $sql22="SELECT shipping_cost FROM " . DB_PREFIX . "invoice_projects where created_date>=".$time_slot1." group by final_invoice_id";
 

$result22 = $ilance->db->query($sql22, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result22)>0)
{
	$total = 0;
	while($line22= $ilance->db->fetch_array($result22))
	{
		$Total_shipping=$line22['shipping_cost'];
		 $total += $Total_shipping;
	}
		echo "<br>Shipping Fees,".$total;

}


$sql23="SELECT sum(taxamount) as Total_tax FROM " . DB_PREFIX . "invoices where  istaxable=1 and projectid='' and combine_project!='' and createdate>=".$time_slot1."";

$result23 = $ilance->db->query($sql23, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result23)>0)
{
	while($line23= $ilance->db->fetch_array($result23))
	{
		$Total_tax=$line23['Total_tax'];
		echo "<br>Sales Tax Charged,".$Total_tax;
	}
}



$sql26="SELECT count(project_id) as number_of_auction_coins_ended_lastweek FROM " . DB_PREFIX . "projects_log p left join " . DB_PREFIX . "users u on u.user_id=p.user_id  where  u.status='active' and  p.filtered_auctiontype='regular' and p.date_end between ".$time_slot." and now()";
$result26 = $ilance->db->query($sql26, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result26)>0)
{
	while($line26= $ilance->db->fetch_array($result26))
	{
		$number_of_auction_coins_ended_lastweek=$line26['number_of_auction_coins_ended_lastweek'];
		echo "<br>number_of_auction_coins_ended_lastweek,".$number_of_auction_coins_ended_lastweek;
	}
}

 $sql25="SELECT sum(buynow_qty) as number_of_buynow_coins_ended_lastweek FROM " . DB_PREFIX . "projects_log p left join " . DB_PREFIX . "users u on u.user_id=p.user_id  where  u.status='active' and  p.filtered_auctiontype='fixed' and p.date_end between ".$time_slot." and now()";

$result25 = $ilance->db->query($sql25, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result25)>0)
{
	while($line25= $ilance->db->fetch_array($result25))
	{
		$number_of_buynow_coins_ended_lastweek=$line25['number_of_buynow_coins_ended_lastweek'];
	   
		echo "<br>number_of_buynow_coins_ended_lastweek,".$number_of_buynow_coins_ended_lastweek;
		
	}
}


$sql24="SELECT count(project_id) as live_sold_auction_coins FROM " . DB_PREFIX . "projects where filtered_auctiontype = 'regular' AND haswinner > 0  AND winner_user_id > 0 and date_end between ".$time_slot." and now()";
$result24 = $ilance->db->query($sql24, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result24)>0)
{
	while($line24= $ilance->db->fetch_array($result24))
	{
		$live_sold_auction_coins=$line24['live_sold_auction_coins'];
		echo "<br>live_sold_auction_coins,".$live_sold_auction_coins;
	}
}

$sql6="SELECT sum(qty) as live_sold_buynow_coins FROM " . DB_PREFIX . "buynow_orders where item_end_date between ".$time_slot." and now()";
$result6 = $ilance->db->query($sql6, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result6)>0)
{
	while($line6= $ilance->db->fetch_array($result6))
	{
		$live_sold_buynow_coins=$line6['live_sold_buynow_coins'];
		echo "<br>live_sold_buynow_coins,".$live_sold_buynow_coins;
		
	}
}
echo '<br>Total number of unique coins sold, '.($live_sold_buynow_coins+$live_sold_auction_coins);
echo '<br>Total number of unique coins Listed, '.($number_of_buynow_coins_ended_lastweek+$number_of_auction_coins_ended_lastweek);
echo "<br>sell_through_percentage,".(($live_sold_buynow_coins+$live_sold_auction_coins)/($number_of_buynow_coins_ended_lastweek+$number_of_auction_coins_ended_lastweek))*100;
}

//to cont
?>