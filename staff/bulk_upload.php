<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
$phrase['groups'] = array(
'administration'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{

	/*Tamil for bug 2675* Starts * Line no 27,28,30,32,141 * 24/05/13 */
	$file_run_sql=$ilance->db->query("SELECT * from ".DB_PREFIX."concurrency_file_status WHERE filename='bulk_upload' ");
	$file_run_res=$ilance->db->fetch_array($file_run_sql);
	if($file_run_res['run_status']==0)
	{
		
		
	define("BULK_UPLOAD_DIR","upload_attachment/");
	  
	
	         $folder=DIR_SERVER_ROOT.BULK_UPLOAD_DIR;
	             
				
				 
				 
	//find all folders
	if ($handle = opendir($folder)) 
	
	{
		while (false !== ($file = readdir($handle))) {
		
			if($file!='.' and $file!='..')
			{
				$files_list[]=$file;
				//echo '<pre>';
				list($coin_id,$ext)=explode("-",$file);
				$coin_id=trim($coin_id);
				$coin_id_list[]=$coin_id;
				if(!check_coin_id($coin_id))
				{
							print_action_failed('There seems to be an inconsistancy in the image names, Image named <strong>'.$file.'</strong> can only be uploaded after coind id <strong>'.$coin_id.'</strong> have be created, <br /> Please remove this image from Upload folder to continue further upload', $ilpage['settings'] );
							exit();
				}
			}
		}
 
		closedir($handle);
		if(isset($files_list) and count($files_list)>0)
		$moved_count=0;
//		for($i=0;$i<count($files_list);$i++)
	
	if(count($files_list)>0)
	{
	$ilance->db->query("UPDATE ".DB_PREFIX."concurrency_file_status  SET run_status=1 WHERE filename='bulk_upload' ");
		//for($i=0;$i<1;$i++)
		for($i=0;$i<count($files_list);$i++)
			{
			$file=$files_list[$i];
			//$new_file=explode("-",$file);
			//$item=explode(".",$new_file[1]);
			list($coin_id,$ext)=explode("-",$files_list[$i]);
			$coin_id=trim($coin_id);
			
		 
 	        $file_name=$file;
	 		$category_id=get_category_id($coin_id);
			$filetype=getmimetype(substr(strrchr($files_list[$i],'.'),1));
				//$filetype=mime_content_type($file);
			$filesize=filesize(DIR_SERVER_ROOT.BULK_UPLOAD_DIR.'/'.$files_list[$i]);
			$hash=md5(microtime());
			$is_item_photo=0;
			$sql=$ilance->db->query("SELECT attachid FROM ".DB_PREFIX."attachment where SUBSTRING(filename,1,LOCATE('.',filename)-1)='".substr($file_name,0,strpos($file_name,'.'))."'");
			 $ilance->db->num_rows($sql);
			//$sql=$ilance->db->query("select attachid from ".DB_PREFIX."attachment where filename='". $file_name."'");
			if($ilance->db->num_rows($sql)== 0)
			{
			$c=$file_name;
			$attachment_serial_no=substr($c,strpos($c,"-")+1,strpos($c,".")-strpos($c,"-")-1);;
				if($attachment_serial_no==1)
				 {
					$is_item_photo=1;
				 }else
				 {
					 //$is_item_photo=($coin_id_list[$i-1]!=$coin_id_list[$i])?1:0;
					 $is_item_photo=0;
				 }
				
				$ilance->db->query("insert into ".DB_PREFIX."attachment ( attachtype, category_id, date, filename, filetype, visible, filesize, filehash, coin_id, project_id,test_int,test_time) values ( 'slideshow', ".$category_id.", '".DATETIME24H."', '".$file_name."', '".$filetype."', 1, '".$filesize."', '".$hash."','".$coin_id."','".$coin_id."','int','".DATETIME24H."')");
				if($is_item_photo)
				{
				  $ilance->db->query("update ".DB_PREFIX."attachment set attachtype = 'itemphoto',test_update='itemphoto',test_time='".DATETIME24H."' where filehash = '".$hash."'");
				}
				if(!is_dir(DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id))
				mkdir(DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id,0777,true);
				
				rename(DIR_SERVER_ROOT.BULK_UPLOAD_DIR.'/'.$files_list[$i],DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach');
			}
			else
			{
			
				
			$ilance->db->query("update ".DB_PREFIX."attachment set filesize='".$filesize."', filehash='".$hash."', filename='".$file_name."', date ='".DATETIME24H."',test_update='upda',test_time='".DATETIME24H."' where filename='".$file_name."'");
				if(!is_dir(DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id))
				mkdir(DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id,0777,true);
				rename(DIR_SERVER_ROOT.BULK_UPLOAD_DIR.'/'.$files_list[$i],DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach');
				$thumbs=preg_split('/\s+/', trim(shell_exec('find /home/gc/public_html/image/ -name "'.$coin_id.'*"')));
	  			foreach($thumbs as $thumb)
	  			{
	  				//echo $thumb.'<br>';
	  				@unlink($thumb);
	  			}
				
			}
					//unlink($folder.'/'.$coinid.'/'.$file);				
			$moved_count++;
			//header('Location:bulk_upload.php');
			}
	$ilance->db->query("UPDATE ".DB_PREFIX."concurrency_file_status  SET run_status=0 WHERE filename='bulk_upload' ");
	}	 
	
	
	}
	else
	{
		print_action_failed('No image found for upload', $ilpage['settings'] );
		exit();
	}
	if($moved_count==0)
	{
			print_action_failed('No image found for upload', $ilpage['settings'] );
			exit();
	
	}else
	{
	print_action_success($moved_count.' images are being uploaded sucessfully', $ilpage['settings'] );
				exit();
	}
	
	}else
	{
	print_action_success("Blocked to prevent concurrency, if u are seeing this second time contact admin", $ilpage['settings'] );
				exit();
	}
	/*Tamil for bug 2675* Ends * Line no 27,28,30,32,141 * 24/05/13  */
	
	
}else
{
refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
exit();
}
function getmimetype($ext)
{
$mimes=array(
"ez" => "application/andrew-inset",
"hqx" => "application/mac-binhex40",
"cpt" => "application/mac-compactpro",
"doc" => "application/msword",
"bin" => "application/octet-stream",
"dms" => "application/octet-stream",
"lha" => "application/octet-stream",
"lzh" => "application/octet-stream",
"exe" => "application/octet-stream",
"class" => "application/octet-stream",
"so" => "application/octet-stream",
"dll" => "application/octet-stream",
"oda" => "application/oda",
"pdf" => "application/pdf",
"ai" => "application/postscript",
"eps" => "application/postscript",
"ps" => "application/postscript",
"smi" => "application/smil",
"smil" => "application/smil",
"wbxml" => "application/vnd.wap.wbxml",
"wmlc" => "application/vnd.wap.wmlc",
"wmlsc" => "application/vnd.wap.wmlscriptc",
"bcpio" => "application/x-bcpio",
"vcd" => "application/x-cdlink",
"pgn" => "application/x-chess-pgn",
"cpio" => "application/x-cpio",
"csh" => "application/x-csh",
"dcr" => "application/x-director",
"dir" => "application/x-director",
"dxr" => "application/x-director",
"dvi" => "application/x-dvi",
"spl" => "application/x-futuresplash",
"gtar" => "application/x-gtar",
"hdf" => "application/x-hdf",
"js" => "application/x-javascript",
"skp" => "application/x-koan",
"skd" => "application/x-koan",
"skt" => "application/x-koan",
"skm" => "application/x-koan",
"latex" => "application/x-latex",
"nc" => "application/x-netcdf",
"cdf" => "application/x-netcdf",
"sh" => "application/x-sh",
"shar" => "application/x-shar",
"swf" => "application/x-shockwave-flash",
"sit" => "application/x-stuffit",
"sv4cpio" => "application/x-sv4cpio",
"sv4crc" => "application/x-sv4crc",
"tar" => "application/x-tar",
"tcl" => "application/x-tcl",
"tex" => "application/x-tex",
"texinfo" => "application/x-texinfo",
"texi" => "application/x-texinfo",
"t" => "application/x-troff",
"tr" => "application/x-troff",
"roff" => "application/x-troff",
"man" => "application/x-troff-man",
"me" => "application/x-troff-me",
"ms" => "application/x-troff-ms",
"ustar" => "application/x-ustar",
"src" => "application/x-wais-source",
"xhtml" => "application/xhtml+xml",
"xht" => "application/xhtml+xml",
"zip" => "application/zip",
"au" => "audio/basic",
"snd" => "audio/basic",
"mid" => "audio/midi",
"midi" => "audio/midi",
"kar" => "audio/midi",
"mpga" => "audio/mpeg",
"mp2" => "audio/mpeg",
"mp3" => "audio/mpeg",
"aif" => "audio/x-aiff",
"aiff" => "audio/x-aiff",
"aifc" => "audio/x-aiff",
"m3u" => "audio/x-mpegurl",
"ram" => "audio/x-pn-realaudio",
"rm" => "audio/x-pn-realaudio",
"rpm" => "audio/x-pn-realaudio-plugin",
"ra" => "audio/x-realaudio",
"wav" => "audio/x-wav",
"pdb" => "chemical/x-pdb",
"xyz" => "chemical/x-xyz",
"bmp" => "image/bmp",
"gif" => "image/gif",
"ief" => "image/ief",
"jpeg" => "image/jpeg",
"jpg" => "image/jpeg",
"jpe" => "image/jpeg",
"png" => "image/png",
"tiff" => "image/tiff",
"tif" => "image/tif",
"djvu" => "image/vnd.djvu",
"djv" => "image/vnd.djvu",
"wbmp" => "image/vnd.wap.wbmp",
"ras" => "image/x-cmu-raster",
"pnm" => "image/x-portable-anymap",
"pbm" => "image/x-portable-bitmap",
"pgm" => "image/x-portable-graymap",
"ppm" => "image/x-portable-pixmap",
"rgb" => "image/x-rgb",
"xbm" => "image/x-xbitmap",
"xpm" => "image/x-xpixmap",
"xwd" => "image/x-windowdump",
"igs" => "model/iges",
"iges" => "model/iges",
"msh" => "model/mesh",
"mesh" => "model/mesh",
"silo" => "model/mesh",
"wrl" => "model/vrml",
"vrml" => "model/vrml",
"css" => "text/css",
"html" => "text/html",
"htm" => "text/html",
"asc" => "text/plain",
"txt" => "text/plain",
"rtx" => "text/richtext",
"rtf" => "text/rtf",
"sgml" => "text/sgml",
"sgm" => "text/sgml",
"tsv" => "text/tab-seperated-values",
"wml" => "text/vnd.wap.wml",
"wmls" => "text/vnd.wap.wmlscript",
"etx" => "text/x-setext",
"xml" => "text/xml",
"xsl" => "text/xml",
"mpeg" => "video/mpeg",
"mpg" => "video/mpeg",
"mpe" => "video/mpeg",
"qt" => "video/quicktime",
"mov" => "video/quicktime",
"mxu" => "video/vnd.mpegurl",
"avi" => "video/x-msvideo",
"movie" => "video/x-sgi-movie",
"ice" => "x-conference-xcooltalk" 
);
return $mimes[$ext];
}
function get_category_id($coinid=0)
{
global $ilance;
$sql=$ilance->db->query("select pcgs from ".DB_PREFIX."coins where coin_id='".$coinid."'");
$res=$ilance->db->fetch_array($sql);
return !empty($res['pcgs'])?$res['pcgs']:0;
}
function check_coin_id($coinid)
{
global $ilance;
$sql=$ilance->db->query("select *  from ".DB_PREFIX."coins where coin_id='".$coinid."'");
if($ilance->db->num_rows($sql)>0) 
{
return true;
}
else
return false;
}
?>
