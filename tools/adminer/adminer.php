<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 5.3.0
*/namespace
Adminer;const
VERSION="5.3.0";error_reporting(24575);set_error_handler(function($Dc,$Fc){return!!preg_match('~^Undefined (array key|offset|index)~',$Fc);},E_WARNING|E_NOTICE);$ad=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($ad||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$oj=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($oj)$$X=$oj;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Fb=adminer()->credentials();$J=Driver::connect($Fb[0],$Fb[1],$Fb[2]);return(is_object($J)?$J:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$He=substr($u,-1);return
str_replace($He.$He,$He,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
escape_string($X){return
substr(q($X),1,-1);}function
idx($va,$x,$k=null){return($va&&array_key_exists($x,$va)?$va[$x]:$k);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$Wg,$ad=false){if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){while(list($x,$X)=each($Wg)){foreach($X
as$_e=>$W){unset($Wg[$x][$_e]);if(is_array($W)){$Wg[$x][stripslashes($_e)]=$W;$Wg[]=&$Wg[$x][stripslashes($_e)];}else$Wg[$x][stripslashes($_e)]=($ad?$W:stripslashes($W));}}}}function
bracket_escape($u,$Ca=false){static$Xi=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($u,($Ca?array_flip($Xi):$Xi));}function
min_version($Ej,$Ve="",$g=null){$g=connection($g);$Qh=$g->server_info;if($Ve&&preg_match('~([\d.]+)-MariaDB~',$Qh,$A)){$Qh=$A[1];$Ej=$Ve;}return$Ej&&version_compare($Qh,$Ej)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_bool($je){$X=ini_get($je);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Dj,$N,$V,$F){$_SESSION["pwds"][$Dj][$N][$V]=($_COOKIE["adminer_key"]&&is_string($F)?array(encrypt_string($F,$_COOKIE["adminer_key"])):$F);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
get_val($H,$m=0,$tb=null){$tb=connection($tb);$I=$tb->query($H);if(!is_object($I))return
false;$K=$I->fetch_row();return($K?$K[$m]:false);}function
get_vals($H,$d=0){$J=array();$I=connection()->query($H);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$d];}return$J;}function
get_key_vals($H,$g=null,$Th=true){$g=connection($g);$J=array();$I=$g->query($H);if(is_object($I)){while($K=$I->fetch_row()){if($Th)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$g=null,$l="<p class='error'>"){$tb=connection($g);$J=array();$I=$tb->query($H);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.error()."\n";return$J;}function
unique_array($K,array$w){foreach($w
as$v){if(preg_match("~PRIMARY|UNIQUE~",$v["type"])){$J=array();foreach($v["columns"]as$x){if(!isset($K[$x]))continue
2;$J[$x]=$K[$x];}return$J;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$J=array();foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$Yc=$m["type"];$J[]=$d.(JUSH=="sql"&&$Yc=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($Yc,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($m,q($X)))));if(JUSH=="sql"&&preg_match('~char|text~',$Yc)&&preg_match("~[^ -@]~",$X))$J[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$J[]=escape_key($x)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,array$n=array()){parse_str($X,$Wa);remove_slashes(array(&$Wa));return
where($Wa,$n);}function
where_link($s,$d,$Y,$Tf="="){return"&where%5B$s%5D%5Bcol%5D=".urlencode($d)."&where%5B$s%5D%5Bop%5D=".urlencode(($Y!==null?$Tf:"IS NULL"))."&where%5B$s%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$e,array$n,array$M=array()){$J="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$wa=convert_field($n[$x]);if($wa)$J
.=", $wa AS ".idf_escape($x);}return$J;}function
cookie($B,$Y,$Oe=2592000){header("Set-Cookie: $B=".urlencode($Y).($Oe?"; expires=".gmdate("D, d M Y H:i:s",time()+$Oe)." GMT":"")."; path=".preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]).(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_settings($Bb){parse_str($_COOKIE[$Bb],$Uh);return$Uh;}function
get_setting($x,$Bb="adminer_settings"){$Uh=get_settings($Bb);return$Uh[$x];}function
save_settings(array$Uh,$Bb="adminer_settings"){$Y=http_build_query($Uh+get_settings($Bb));cookie($Bb,$Y);$_COOKIE[$Bb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==1))session_start();}function
stop_session($id=false){$wj=ini_bool("session.use_cookies");if(!$wj||$id){session_write_close();if($wj&&@ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Dj,$N,$V,$j=null){$sj=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($Dj=='mssql'||$Dj=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$sj,$A);return"$A[1]?".(sid()?SID."&":"").($Dj!="server"||$N!=""?urlencode($Dj)."=".urlencode($N)."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($j!=""?"&db=".urlencode($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Re,$if=null){if($if!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Re!==null?$Re:$_SERVER["REQUEST_URI"]))][]=$if;}if($Re!==null){if($Re=="")$Re=".";header("Location: $Re");exit;}}function
query_redirect($H,$Re,$if,$fh=true,$Kc=true,$Tc=false,$Ki=""){if($Kc){$ji=microtime(true);$Tc=!connection()->query($H);$Ki=format_time($ji);}$di=($H?adminer()->messageQuery($H,$Ki,$Tc):"");if($Tc){adminer()->error
.=error().$di.script("messagesPrint();")."<br>";return
false;}if($fh)redirect($Re,$if.$di);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
connection()->query($H);}function
apply_queries($H,array$T,$Gc='Adminer\table'){foreach($T
as$R){if(!queries("$H ".$Gc($R)))return
false;}return
true;}function
queries_redirect($Re,$if,$fh){$ah=implode("\n",Queries::$queries);$Ki=format_time(Queries::$start);return
query_redirect($ah,$Re,$if,$fh,false,!$fh,$Ki);}function
format_time($ji){return
sprintf('%.3f s',max(0,microtime(true)-$ji));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($qg=""){return
substr(preg_replace("~(?<=[?&])($qg".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($x,$Rb=false,$Xb=""){$Zc=$_FILES[$x];if(!$Zc)return
null;foreach($Zc
as$x=>$X)$Zc[$x]=(array)$X;$J='';foreach($Zc["error"]as$x=>$l){if($l)return$l;$B=$Zc["name"][$x];$Si=$Zc["tmp_name"][$x];$yb=file_get_contents($Rb&&preg_match('~\.gz$~',$B)?"compress.zlib://$Si":$Si);if($Rb){$ji=substr($yb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$ji))$yb=iconv("utf-16","utf-8",$yb);elseif($ji=="\xEF\xBB\xBF")$yb=substr($yb,3);}$J
.=$yb;if($Xb)$J
.=(preg_match("($Xb\\s*\$)",$yb)?"":$Xb)."\n\n";}return$J;}function
upload_error($l){$df=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($df?" ".sprintf('Maximum allowed file size is %sB.',$df):""):'File does not exist.');}function
repeat_pattern($Cg,$y){return
str_repeat("$Cg{0,65535}",$y/65535)."$Cg{0,".($y%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$Uc=false){$J=table_status($R,$Uc);return($J?reset($J):array("Name"=>$R));}function
column_foreign_keys($R){$J=array();foreach(adminer()->foreignKeys($R)as$p){foreach($p["source"]as$X)$J[$X][]=$p;}return$J;}function
fields_from_edit(){$J=array();foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$B=bracket_escape($x,true);$J[$B]=array("field"=>$B,"privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>1,"auto_increment"=>($x==driver()->primary),);}return$J;}function
dump_headers($Qd,$sf=false){$J=adminer()->dumpHeaders($Qd,$sf);$mg=$_POST["output"];if($mg!="text")header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Qd).".$J".($mg!="file"&&preg_match('~^[0-9a-z]+$~',$mg)?".$mg":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$J;}function
dump_csv(array$K){foreach($K
as$x=>$X){if(preg_match('~["\n,;\t]|^0|\.\d*0$~',$X)||$X==="")$K[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$K)."\r\n";}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){$J=ini_get("upload_tmp_dir");if(!$J){if(function_exists('sys_get_temp_dir'))$J=sys_get_temp_dir();else{$o=@tempnam("","");if(!$o)return'';$J=dirname($o);unlink($o);}}return$J;}function
file_open_lock($o){if(is_link($o))return;$q=@fopen($o,"c+");if(!$q)return;chmod($o,0660);if(!flock($q,LOCK_EX)){fclose($q);return;}return$q;}function
file_write_unlock($q,$Lb){rewind($q);fwrite($q,$Lb);ftruncate($q,strlen($Lb));file_unlock($q);}function
file_unlock($q){flock($q,LOCK_UN);fclose($q);}function
first(array$va){return
reset($va);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$q=file_open_lock($o);if(!$q)return'';$J=stream_get_contents($q);if(!$J){$J=rand_string();file_write_unlock($q,$J);}else
file_unlock($q);return$J;}function
rand_string(){return
md5(uniqid(strval(mt_rand()),true));}function
select_value($X,$_,array$m,$Ji){if(is_array($X)){$J="";foreach($X
as$_e=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($_e):"")."<td>".select_value($W,$_,$m,$Ji);return"<table>$J</table>";}if(!$_)$_=adminer()->selectLink($X,$m);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$J=adminer()->editVal($X,$m);if($J!==null){if(!is_utf8($J))$J="\0";elseif($Ji!=""&&is_shortable($m))$J=shorten_utf8($J,max(0,+$Ji));else$J=h($J);}return
adminer()->selectVal($J,$_,$m,$X);}function
is_mail($uc){$xa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Cg="$xa+(\\.$xa+)*@($gc?\\.)+$gc";return
is_string($uc)&&preg_match("(^$Cg(,\\s*$Cg)*\$)i",$uc);}function
is_url($Q){$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($gc?\\.)+$gc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$m){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~',$m["type"]);}function
count_rows($R,array$Z,$te,array$wd){$H=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($te&&(JUSH=="sql"||count($wd)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$wd).")$H":"SELECT COUNT(*)".($te?" FROM (SELECT 1$H GROUP BY ".implode(", ",$wd).") x":$H));}function
slow_query($H){$j=adminer()->database();$Li=adminer()->queryTimeout();$Yh=driver()->slowQuery($H,$Li);$g=null;if(!$Yh&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$Ce=get_val(connection_id(),0,$g);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$Ce&token=".get_token()."'); }, 1000 * $Li);");}}ob_flush();flush();$J=@get_key_vals(($Yh?:$H),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$dh=rand(1,1e6);return($dh^$_SESSION["token"]).":$dh";}function
verify_token(){list($Ti,$dh)=explode(":",$_POST["token"]);return($dh^$_SESSION["token"])==$Ti;}function
lzw_decompress($Ia){$cc=256;$Ja=8;$gb=array();$qh=0;$rh=0;for($s=0;$s<strlen($Ia);$s++){$qh=($qh<<8)+ord($Ia[$s]);$rh+=8;if($rh>=$Ja){$rh-=$Ja;$gb[]=$qh>>$rh;$qh&=(1<<$rh)-1;$cc++;if($cc>>$Ja)$Ja++;}}$bc=range("\0","\xFF");$J="";$Nj="";foreach($gb
as$s=>$fb){$tc=$bc[$fb];if(!isset($tc))$tc=$Nj.$Nj[0];$J
.=$tc;if($s)$bc[]=$Nj.$tc[0];$Nj=$tc;}return$J;}function
script($ai,$Wi="\n"){return"<script".nonce().">$ai</script>$Wi";}function
script_src($tj,$Ub=false){return"<script src='".h($tj)."'".nonce().($Ub?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($B,$Y=""){return"<input type='hidden' name='".h($B)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$Za,$Ee="",$Sf="",$db="",$Ge=""){$J="<input type='checkbox' name='$B' value='".h($Y)."'".($Za?" checked":"").($Ge?" aria-labelledby='$Ge'":"").">".($Sf?script("qsl('input').onclick = function () { $Sf };",""):"");return($Ee!=""||$db?"<label".($db?" class='$db'":"").">$J".h($Ee)."</label>":$J);}function
optionlist($Xf,$Ih=null,$xj=false){$J="";foreach($Xf
as$_e=>$W){$Yf=array($_e=>$W);if(is_array($W)){$J
.='<optgroup label="'.h($_e).'">';$Yf=$W;}foreach($Yf
as$x=>$X)$J
.='<option'.($xj||is_string($x)?' value="'.h($x).'"':'').($Ih!==null&&($xj||is_string($x)?(string)$x:$X)===$Ih?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($B,array$Xf,$Y="",$Rf="",$Ge=""){static$Ee=0;$Fe="";if(!$Ge&&substr($Xf[""],0,1)=="("){$Ee++;$Ge="label-$Ee";$Fe="<option value='' id='$Ge'>".h($Xf[""]);unset($Xf[""]);}return"<select name='".h($B)."'".($Ge?" aria-labelledby='$Ge'":"").">".$Fe.optionlist($Xf,$Y)."</select>".($Rf?script("qsl('select').onchange = function () { $Rf };",""):"");}function
html_radios($B,array$Xf,$Y="",$Mh=""){$J="";foreach($Xf
as$x=>$X)$J
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$Mh";return$J;}function
confirm($if="",$Jh="qsl('input')"){return
script("$Jh.onclick = () => confirm('".($if?js_escape($if):'Are you sure?')."');","");}function
print_fieldset($t,$Me,$Hj=false){echo"<fieldset><legend>","<a href='#fieldset-$t'>$Me</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$t');",""),"</legend>","<div id='fieldset-$t'".($Hj?"":" class='hidden'").">\n";}function
bold($La,$db=""){return($La?" class='active $db'":($db?" class='$db'":""));}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
pagination($D,$Ib){return" ".($D==$Ib?$D+1:'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>");}function
hidden_fields(array$Wg,array$Ud=array(),$Og=''){$J=false;foreach($Wg
as$x=>$X){if(!in_array($x,$Ud)){if(is_array($X))hidden_fields($X,array(),$x);else{$J=true;echo
input_hidden(($Og?$Og."[$x]":$x),$X);}}}return$J;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),(SERVER!==null?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
enum_input($U,$ya,array$m,$Y,$xc=null){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$Ye);$J=($xc!==null?"<label><input type='$U'$ya value='$xc'".((is_array($Y)?in_array($xc,$Y):$Y===$xc)?" checked":"")."><i>".'empty'."</i></label>":"");foreach($Ye[1]as$s=>$X){$X=stripcslashes(str_replace("''","'",$X));$Za=(is_array($Y)?in_array($X,$Y):$Y===$X);$J
.=" <label><input type='$U'$ya value='".h($X)."'".($Za?' checked':'').'>'.h(adminer()->editVal($X,$m)).'</label>';}return$J;}function
input(array$m,$Y,$r,$Ba=false){$B=h(bracket_escape($m["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r){$Y=json_encode($Y,128|64|256);$r="json";}$ph=(JUSH=="mssql"&&$m["auto_increment"]);if($ph&&!$_POST["save"])$r=null;$rd=(isset($_GET["select"])||$ph?array("orig"=>'original'):array())+adminer()->editFunctions($m);$dc=stripos($m["default"],"GENERATED ALWAYS AS ")===0?" disabled=''":"";$ya=" name='fields[$B]'$dc".($Ba?" autofocus":"");$Cc=driver()->enumLength($m);if($Cc){$m["type"]="enum";$m["length"]=$Cc;}echo
driver()->unconvertFunction($m)." ";$R=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($rd[""])."<td>".adminer()->editInput($R,$m,$ya,$Y);else{$Dd=(in_array($r,$rd)||isset($rd[$r]));echo(count($rd)>1?"<select name='function[$B]'$dc>".optionlist($rd,$r===null||$Dd?$r:"")."</select>".on_help("event.target.value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($rd))).'<td>';$le=adminer()->editInput($R,$m,$ya,$Y);if($le!="")echo$le;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$ya value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$ya value='1'>";elseif($m["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$Ye);foreach($Ye[1]as$s=>$X){$X=stripcslashes(str_replace("''","'",$X));$Za=in_array($X,explode(",",$Y),true);echo" <label><input type='checkbox' name='fields[$B][$s]' value='".h($X)."'".($Za?' checked':'').">".h(adminer()->editVal($X,$m)).'</label>';}}elseif(preg_match('~blob|bytea|raw|file~',$m["type"])&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($r=="json"||preg_match('~^jsonb?$~',$m["type"]))echo"<textarea$ya cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';elseif(($Hi=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$Y)){if($Hi&&JUSH!="sqlite")$ya
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$ya
.=" cols='30' rows='$L'";}echo"<textarea$ya>".h($Y).'</textarea>';}else{$ij=driver()->types();$ff=(!preg_match('~int~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A)?((preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0)):($ij[$m["type"]]?$ij[$m["type"]]+($m["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$m["type"]))$ff+=7;echo"<input".((!$Dd||$r==="")&&preg_match('~(?<!o)int(?!er)~',$m["type"])&&!preg_match('~\[\]~',$m["full_type"])?" type='number'":"")." value='".h($Y)."'".($ff?" data-maxlength='$ff'":"").(preg_match('~char|binary~',$m["type"])&&$ff>20?" size='".($ff>99?60:40)."'":"")."$ya>";}echo
adminer()->editHint($R,$m,$Y);$bd=0;foreach($rd
as$x=>$X){if($x===""||!$X)break;$bd++;}if($bd&&count($rd)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $bd);");}}function
process_input(array$m){if(stripos($m["default"],"GENERATED ALWAYS AS ")===0)return;$u=bracket_escape($m["field"]);$r=idx($_POST["function"],$u);$Y=$_POST["fields"][$u];if($m["type"]=="enum"||driver()->enumLength($m)){if($Y==-1)return
false;if($Y=="")return"NULL";}if($m["auto_increment"]&&$Y=="")return
null;if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($r=="NULL")return"NULL";if($m["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$r="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(preg_match('~blob|bytea|raw|file~',$m["type"])&&ini_bool("file_uploads")){$Zc=get_file("fields-$u");if(!is_string($Zc))return
false;return
driver()->quoteBinary($Zc);}return
adminer()->processInput($m,$Y,$r);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Lh="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=adminer()->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Sg="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$B</a>";echo"$Lh<li>".($I?$Sg:"<p class='error'>$Sg: ".error())."\n";$Lh="";}}}echo($Lh?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($mb,$Wh=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $mb, $Wh) }, onmouseout: helpMouseout});","");}function
edit_form($R,array$n,$K,$rj,$l=''){$vi=adminer()->tableName(table_status1($R,true));page_header(($rj?'Edit':'Insert'),$l,array("select"=>array($R,$vi)),$vi);adminer()->editRowPrint($R,$n,$K,$rj);if($K===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout'>".script("qsl('table').onkeydown = editingKeydown;");$Ba=!$_POST;foreach($n
as$B=>$m){echo"<tr><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$mh))$k=$mh[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($K!==null?($K[$B]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($K[$B])?implode(",",$K[$B]):(is_bool($K[$B])?+$K[$B]:$K[$B])):(!$rj&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);$r=($_POST["save"]?idx($_POST["function"],$B,""):($rj&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$rj&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ba!==false)$Ba=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ba);if($Ba)$Ba=false;echo"\n";}if(!support("table")&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($n){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($rj?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($rj?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."â€¦', this); };"):"");}echo($rj?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$y=80,$pi=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);return
h($A[1]).$pi.(isset($A[2])?"":"<i>â€¦</i>");}function
icon($Pd,$B,$Od,$Ni){return"<button type='submit' name='$B' title='".h($Ni)."' class='icon icon-$Pd'><span>$Od</span></button>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}@ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M‡±h´ÄgÌĞ±ÜÍŒ\"PÑiÒm„™cQCa¤é	2Ã³éˆŞd<Ìfóa¼ä:;NBˆqœR;1Lf³9ÈŞu7&)¤l;3ÍÑñÈÀJ/‹†CQXÊr2MÆaäi0›„ƒ)°ìe:LuÃhæ-9ÕÍ23lÈÎi7†³màZw4™†Ñš<-•ÒÌ´¹!†U,—ŒFÃ©”vt2‘S,¬äa´Ò‡FêVXúa˜Nqã)“-—ÖÎÇœhê:n5û9ÈY¨;jµ”-Ş÷_‘9krùœÙ“;.ĞtTqËo¦0‹³­Öò®{íóyùı\rçHnìGS™ Zh²œ;¼i^ÀuxøWÎ’C@Äö¤©k€Ò=¡Ğb©Ëâì¼/AØà0¤+Â(ÚÁ°lÂÉÂ\\ê Ãxè:\rèÀb8\0æ–0!\0FÆ\nB”Íã(Ò3 \r\\ºÛêÈ„a¼„œ'Iâ|ê(iš\n‹\r©¸ú4Oüg@4ÁC’î¼†º@@†!ÄQB°İ	Â°¸c¤ÊÂ¯Äq,\r1EhèÈ&2PZ‡¦ğiGûH9G’\"v§ê’¢££¤œ4r”ÆñÍDĞR¤\n†pJë-A“|/.¯cê“Du·£¤ö:,˜Ê=°¢RÅ]U5¥mVÁkÍLLQ@-\\ª¦ËŒ@9Áã%ÚSrÁÎñMPDãÂIa\rƒ(YY\\ã@XõpÃê:£p÷lLC —Åñè¸ƒÍÊO,\rÆ2]7œ?m06ä»pÜTÑÍaÒ¥Cœ;_Ë—ÑyÈ´d‘>¨²bnğ…«n¼Ü£3÷X¾€ö8\rí[Ë€-)Ûi>V[Yãy&L3¯#ÌX|Õ	†X \\Ã¹`ËC§ç˜å#ÑÙHÉÌ2Ê2.# ö‹Zƒ`Â<¾ãs®·¹ªÃ’£º\0uœhÖ¾—¥M²Í_\niZeO/CÓ’_†`3İòğ1>‹=Ğk3£…‰R/;ä/dÛÜ\0ú‹ŒãŞÚµmùúò¾¤7/«ÖAÎXƒÂÿ„°“Ãq.½sáL£ı— :\$ÉF¢—¸ª¾£‚w‰8óß¾~«HÔj…­\"¨¼œ•¹Ô³7gSõä±âFLéÎ¯çQò_¤’O'WØö]c=ı5¾1X~7;˜™iş´\rí*\n’¨JS1Z¦™ø£ØÆßÍcå‚tœüAÔVí86fĞdÃy;Y]©õzIÀp¡Ñû§ğc‰3®YË]}Â˜@¡\$.+”1¶'>ZÃcpdàéÒGLæá„#kô8PzœYÒAuÏvİ]s9‰ÑØ_AqÎÁ„:†ÆÅ\nK€hB¼;­ÖŠXbAHq,âCIÉ`†‚çj¹S[ËŒ¶1ÆVÓrŠñÔ;¶pŞBÃÛ)#é‰;4ÌHñÒ/*Õ<Â3L Á;lfª\n¶s\$K`Ğ}ÆôÕ”£¾7ƒjx`d–%j] ¸4œ—Y¤–HbY ØJ`¤GG ’.ÅÜK‚òfÊI©)2ÂŠMfÖ¸İX‰RC‰¸Ì±V,©ÛÑ~g\0è‚àg6İ:õ[jí1H½:AlIq©u3\"™êæq¤æ|8<9s'ãQ]JÊ|Ğ\0Â`p ³îƒ«‰jf„OÆbĞÉú¬¨q¬¢\$é©²Ã1J¹>RœH(Ç”q\n#rŠ’à@e(yóVJµ0¡QÒˆ£òˆ6†Pæ[C:·Gä¼‘ İ4©‘Ò^ÓğÃPZŠµ\\´‘è(\nÖ)š~¦´°9R%×Sj·{‰7ä0Ş_šÇs	z|8ÅHê	\"@Ü#9DVLÅ\$H5ÔWJ@—…z®a¿J Ä^	‘)®2\nQvÀÔ]ëÇ†ÄÁ˜‰j (A¸Ó°BB05´6†bË°][ŒèkªA•wvkgôÆ´öºÕ+k[jm„zc¶}èMyDZií\$5e˜«Ê·°º	”A˜ CY%.W€b*ë®¼‚.­Ùóq/%}BÌXˆ­çZV337‡Ê»a™„€ºòŞwW[áLQÊŞ²ü_È2`Ç1IÑi,÷æ›£’Mf&(s-˜ä˜ëÂAÄ°Ø*””DwØÄTNÀÉ»ÅjX\$éxª+;ĞğËFÚ93µJkÂ™S;·§ÁqR{>l;B1AÈIâb) (6±­r÷\rİ\rÚ‡’Ú‚ìZ‘R^SOy/“ŞM#ÆÏ9{k„àê¸v\"úKCâJƒ¨rEo\0øÌ\\,Ñ|faÍš†³hI“©/oÌ4Äk^pî1HÈ^“ÍphÇ¡VÁvox@ø`ígŸ&(ùˆ­ü;›ƒ~ÇzÌ6×8¯*°ÆÜ5®Ü‰±E ÁÂp†éâîÓ˜˜¤´3“öÅ†gŸ™rDÑLó)4g{»ˆä½å³©—Lš&ú>è„»¢ØÚZì7¡\0ú°ÌŠ@×ĞÓÛœffÅRVhÖ²çIŠÛˆ½âğrÓw)‹ ‚„=x^˜,k’Ÿ2ôÒİ“jàbël0uë\"¬fp¨¸1ñRI¿ƒz[]¤wpN6dIªzëõån.7X{;ÁÈ3ØË-I	‹âûü7pjÃ¢R#ª,ù_-ĞüÂ[ó>3À\\æêÛWqŞq”JÖ˜uh£‡ĞFbLÁKÔåçyVÄ¾©¦ÃŞÑ•®µªüVœîÃf{K}S ÊŞ…‰Mş‡·Í€¼¦.M¶\\ªix¸bÁ¡1‡+£Î±?<Å3ê~HıÓ\$÷\\Ğ2Û\$î eØ6tÔOÌˆã\$s¼¼©xÄşx•ó§CánSkVÄÉ=z6½‰¡Ê'Ã¦äNaŸ¢Ö¸hŒÜü¸º±ı¯R¤å™£8g‰¢äÊw:_³î­íÿêÒ’IRKÃ¨.½nkVU+dwj™§%³`#,{é†³ËğÊƒY‡ı×õ(oÕ¾Éğ.¨c‚0gâDXOk†7®èKäÎlÒÍhx;ÏØ İƒLû´\$09*–9 ÜhNrüMÕ.>\0ØrP9ï\$Èg	\0\$\\Fó*²d'ÎõLå:‹bú—ğ42Àô¢ğ9Àğ@ÂHnbì-¤óE #ÄœÉÃ êrPY‚ê¨ tÍ Ø\nğ5.©àÊâî\$op l€X\n@`\r€	àˆ\r€Ğ Î ¦ ’ ‚	 ÊàêğÚ Î	@Ú@Ú\n ƒ †	\0j@ƒQ@™1\rÀ‚@“ ¢	\$p	 V\0ò``\n\0¨\n Ğ\n@¨' ìÀ¤\n\0`\rÀÚ ¬	à’\rà¤ ´\0Ğr°æÀò	\0„`‚	àî {	,\"¨È^PŸ0¥\n¬4±\n0·¤ˆ.0ÃpËğÓ\rpÛ\rğãpëğópûñqñQ0ß%€ÑÑ1Q8\n Ô\0ôkÊÈ¼\0^—àÒ\0`àÚ@´àÈ>\nÑo1w±,Y	h*=Š¡P¦:Ñ–VƒïĞ¸.q£ÅÍ\rÕ\r‘péĞñ1ÁÑQ	ÑÑ1× ƒ`Ññ/17±ëñò\r ^Àä\"y`\nÀ Œ# ˜\0ê	 p\n€ò\n€š`Œ ˆr ”Q†ğ¦bç1Ò3\n°¯#°µ#ğ¼1¥\$q«\$Ñ±%0å%q½%Ğù&Ç&qÍ ƒ&ñ'1Ú\rR}16	 ï@b\r`µ`Ü\rÀˆ	€ŞÀÌ€dàª€¨	j\n¯``À†\n€œ`dcÑP–€,ò1R×Ÿ\$¿rIÒO ‚	Q	òY32b1É&‘Ï01ÓÑÙ ’Ó fÀÏ\0ª\0¤ Îf€\0j\n f`â	 ®\n`´@˜\$n=`†\0ÈÒv nIĞ\$ÿP(Âd'Ëğô„Äà·gÉ6‘™-Šƒ-ÒC7Rçà‡ —	4à ô-1Ë&±Ñ2t\rô\"\n 	H*@	ˆ`\n ¤ è	àòlÕ2¿,z\rì~È è\r—Fìth‰Šö€Ø ëmõäÄì´z”~¡\0]GÌF\\¥×I€\\¥£}ItC\nÁT„}ªØ×IEJ\rx×ÉûÂ>ÙMp‹„IHô~êäfht„ë¯.b…—xYEìiK´ªoj\nğíÅLÀŞtr×.À~d»H‡2U4©Gà\\Aê‚ç4ş„uPtŞÃÕ½è° òàÍL/¿P×	\"G!RîÎMtŸO-Ìµ<#õAPuI‡ëRè\$“c’¹ÃD‹ÆŠ €§¢-‚ÃGâ´O`Pv§^W@tH;Q°µRÄ™Õ\$´©gKèF<\rR*\$4®' ó¨ĞÈÊ[í°ÛIªó­UmÑÆh:+ş¼5@/­l¾I¾ªí2¦‚^\0ODøšª¬Ø\rR'Â\rèTĞ­[êÖ÷ÄÄª®«MCëMÃZ4æE B\"æ`ö‚´euNí,ä™¬é]Ïğtú\rª`Ü@hö*\r¶.Vƒ–%Ú!MBlPF™Ï\"Øï&Õ/@îv\\CŞï©:mMgnò®öÊi8˜I2\rpívjí©Æ÷ï+Z mT©ueõÕfv>f´ĞCàµTñM.±MŒ3Pv'ktõdO\rd€÷kõ¦µyWÂÀß‚¾ëV6àUÔÊ–€şöë-~X¯ºûîÀêÔöBGd¶\$iª %Ñqj EÎ—rLJ£Pr§%n=¢¦H®\"\"\"hí _\$b@átªä\0f\"Œén·H®*ŠæBv\$\$¬BÖ×@\"@r¯‚(\r`Ê îC÷XÇ(0&†.`ÒNk9B\n&#(Äêâ„@ä‚¯Ú«d—œ^÷Z®Šü £@²`ÒI-uƒ0£â\n–Bu‚4sGu§÷ÛtN©b÷u Ñub}×O~)uBàÁwë{ÂÀÅ5=w9[É«såï	8=Â\0\r ¾%`Ú]x&^Ø3scİƒ.\$åÍƒÀZ4¸4€÷u`¾ĞÀÇ„ãÛ… á„8;ƒwTøM„‰e†ÃÑ…Ãİ‡8X¤‚zlëK…U‡˜`ãĞø†«˜^XOØ¨m§@MŸ‰â‹€WûŠX‡…øjàßˆØ½Œ‰‡XG†˜T¤EˆÊ‹øHÂ€¿xŒÂƒYæa,øÃ¸ïÅŠ×o|„×t¸%ˆ˜Uj¨š·q7wX¶=Ø»Œdû‰xU’8ô\r€¾O«òù¥ş¿¯£‘¸iy˜c‹Ù.&Y%†9A„˜k“˜äd§Dz–9DèÄ‹Ä£[·¼ÓY×<Šxµ–X^V€Ï‚êDG˜‰gƒwÇ‰˜Àé™C…ù—I™`Æ§ù¢=Ù—zYOØ 6¦|x«—)«šâß‹8#“Ù„ée™ÙÙ™8Q™xó™Y´Ùé™ù©ùß›&K˜÷¥ˆÙÍùº(¹¾%ÙÂšr-——ÙÁJŸˆÀğ @¿˜Z ·…x——ùªDz!¢WÇÙÛ£yŸœÙÜ \0èrYy|b¿|Ê!£Yõyã˜ùó¦9Ÿ›™yzC+‘™€=ÔÙ¦âÃ§:]…s¢ø¨w‡Ÿša…úŒxùªf*ºqz™Ó§`[‡#’s¹y¢÷Š+u•XTÂ™ù‘øÙŒØc¬½•ë¬à¿C1¬8U©9«ì5šİ­Ç–`àúÍ¬™™®ø¹…Zó¯yg¦zÿ† Ú§ì®ºû”à¿°û°¸é¯Úã†# eË±Záˆû+²úÕÙp>Ç¡€L)€ºª‡Å±›O„+˜I±ØïµxìR Ë¯ºŸyB—y—»´¸I{z\0\rŠáœ„ı{k¤^º‚=®“©úµ£:§ƒÎ“„A¥2;Ÿ“ÚÁµ˜7È\$é;c®›­ˆšõ±;»…û]XX³kY¼#Ü5vT\\®Q¸å:À¢>ÀàÉ“Øk±û'¯»µ­[í“æa½Ä§¾0§xI˜[óü;û½\0[‹Ü²AÁZÅÁ»ï€e¸êâ?äec³ƒpÎ„ìÕƒ„œ:â#‚fCZÀ…SDv£ü.\rÃò#Ùß”„U ");}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M‡±h´ÄgÆÈh0ÁLĞàd91¢S!¤Û	Fƒ!°æ\"-6N‘€ÄbdGgÓ°Â:;Nr£)öc7›\rç(HØb81˜†s9¼¤Ük\rçc)Êm8O•VA¡Âc1”c34Of*’ª- P¨‚1©”r41Ùî6˜Ìd2ŒÖ•®Ûo½ÜÌ#3—‰–BÇf#	ŒÖg9Î¦êØŒfc\rÇI™ĞÂb6E‡C&¬Ğ,buÄêm7aVã•ÂÁs²#m!ôèhµårùœŞv\\3\rL:SA”Âdk5İnÇ·×ìšıÊaF†¸3é˜Òe6fS¦ëy¾óør!ÇLú -ÎK,Ì3Lâ@º“J¶ƒË²¢*J äìµ£¤‚»	¸ğ—¹Ášb©cèà9­ˆê9¹¤æ@ÏÔè¿ÃHÜ8£ \\·Ãê6>«`ğÅ¸Ş;‡Aˆà<T™'¨p&q´qEˆê4Å\rl­…ÃhÂ<5#pÏÈR Ñ#I„İ%„êfBIØŞÜ²”¨>…Ê«29<«åCîj2¯î»¦¶7j¬“8jÒìc(nÔÄç?(a\0Å@”5*3:Î´æ6Œ£˜æ0Œã-àAÀlL›•PÆ4@ÊÉ°ê\$¡H¥4 n31¶æ1Ítò0®áÍ™9ŒƒéWO!¨r¼ÚÔØÜÛÕèHÈ†£Ã9ŒQ°Â96èF±¬«<ø7°\rœ-xC\n Üã®@Òø…ÜÔƒ:\$iÜØ¶m«ªË4íKid¬²{\n6\r–…xhË‹â#^'4Vø@aÍÇ<´#h0¦Sæ-…c¸Ö9‰+pŠ«Ša2Ôcy†h®BO\$Áç9öw‡iX›É”ùVY9*r÷Htm	@bÖÑ|@ü/€l’\$z¦­ +Ô%p2l‹˜É.õØúÕÛìÄ7ï;Ç&{ÀËm„€X¨C<l9ğí6x9ïmìò¤ƒ¯À­7RüÀ0\\ê4Î÷PÈ)AÈoÀx„ÄÚqÍO#¸¥Èf[;»ª6~PÛ\rŒa¸ÊTGT0„èìu¸ŞŸ¾³Ş\n3ğ\\ \\ÊƒJ©udªCGÀ§©PZ÷>“³Áûd8ÖÒ¨èéñ½ïåôC?V…·dLğÅL.(tiƒ’­>«,ôƒÖœÃR+9i‡‡ŞC\$äØ#\"ÎAC€hV’b\nĞÊ6ğT2ƒewá\nf¡À6m	!1'cÁä;–Ø*eLRn\rì¾G\$ô2S\$áØ0†Àêa„'«l6†&ø~Ad\$ëJ†\$sœ ¦ÈƒB4òÉéjª.ÁRCÌ”ƒQ•jƒ\"7\nãXs!²6=ÎBÈ€}");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("':œÌ¢™Ğäi1ã³1Ôİ	4›ÍÀ£‰ÌQ6a&ó°Ç:OAIìäe:NFáD|İ!‘Ÿ†CyŒêm2ËÅ\"ã‰ÔÊr<”Ì±˜ÙÊ/C#‚‘Ùö:DbqSe‰JË¦CÜº\n\n¡œÇ±S\rZ“H\$RAÜS+XKvtdÜg:£í6Ÿ‰EvXÅ³j‘ÉmÒ©ej×2šM§©äúB«Ç&Ê®‹L§C°3„åQ0ÕLÆé-xè\nÓìD‘ÈÂyNaäPn:ç›¼äèsœÍƒ( cLÅÜ/õ£(Æ5{ŞôQy4œøg-–‚ı¢êi4ÚƒfĞÎ(ÕëbUıÏk·îo7Ü&ãºÃ¤ô*ACb’¾¢Ø`.‡­ŠÛ\rÎĞÜü»ÏÄú¼Í\n ©ChÒ<\r)`èØ¥`æ7¥CÊ’ŒÈâZùµãXÊ<QÅ1X÷¼‰@·0dp9EQüf¾°ÓFØ\r‰ä!ƒæ‹(hô£)‰Ã\np'#ÄŒ¤£HÌ(i*†r¸æ&<#¢æ7KÈÈ~Œ# È‡A:N6ã°Ê‹©lÕ,§\r”ôJPÎ3£!@Ò2>Cr¾¡¬h°N„á]¦(a0M3Í2”×6…ÔUæ„ãE2'!<·Â#3R<ğÛãXÒæÔCHÎ7ƒ#nä+±€a\$!èÜ2àPˆ0¤.°wd¡r:Yö¨éE²æ…!]„<¹šjâ¥ó@ß\\×pl§_\rÁZ¸€Ò“¬TÍ©ZÉsò3\"²~9À©³jã‰PØ)Q“Ybİ•DëYc¿`ˆzácµÑ¨ÌÛ'ë#t“BOh¢*2ÿ…<Å’Oêfg-Z£œˆÕ# è8aĞ^ú+r2b‰ø\\á~0©áş“¥ùàW©¸ÁŞnœÙp!#•`åëZö¸6¶12×Ã@é²kyÈÆ9\rìäB3çƒpŞ…î6°è<£!pïG¯9àn‘o›6s¿ğ#FØ3íÙàbA¨Ê6ñ9¦ıÀZ£#ÂŞ6ûÊ%?‡s¨È\"ÏÉ|Ø‚§)şbœJc\r»Œ½NŞsÉÛih8Ï‡¹æİŸè:Š;èúHåŞŒõu‹I5û@è1îªAèPaH^\$H×vãÖ@Ã›L~—¨ùb9'§ø¿±S?PĞ-¯˜ò˜0Cğ\nRòmÌ4‡ŞÓÈ“:ÀõÜÔ¸ï2òÌ4œµh(k\njIŠÈ6\"˜EYˆ#¹W’rª\r‘G8£@tĞáXÔ“âÌBS\nc0Ék‚C I\rÊ°<u`A!ó)ĞÔ2”ÖC¢\0=‡¾ æáäPˆ1‘Ó¢K!¹!†åŸpÄIsÑ,6âdÃéÉi1+°ÈâÔk‰€ê<•¸^	á\nÉ20´FÔ‰_\$ë)f\0 ¤C8E^¬Ä/3W!×)Œu™*äÔè&\$ê”2Y\n©]’„EkñDV¨\$ïJ²’‡xTse!RY» R™ƒ`=Lò¸ãàŞ«\nl_.!²V!Â\r\nHĞk²\$×`{1	|± °i<jRrPTG|‚w©4b´\r‰¡Ç4d¤,§E¡È6©äÏ<Ãh[N†q@Oi×>'Ñ©\rŠ¥ó—;¦]#“æ}Ğ0»ASIšJdÑA/QÁ´â¸µÂ@t\r¥UG‚Ä_G<éÍ<y-IÉzò„¤Ğ\" PÂàB\0ıíÀÈÁœq`‘ïvAƒˆaÌ¡Jå RäÊ®)Œ…JB.¦TÜñL¡îy¢÷ Cpp\0(7†cYY•a¨M€é1•em4Óc¢¸r£«S)oñÍà‚pæC!I†¼¾SÂœb0mìñ(d“EHœøš¸ß³„X‹ª£/¬•™P©èøyÆXé85ÈÒ\$+—Ö–»²gdè€öÎÎyİÜÏ³J×Øë ¢lE“¢urÌ,dCX}e¬ìÅ¥õ«mƒ]ˆĞ2 Ì½È(-z¦‚Zåú;Iöî¼\\Š) ,\n¤>ò)·¤æ\rVS\njx*w`â´·SFiÌÓd¯¼,»áĞZÂJFM}ĞŠ À†\\Z¾Pìİ`¹zØZûE]íd¤”ÉŸOëcmÔ]À ¬Á™•‚ƒ%ş\"w4Œ¥\n\$øÉzV¢SQDÛ:İ6«äG‹wMÔîS0B‰-sÆê)ã¾Zí¤cÇ2†˜Î´A;æ¥n©Wz/AÃZh G~cœc%Ë[ÉD£&lFRæ˜77|ªI„¢3¹íg0ÖLƒˆa½äcÃ0RJ‘2ÏÑ%“³ÃFáº SÃ ©L½^‘ trÚîÙtñÃ›¡Ê©;”Ç.å–šÅ”>ù€Ãá[®a‡N»¤Ï^Ã(!g—@1ğğó¢üN·zÔ<béİ–ŒäÛÑõO,ÛóCîuº¸D×tjŞ¹I;)®İ€é\nnäcºáÈ‚íˆW<sµ	Å\0÷hN¼PÓ9ÎØ{ue…¤utëµ•öè°ºó§½ 3ò‡î=ƒg¥ëº¸ÎÓJìÍºòWQ‡0ø•Øw9p-…Àº	ı§”øËğÙ'5»´\nOÛ÷e)MÈ)_kàz\0V´ÖÚúŞ;jîlîÎ\nÀ¦êçxÕPf-ä`CË.@&]#\0Ú¶pğyÍ–Æ›ŒtËdú¶ Ãó¼b}	G1·mßru™ßÀ*ñ_ÀxD²3Çq¼„BÓsQæ÷u€ús%ê\nª5s§ut½„Â{sòy¥€øNŸ¯4¥,J{4@®ş\0»’PÄÊÃ^ºš=“¯l„“²`èe~FÙ¡h3oé\"¤”q·R<iUT°[QàôUˆÇM6üT. ºê0'pe\\¼½ôŞ5ßÖÌ”pCe	Ù•Ô\"* M	”¨¦–D™ş±?ûhüØ2¡ĞãzU@7°CÓ4ıaµ²iE!fË\$üB¤…<œ9o*\$¯ælH™\$ Å@ààÊæ€P\rNÀYn<\$²	ÀQ…=F&¥ *@]\0ÊÏË W'dÖ z\$æĞjĞP[¢ö\$òä¯Ğ0#& _Ì`+†B)„wŒv%	âÔ›LcJ„€RSÀÂi`ÌÅ®	F€W	êË\nBP\nç\r\0}	ï¦®0²Zğ¸‚ò/`j\$«: §8ieüÀØÏ†xâ¹Â±îa ¬GnøsgO¢äU%VU°†@‚NÀ¤Ïúd+®(oJï†@XÆèàzM'FÙ£àWhV®I^Ù¢™1>İ@Ğ\"î¨¤‰ ÈQñR!‘\\¢`[¥¤«¨‰.Ø0fb†F;ëÂ‡çFpÏp/t`Â ô®(§ÀVé¸ø b“È²‰(€ˆHˆl‚œÁÎÔ¯1v­Ş‘€ğHĞï1Tï3ñ“q›àÉ1¦ÑªfË\nT\$°éàNq+Ëí`ŞvÖÇœï\rüVmûÇr°¨Ø'Ï¸±ñg%«\"Lˆm¼…‘(’(CLzˆ\"hâXØm= \\H\n0U‡‚ f&M\$¤g\$ñU`a\rPş>`Ë#gªhôî`†R4H€Ñ'ç©­³²GK;\"M¶Û¨TŒhµBEn\"b> Ú\rÀš©#›\0æ•N:í#_	QQ1{	f:BËÂáRª&àÜã)JµÄBr¹+ÂK.\$ĞPqõ-r®S%TIT&Qö·Ò{#2o(*P¯â5ï`„1H…®¢'	<Tğd±÷ª¾sÀì,NÚÊ ÒÉÔì^\r%ƒ3îĞ\r&à“4Bì/\0ĞkLH\$³4dÓ>ŠàÒ/³à¶µ€Hö€·* ºù3JÇĞ¥<†Hh©pú'‚çO/&ï2I.îx3V.¢s5Óe3íªÛZÛ(õ9E”g§;R—;±J½‘QÃ@ªÓvgz@¶“‚Şó†'dZ&Â,Uã²ßò¦F æb*²D‹òH! ä\r’;%‡x'G#°šÍ w‰Á#°Ö È2;#òBvÀXÉâ”aí\nb”{4K€G¦ß%°†ÒGuE`\\\rB\r\0¨-mW\rM\"¶#EôcFbFÕnzÓóÿ@4JÈÒ[\$Êë%2V”‹%ô&TÔV›ˆdÕ4hemN¯-;EÄ¾%E¥E´r <\"@»FÔPÂ€·L Üß­Ü4EÉğ°ÒÄz`ĞuŒ7éNŠ4¯Ë\0°F:hÎKœh/:\"™MÊZÔö\r+P4\r?¤™Sø™O;B©0\$FCEp‚ÇM\"%H4D´|€LN†FtEÑşgŠş°5å=J\r\"›Ş¼5³õ4à¾KñP\rbZà¨\r\"pEQ'DwKõW0î’g'…l\"hQFïC,ùCcŒ®òIHÒP hF]5µ& fŸTæÌiSTUS¨ÿîÉ[4™[uºNe–\$oüKìÜO àÿb\" 5ï\0›DÅ)EÒ%\"±]Âî/­âÈĞŒJ­6UÂdÿ‡`õña)V-0—DÓ”bMÍ)­šŠïÔ¯ØıÄ`Šæ%ñELtˆ˜+ìÛ6C7jëdµ¤:´V4Æ¡3î -ßR\rGòIT®…#¥<4-CgCP{V…\$'ëˆÓ÷gàûR@ä'Ğ²S=%À½óFñk: ¢k‘Ø9®²¤óe]aO¼ÒG9˜;îù-6Ûâ8WÀ¨*øx\"U‹®YlBïîöò¯ğÖ´°·	§ı\n‚îp®ğÉlšÉìÒZ–m\0ñ5¢òä®ğOqÌ¨ÌÍbÊW1s@ĞùKéº-pîûÆE¦Spw\nGWoQÓqG}vp‹w}q€ñqÓ\\Æ7ÆRZ÷@Ìì¡t‡ıtÆ;pG}w×€/%\"LE\0tÀhâ)§\r€àJÚ\\W@à	ç|D#S³¸ÆƒVÏâR±z‰2Ïõövµú©–‘	ã}¨’‡¢¯(¸\0y<¤X\r×İx±°‹q·<µœIsk1Sñ-Q4Yq8î#Şîv—îĞd.Ö¹S;qË!,'(òƒä<.è±J7Hç\"’š.³·¨ñuŒ°‡ü€#ÊQ\reƒrÀXv[¬h\$â{-éY °ûJBgé‰iM8¸”'Â\nÆ˜tDZ~/‹b‹ÖÕ8¸\$¸¸DbROÂOÆû`O5S>¸ö˜Î[ DÇê”¸¥ä€_3Xø)©À'éÄJd\rX»©¸UDìU X8ò•x¯-æ—…àPÌN` 	à¦\nŠZà‹”@Ra48§Ì:ø©\0éŠx°†ÖN§\\ê0%ãŒ·f“˜\\ ğ>\"@^\0ZxàZŸ\0ZaBr#åXÇğ\r•¨{•àË•¹flFb\0[–Şˆ\0[—6›˜	˜¢° ©=’â\n ¦WBøÆ\$'©kG´(\$yÌe9Ò(8Ù& h®îRÜ”ÙæoØÈ¼ Ç‡øƒ†Y£–4Øô7_’­dùã9'ı‘¢ú Üúï²ûz\r™ÙÖ  Ÿåğşv›G€èO8èØìMOh'æèXöS0³\0\0Ê	¸ı9s?‡öI¹MY¢8Ø 9ğ˜üä£HO“—,4	•xs‘‚P¤*G‡¢çc8·ªQÉ ø˜wB|Àz	@¦	à£9cÉK¤¤QGÄbFjÀXú’oSª\$ˆdFHÄ‚PÃ@Ñ§<å¶´Å,‚}ï®m£–rœÿ\"Å'k‹`Œ¡cà¡x‹¦e»C¨ÑCìì:¼ŞØ:XÌ ¹TŞÂÂ^´dÆÃ†qh¤ÎsÃ¹×LvÊÒ®0\r,4µ\r_vÔLòj¥jMáb[  ğƒlsÀŞ•Z°@øºäÁ¶;f”í`2Ycëeº'ƒMerÊÛF\$È!êê\n ¤	*0\rºAN»LP¥äjÙ“»»¿¼;Æ£VÓQ|(ğ‰3’†ÄÊ[p‰˜8óú¼|Ô^\räBf/DÆØÕÒ Bğ€_¶N5Mô© \$¼\naZĞ¦¶È€~ÀUlï¥eõrÅ§rÒ™Z®aZ³•¹ãøÕ£s8RÀGŒZŒ w®¢ªNœ_Æ±«YÏ£òm­‰âªÀ]’¦;ÆšLÚÿ‚º¶cø™€û°Å°ÆÚIÀQ3¹”Oã‡Ç|’y*`  ê5ÉÚ4ğ;&v8‘#¯Rô8+`XÍbVğ6¸Æ«i•3Fõ×EĞô„Øoc82ÛM­\"¶˜¹©G¦Wb\rOĞC¿VdèÓ­¤w\\äÍ¯*cSiÀQÒ¯“ã³R`úd7}	‚ºš)¢Ï´·,+bd§Û¹½FN£3¾¹L\\ãşeRn\$&\\rôê+dæÕ]O5kq,&\"DCU6j§pçÇÉ\\'‚@oµ~è5N=¨|”&è´!ÏÕBØwˆHÚyyz7Ï·(Çøâ½b5(3Öƒ_\0`zĞb®Ğ£r½‚8	ğ¢ZàvÈ8LË“·)²SİM<²*7\$›º\rRŒb·–âB%ıàÆ´Ds€zÏR>[‚Q½ŒĞ&Q«¨À¯¡Ì'\r‡ppÌz·/<‹‡}L¢#°Î•ÂĞâZ¹ã²\"tÆï\n„.4Şgæ«Pºp®Dìnà¥Ê¹NÈâFàd\0`^—åä\rnÈ‚×³#_âÄ w(ü2÷<7-ªXŞ¹\0··s¬ø,^¹hC,å!:×\rK„Ó.äİÓ¢¯Å¢ï¹ÔØ\\„ò+v˜Zàê\0§Q9eÊ›ËEöw?>°\$}£·D#ªğã cÓ0MV3½%Y»ÛÀ\rûÄtj5ÔÅ7¼ü{ÅšLz=­<ƒë8IøMõ°•õâGØÑÎŞLÅ\$’á2‰€{(ÿpe?uİ,Rïd*Xº4é®ı¿‡Í\0\"@Šˆš}<.@õ’	€ŞN²²\$î«XUjsİ/üî<>\"* è#\$Ôş÷Õ&CPI	ÿèt¿áùü¦î?è †´	ğOËÇ\\ Ì_èÎQ5YH@‹ŠÙbâÑcÑhî·ùæë±––…O0T©' 8¡wü»­öj+H€v_#º„íïì06ÈwÖœX†à»d+£Ü“\\Àå–\n\0	\\ğŸŸ>sî…ÓšA	PFöd8m'@š\nH´\0¬cèOwSßØ’—Yá`²ˆˆ¨¢R×ıDna\" ì™~Â?Ámğ†|@6ä½+ìGxV’ä\0°‰WƒÓ°’nw”„‘.¡Øƒb«Ÿ9Ã¸ˆEÈ|E·ÃÂ\rĞˆr¬\"Ğøx„‘¸-¸êŠâš\rN6n·\$Ò¬ı-BíHæ^Ó)â¥y&ãã×šW–Ç§àbv…Rì	¸¥³N\0°Ànâ	T„–`8X¬ğA\r:{Oş@\" Œ!Á¤\$KÂäqoĞËjYÖªJ´şÂíÜh}d<1IÇxdŠÊÎTT4NeeC0ä¥¿‡:D›FÚ5LŞ*::H”jZå—­FõRªMÖ€nS\n>POó[Œ\$V8;#‰K\\'ùBÖè»R®Ø¯°›RÑ_8Ájé*Ej \\~vÆÂĞvÄÛp@T€X‹\0002dE	…Hí‡Vğñ×D”\"Q'EDJB~A´ƒA¤Il*'\n¶Yå.è›+©9¾ñpg†ƒÒ/\"¸1—8Ä0„IAÊFCÈ¨ŠV*a™èPÀdÖĞ£5H\" AØå6İs¬YİØ;è¨È/¨¸0ãv}y˜\rÍƒâÎ×¥1…u\"Ë‹Šmãñ_º0ç„„`ß¯¿\\B1^\nk\r]lhø}]HBW`±—0½ê¨¹rFf€)”W,ÕÒ§]sm9'O¢xÔ½Í,ê9J8§£? 4ÉÉï¡\"Ò…èÛ½Ì<Ñ-S¨ÉÃşMÃ;ĞvÌñ6y|„ZòÁ‹¨%àa•#8¢ˆTC‘!pºË\nØïCZ(ï½wéØa– ·ˆÁ?9|€ó0<BL\r‰\nˆ]ÀPB0¤&‘+tÄHƒñÖ…àDx^÷î³,Lğ}[¦ÄBñx}½ĞruĞË\0¾€\0005‹åS@\"UØ”@Ü°\0€\$äÁŞ\"Ò ŸÄ]l/	ùíIâB4¯™.Â6 Â…ˆd7ˆ\r@=‘ªß¬¢ÕÛ*G jŒ¬Šüf`»:Hnì‘ÔbÄ€71Çê)C<@AÍY#°¦¡ëÑe’oâÖY!ÅÊI’DM¼\nlt¨“€/)˜\\43)®Ù2ï­É¸Ó)ÁŒ²f[ ppp1€µ©#“‰Ã¶p\0Ä§Å“l›À^{€„Aœ¤THå6ÖÊ«è\n\0PâH€.\r›’|ÀTFD0ŠS€y”ğÀÏ'1Ö´¤K’² dØµ±¯ÄBş”™Cç&Å)şW€s Hee+@4– r·“áÛš*Lp1<üf‚N–Y'­-	XKVa¦–L­¥ö\"›€Œ\"ìl•£q…É.YJHàm HV/lCŞ&àÀH)oÁ&\\2Äœ­%âáéz\n^Q(6ì˜D€ ÈûJq°–á«\00a#Ë6\0vr,»MÌú&A„Ôòìœ»‰9%YdBêhÀÖ!W\0êb\r{˜”Æ@Ç1¹‹I¬22AÚÚ)™H¾a@r’0GÉÜ7Dd.LM˜<˜ã2ĞÈË,k/™Meª¹œ}Ò’3ä=\0Ğ&É‹B‰ø\nPd.\"ÈñF3XÈSd(*¨J6 ä‡‹–F:¬×)1Â1á?lQ&Ïùµ¬h<JÍ‹¤f‡d–EÕº*ñx\n\0¼À.\"B -…#£ÀÎ—t¿IÎ«õ›Ğ	I8 ²’8dh	«èƒx€Ÿ§~°ƒ	L!K(úBXµ£-Èì‘hÎåc/Öræ×PÕIõ«NÊ2È|Éç×¶ŸÒ|\"µM‘'¡K,\\H°Ée5*o]4—ÒFP	2›Í<)ˆT¾“o˜À\n¢¸ØI¶Ú¢Ä!¨(øˆ‰_8Xrç;uŠúàØNJù„¡ˆé[rû˜DC:¸@ÁÍ³Àlœ\0©e\\*x@AÈ¡&í(‘5Ã×,ªŠ˜#1xÀ º!T D„ª­(QƒŸáDJ|D D:\0ÉAÙĞ¹Ô ÁbaEÓ?rn°²WkxŒøX=i‡,\$3[‚r™9B•Æ±§dã¡ş\0ºÔH‘4­«É<(zÊºô?àsIbJ©g UÂ\n(}¨Š›J\"à¦A™€BĞ19…~ÅIé#Ú\$¹‘%d  e\"µ`Àìátª¨•'O= À @\$µˆO”\nmT×o+Zäñ™ø-­„¢êßPF?Ò_…I¤JËX Ä£2Â¢ê-V¶;ª?2¥Áá0¡*P3Éªõë_T<E¥JÅ\\(İ2ô €Ø)êIQ‘Šé¬©·óÉRŒL&¥Í!È¯KÁiÑ†’t»¤°ÎKúHRl¢È¬Es“¶‰…¿¤DøŠxÇ´¬i¾ºÖ!faBÉñó¼FÔËe>€Vç©É-QjÂI‘Å7§˜ş\"%RhÈ g£áMŒ³ø«Õ-b£58RÂ‹¨„¯Ä*ã§9ÔÆêŠ°«·Ô9¤2Q0ı‡¬IR[üZ£İN\0÷ÇÂ20£¡ŒÂĞ\\[@áQ\0¤ÔJx„ùµ…äEC{©â\$lp1=\0·RĞ¾É>E~ßÆê×„ˆÑ:0À˜%€R+)\0°	Æ‘Qá@(\"¡_jT•X\0˜„ì\r1“\0P“9#\0”ÍôòH;Bª|À™²LöZ‘¼ÆŠ‹6ù/B’à\nB{ñğà|HÄ,á	*;œ(õ`Ê2@6ª>¡	å?P\0/„¹ó\0|\\ÅeBÜ`›’jq©U/\rc©üêÔÒ†¤6(N\0º/\$à\n8µj*U…\$›ñºŠy*³=¬;ˆ„ğŸ\$f¬â8XØBCEşœr\"/Ÿàª‚kÚ%\\9k§ùèBšœğ0§F­À(¬ğ'ôUôªµÆ®m¤@k‰T\0Õ¹EáÍsEhyòe\nä) )“b7ªã(W%,ÈJ¤r¨ó2D¶rhEùŸ\n0Qê3Š U9TPOÀŠÕô‘°8j|¤}ÃR<0‹Èâ™Zl ĞØTáö°ŒÈÙÚ*¯\$ÎÀU\rÛ\"¤.ª Ts~Ë~(ğ3€aº¨œ@ˆÕ+là`:Î`­:O…iùBXÁ?Ê„¦é7‰¾Lj|Í:n—K:Ø²}²\0İÉUMc`P%nn\n,ì4á™Q'%+H.è‹\"#GĞ3`¥¡İèİ\n1fg\0Ğœ'¼k¦²qxD<\"Œ,a|{~şó¸ÜC<S»i•Bï\nkNş ÖG³}’Óàk:„–Îî­ÀİgÛ)˜JD°ˆ•hÃ›f¢\"™kV~³ámM`HO”kD‹¬^ˆ0/tj«l³\rŒ!Ïf<ÀGôÛTºÕvµ#@­ek@2«wéı´0ÜÜ­tÄÙ€Ä¯1ÄuÌyvË%8±?1¼ÛÊlæ×xtÇœmp­›fK3ZÜJ£=\0@—^p·ÂÛ‘¹¶æ³ø]Ò²'ëtÙ¡@C·bëå\r[ÈãVôµ-½ÀËo“-œ¦İ e·}ÀéYªÜ	-é‡-m³I\0+ƒÍVßDÛ[B+€ç(-Ù4ä«>®qè–i>=½î‡/0-¦cL“pJ b\ndáò)â«#áGËs­·ä\"ÒQĞN“œøˆ`.úÈÔyÈEtPŠqÔI]ó¤ëJ8¼€»rWTÅÁIµè‹f÷aG„.ë–„7yçËlÙÕA€³7'¥1	âS€-ÙxI§œm·ËÂL:eÎ‰AÆWøİÎ¶EIİâ—Wz€Ô3Wòı°)*/)CÊÇÿx*c]ì%÷}½âÅ»_ÏÌIvÍ²½'˜\$U÷İS4k”5WÊJC®˜ 7*œb%<WC@Â“Æ	À¼©»c{Ş´«ò”¬3)Xò˜&&¢eLìI”å¢,Nì 2k#p5 €´f4«ˆöÇºëz¯#â½ä\\®ºà¡ûNøbÔUˆğoyğ€ÈSÕ4¾`qÓ~1–=ì8å‰¸*áOOJêC¡ñ®âÚè'Dd,@kLñ¹à¤ƒ÷”\\âj2Í©Äê±<³@_q÷2Ÿ\0‚Õ±Á)`˜˜Àêı•s°±óF\0¡ÓâÀÖ\n­‚Fš×<*Àx*•Àë`ÔàÁ-ƒŸ\røˆ‡|@ÑñÔ7ğH@w€óÿ‰H]µå˜\0¶àü_w¾µh0!Ës¢1Ï¾¦Ç¬„hW°€.Ãê=WªR*÷A_Æ”åEDÔ· ?1,UbÌ9=tÈ4Ã¨¤äWˆ¢^åäÙ;‘ßè±Ì@™ò(1<DâEÌ‚Hx©T()0zŠ`Ñ_Ğ;¨›ALé±)\nÌK[fˆH—Œ‰Wo—@bBKÀiMŠ±Ãd+ï>èvI¶(z:äİ.İ€À 9uiÑ¤DYÖâ¾ûÉO`ö®á]I\0Œ°RÄ†,K,÷¨ã6L¸Ä\"\"£1gª(•­†|T.,ñ9vb+\rk]u¶&è©|©åb£SÍÅd[¼,gêèaJº(CÄök¤”\rFØÂ“+	€ñŒ9âÂL©¹)Â)UAßB‰U†hÂgà’c3xñ-n9±úü»äxÈ®2¯´q¬ibÖrY7é€kÌyìfˆ, §¼àÎ)¬Ùª¤J:«NÂ8ÜRcly\nõ¼2ÅWô;¬.>Åv6Q#A0­ê{Î­iùï7~@VXÀ…¢^¿å11-É+Ïv|£Ü]Vf¸¢û.›{	áÒÀ\r·§;ê1lp°/ÙõuF‘Çd‰\$PĞ®0=@kSÆ0h›ÁÉˆÂœ@‘Ñ/*(OæV.•´G>‰(rËÎ!˜6àª÷…®òY=XZ@Â:²'&06kE|š“Ï'|H;“¼æNò€gÒ%ËW™+Âæ¯4ù;Íƒ¯¯'x|f©9­ÌÚ(O¨ğd¦§é·w%9]¦×f}ÌÃGÖÔÄs¦µçÂ¾óÓ…÷XM0ÍéŒ†gQ·ª¶8Ì„ù+O}¶Í0}’9„ÖĞŞ»–ßNhÎ/mgDé“s…°ü¦Äà\nÍ74å‹³P~}O)©UgÜ9ùÉÖj§8Pœ„İ¸Á(Ğ%ÄóöÛjŞ7oAB×Ği)ˆüKò„½Ùu¤ë´ …}s±1è=odİV[Ä´\n¬ç²zl€MĞ·r:F#{Öğ*#°xœÜÜ°¯<Ds½™k/mw :^æë¦âÉ1¿ÄÏD¨˜2ºz*Ñòn’ª%ôŞåÓÚiâÃ™ *Ê!8-·á¦tH•'í„ã\rÍĞºĞ4™äİ8`‚¿\"”¡»»i]’ZZœ>Z\0Ş¦9û”ìÚ+äŸ‚~†á\$Ş­„€LÄP\\ì‡XA©¬ èÀóŒÍišççzÒhÂ\$÷Â‹SMÚT'•š„1×èÏDÍâ	˜Ë5E©\0Ä\$ãttÔ®¥ì:\rMÆ·S¦šÓ––lsªˆAfÖKàk,N…lÛD^zz²dS˜®/rt²Nù>ıo%i¥½\0J¯B©po¢ÜR“™Ãê/Ö˜Ù«x\nyœ+«ì,e4‚Îq5Q'JDˆ]¿B@mÓ´ÈÃR§Ski~úÜÎ¶t0ç[ 1€z	••&×û^“\nOÕ¶²ÉV÷ëÀ³GV@T*ŞH9ÑÏ‰G0\0'Ö`Ñ°\r‡åûbQKsLd…*;\ná×æÁ.Ä”UNpà,Lâ@TRàe øb€œFÀø˜yŸn> IKÀ¶rGû	@Ù‚?cI’İ“u%GöOô1„ ÖCöh¦5Tüy„üI­Ù:\\0¼àX¥Ë>öÊŠ0ËŞ¾ûQB¶‡©EI/-LBTÚ!bïœ÷6ìÿk`jp\0K„„Â>kƒdšâÄ/•äISk.+*Íû¡R›|gR¡ıøW\\wùÂÓtà.)¤^Zc8ÕZ€~FÀ°SÇµÔSmÌ•;b>\0jz=î¢T'Á>Ìåq‹y}:»u§µ&åÀWºDQ¢Ïc-ªËşÇ6<[‡e÷x›Ø èĞî[ú¹L©\0wmùl°t•zëç<S€&ğådbÜxÍúoiâgK©\r`ÖÂµÔ?D5u@b‘„N¸àO•ğ¤·¤ˆíøYÔ[õŸè£Àñ{ÃNïœré‰ût±¾ó\0ïÅtMsšcBW?°*Dƒ.põ€¤'2•Ge\rp*#­e¹ĞêÚÅCıÓø\"³QI\nˆ‚hiøQÁ@Œ™á\rl	ˆß´à_.‡¤Êt*á^œøsÁ9ğ€ïWhqÕê¸~,¤áYÎ¸‚ÄdQsÂ¦\r‡BjºõDÿÇ¡ ñ<<T)C´\n¶Šø°Í&¹D{\rĞlÖğÑ-RãÊ\r@rk§é–Ï¢ø +ZíûïP¾ÛÖÎèéu8È¨ôÇ€ÚsãÙˆŠøóoç#äÊg€Èuï›¹\$F&\n-v\"PÜÎæ¶Ûjšnntë1ßV®§»¥öêAwbxß„ÄDÑ5áÍ-Ô0³aœ\0\r§/!ÈI¢Ñúí|/‰‚‚Şh…án„Gf-Mdnaˆ^(eïa´¤Â¨·YŞÏZ,†S€EöN‘ƒ\\§Õó›¸=Ò4~MÍ´¸\rÆëıÒFt•Å¦ñu\"|`ÑÒEá²ÀRózœÂDÌ`â{Äè@“k/KæY¹šŠ®3sJ¡äƒ¿5XGÍª”%®9)Qà £QÜäá¦1t•h¶ô!TRæ²ñÑHÂâÚQİ\rŸCåEÔ0—#wçG2ÂŞ/¾Ö/ç‚é=^ –/ÔºñÎÎÄÙËE’¬\0{+òü€t–+¨äqßĞ±ªæ–IÍt·|ú÷ÈÕvêğqª¹ÔˆÆŒ&Ï\r\\ëVß =–°EbÚënOÎrn›ê‘X({‡É¹uzK­¯`=:ø\núÄß÷\0ªêÇĞ[é%™:p”ˆq+¦ÔR’ldY”ë\"ÅÇ[VÏu{H-­ÁH×_ıâ¢8j‰ëV†Õ5€’à\"\0\"N?E;+°O~»wNÃ];Lœ'„‰íSOFˆÔêä»±Dæ-×!#sNÉ<Õêô Â¯Ñşmu³¤ÈóG¯8ûÎTn]¶¼Îá:úzIMnœ O°8ÀèÄz5…o\\57<ÅÍÅ²#8â¨ñé?sNîº•ÛLõ¸	}úxîÖ&4î†?ç[àz½–ôó³·¶Éı¡Œ<*W¸èİóÀe}{HZ‹§±,(<oÔoÀxW¨t¶2íĞİ# A*·¡»Ÿo\\ç¼R²}xH>NP¸|QÉš|x°'È-° ÛÅ2\0 İ?Æ¾2*\r|]tö•pá\"¢Ú²JuuXybŞD\nÊZ|„H7 _òW‘®şGuXyH>T\r¨G»äş˜Qlˆ¼ñ¨ÉƒÂçn!Îu'Ä*ºC5¸İ>Uª2!b	£9PwÂİ4åü›õá¢}yèWŞ|ñâa\$¾g†éêÁ óTÇUË¡&~9(\\*½!b_Ïùû€w±7\\£Çğ‹]=ß\\*ä­€@ğ#N7Íªè¯Á5QN`@<\0°6!‰9ÆÑl…¥\$ˆwI\$4 õ¾2–ë\$¥&‚Ğì.RZòà—³Y†›uyá¤³ìpå‡&SI®İ@¨EJiL€cõºV®1Fñ1…äZ\r\r¦‚àh“¡kÚ»öHHŠñË¿®ªªöˆKı§ ?xµâ-0\nÛêdÍN3Kó„CÓ59)Ä¾:B#¨ÌdN5A1”Æ‰šÆøÌOd[3Ú œáh–[s~)±9 DNâyøáñş>”âÀX±Ÿ'È½ÎÏHèòç,–î)Ú‚½\"Âeó0;\0Ëqeo>¦Û=®|«2¦G+B¨@z·ˆÏäøò@]}rQîÁÒ k/Š|íGñ:Ñ¯äW\0ça4>”ò^|õïƒìgİoûXEä9p…üÅLrg“A—Ä6¼˜p¿eúïÛÇ1ï´*Åëã½7ÚÀ[ö>]ı#ë?jB¨~Ö/¿}Å3ÿ:ûœU\$ğ?¼<•¿GüäaÿïÁ\n>0#!iƒ>.{A}'hQÿLwë~ŸW_¨îªTh#dÀÅÃ»–ªdŠŸFQ¸“µóâ*{æø\"‰\"¤P{õŸà}Ş4 N×ÕÓióŸ­Õ\r_ÅÊØÄe?l4À2¡?\nå—F™ú	åôqÎUï×Ä½°_İÿ`_üõÇàˆjı¬{_k_Ûo÷~ÿ¿c*#ÿ(´/ü!Dn¤Fÿ`ïü?@sôBÚ!®?;ÜEâ²úÿ“ş¾ÿ\0kş	ÿ*NıìD;¼õ°+d\nZZdB»À÷ ‹Š`B5æP\n8¬Öéàğ‡Ìc#ou½¤kßËŠM“İ¯w‡.ìªFÀJ¦ˆÈ!|®Äˆ2Fc‹Y).¬§ºôXHyò[ëê~ˆ†ù€#/™&¢£öã[À ÿñÂŒˆY@ı¨À(|\r\0,O¼ñ0YbÔÎ²Å¬ï\$0×ÓÛaË‘–À“É ˆA\$Çú0,Ë@ªÓ°>>9úÁ\\tiø<—\0ã—q\0Ä}@`ñ\0fVjƒ°­dß '(“‚†€	!_²nõ 0+c’´µiig8a]'=-¬B!(§Ø8†_İëÆx²j©Œµ”)\rH5HïƒYn	,f«rœí}-d\$òÖH ¬2né´†Ü›È=à-­d©“€FE-dáé¨a‚N_z4@”À[ènãŒ\$x!!i0Tª”ÊuÀ8ÌÉ¸…¼¯ş\0PZ8ZÁ¹†êcçàÁ®+ĞŠ‰AAF(äÁØÛ`mg*¸vS, Ç†ÜğKcAşÛ¬ &Ä¨9êÀ…ÁŠücİ0w•+ˆn€Î=›°)\$ë…ĞQğ~AŠÛaæ\0004\0uñ{Ä(´¤\$°­y	!°„B‹Û A<µa„‘Az ¨ÁZA4\$ZY9.aX\r•ˆdÚAˆLÂv|oOz|ßÂšZÜ(îeíZ£Ä†À");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("v0œF£©ÌĞ==˜ÎFS	ĞÊ_6MÆ³˜èèr:™E‡CI´Êo:C„”Xc‚\ræØ„J(:=ŸE†¦a28¡xğ¸?Ä'ƒi°SANN‘ùğxs…NBáÌVl0›ŒçS	œËUl(D|Ò„çÊP¦À>šE†ã©¶yHchäÂ-3Eb“å ¸b½ßpEÁpÿ9.Š˜Ì~\n?Kb±iw|È`Ç÷d.¼x8EN¦ã!”Í2™‡3©ˆá\r‡ÑYÌèy6GFmY8o7\n\r³0²<d4˜E'¸\n#™\ròˆñ¸è.…C!Ä^tè(õÍbqHïÔ.…›¢sÿƒ2™N‚qÙ¤Ì9î‹¦÷À#{‡cëŞåµÁì3nÓ¸2»Ár¼:<ƒ+Ì9ˆCÈ¨®‰Ã\n<ô\r`Èö/bè\\š È!HØ2SÚ™F#8ĞˆÇIˆ78ÃK‘«*Úº!ÃÀèé‘ˆæ+¨¾:+¯›ù&2|¢:ã¢9ÊÁÚ:­ĞN§¶ãpA/#œÀ ˆ0Dá\\±'Ç1ØÓ‹ïª2a@¶¬+Jâ¼.£c,”ø£‚°1Œ¡@^.BàÜÑŒá`OK=`B‹ÎPè6’ Î>(ƒeK%! ^!Ï¬‰BÈáHS…s8^9Í3¤O1àÑ.Xj+†â¸îM	#+ÖF£:ˆ7SÚ\$0¾V(ÙFQÃ\r!Iƒä*¡X¶/ÌŠ˜¸ë•67=ÛªX3İ†Ø‡³ˆĞ^±ígf#WÕùg‹ğ¢8ß‹íhÆ7µ¡E©k\rÖÅ¹GÒ)íÏt…We4öVØ½Š…ó&7\0RôÈN!0Ü1Wİãy¢CPÊã!íåi|Àgn´Û.\rã0Ì9¿Aî‡İ¸¶…Û¶ø^×8vÁl\"bì|…yHYÈ2ê9˜0Òß…š.ı:yê¬áÚ6:²Ø¿·nû\0Qµ7áøbkü<\0òéæ¹¸è-îBè{³Á;Öù¤òã W³Ê Ï&Á/nå¥wíî2A×µ„‡˜ö¥AÁ0yu)¦­¬kLÆ¹tkÛ\0ø;Éd…=%m.ö×Åc5¨fì’ï¸*×@4‡İ Ò…¼cÿÆ¸Ü†|\"ë§³òh¸\\Úf¸PƒNÁğqû—ÈÁsŸfÎ~PˆÊpHp\n~ˆ«>T_³ÒQOQÏ\$ĞVßŞSpn1¤Êšœ }=©‚LëüJeuc¤ˆ©ˆØaA|;†È“Nšó-ºôZÑ@R¦§Í³‘ Î	Áú.¬¤2†Ğêè…ª`REŠéí^iP1&œ¸Şˆ(Š²\$ĞCÍY­5á¸Øƒø·axh@ÑÃ=Æ²â +>`€ş×¢Ğœ¯\r!˜b´“ğr€ö2pø(=¡İœø!˜es¯X4GòHhc íM‘S.—Ğ|YjHƒğzBàSVÀ 0æjä\nf\rà‚åÍÁD‘o”ğ%ø˜\\1ÿ“ÒMI`(Ò:“! -ƒ3=0äÔÍ è¬Sø¼ÓgW…e5¥ğzœ(h©ÖdårœÓ«„KiÊ@Y.¥áŒÈ\$@šsÑ±EI&çÃDf…SR}±ÅrÚ½?x\"¢@ng¬÷À™PI\\U‚€<ô5X\"E0‰—t8†Yé=‚`=£”>“Qñ4B’k ¬¸+p`ş(8/N´qSKõr¯ƒëÿiîO*[JœùRJY±&uÄÊ¢7¡¤‚³úØ#Ô>‰ÂÓXÃ»ë?AP‘òCDÁD…ò\$‚Ù’ÁõY¬´<éÕãµX[½d«d„å:¥ìa\$‚‹ˆ†¸Î üŠWç¨/É‚è¶!+eYIw=9ŒÂÍiÙ;q\r\nÿØ1è³•xÚ0]Q©<÷zI9~Wåı9RDŠKI6ƒÛL…íŞCˆz\"0NWŒWzH4½ x›gû×ª¯x&ÚF¿aÓƒ†è\\éxƒà=Ó^Ô“´şKH‘x‡¨Ù“0èEÃÒ‚Éšã§Xµk,ñ¼R‰ ~	àñÌ›ó—Nyº›Szú¨”6\0D	æ¡ìğØ†hs|.õò=I‚x}/ÂuNçƒü'R•åìn'‚|so8r•å£tèæéÈa¨\0°5†PòÖ dwÌŠÇÆÌ•q³¹Š€5(XµHp|K¬2`µ]FU’~!ÊØ=å Ê|ò,upê‚\\“ ¾C¨oçT¶eâ•™C‚}*¨¥f¢#’shpáÏ5æ‹›³®mZ‹xàçfn~v)DH4çe††v“ÉVªõbyò¶TÊÇÌ¥,´ôœ<Íy,ÖÌ«2¹ôÍz^÷¥” Kƒ˜2¢xo	ƒ ·•Ÿ2Ñ I”ùa½hõ~ ­cê€ejõ6­×)ÿ]¦Ô¢5×ÍdG×ŠEÎtË'Ná=VĞİÉœ@Ğşƒàb^åÌÚöp:k‡Ë1StTÔ™FF´—`´¾`øò{{Ôï­é4÷•7ÄpcPòØ·öõVÀì9ÂÙ‰Lt‰	M¶µ­Ò{öC©l±±n47sÉPL¬˜!ñ9{l a°Á‰–œ½!pG%Ü)<Á·2*ä<Œ9rV‘Éø\\©³”]îWËtn\r<Ä—Ş0£vJ“æ ±Iãi ™1›½Ys{uHÕ°?ëÛ–‘ƒ®ÇÎU€oäAß’r`SˆÿCc€ï”ôv‘Ë³Jé‡c§µõÔû=Ïã-H/À®Øq'E° ï–w|ŠÂNß{\r};™ø>şxèrÛãÁu5ˆB¸*\0¹àìÈM³©„ïÚaîí\0à{HU·öçCâ¹WŒå»³ÉyB'Í<Ç6ó[“´s¾Ùíyÿî¾ë»ç@Ùï{ïQàŸ™ü>?/<µK@  „À¨B|aH\"„¾ R	á@>~@œBhEL\$ğ®[Š°Sa \"„Ğ‚0ìFe`b\0ˆüÀ‚@‚\n`Š=ÒínÚù.*Ì”îO”èÏ˜´œnï ò¯<jO¦lM”\"mRÊÎğ/±¦*î&Tè‚™ÄTû _E4èÌúÏ8Üğç|R0*ñoÖÊBo>S%\$“ª ÈN¸<î|ÎÅÎ¾—ğy¯7\n§Ì÷íŞ´,é¯¢óğú°¶ì¬íPtíĞ\"l&Tîo—íE05nùüão©ĞrøğväîéĞèùÆÖ£BpğòpËÏ\nÔçPÙİĞ.-,æÔq÷ÀÖø3\r/‹p°‘PßŠ b¾éÆÁ%mĞèÎP2?P‰°ñ@ó°÷0(ö/gpz÷0è`ÜÑgÏ…ğ×Ï‘‘\\å¬³qòñ>ø‘pú@\\©ªuëŒ@Â Ø\$Ne°Q‘¦úçÌè0(A(¦mc‚L'`Bh\r-†!Íb`ñÛk`Ê ¶Ùäş‡`NË0§	§Ğ¯nN×`ú»D\0Ú@~¦ÄÆÀ`KâÃÂ] ×\r¨|®©ÀÊ¾àA#ËöiÔYåxfã¢\r‰4 ,vÌ\0Ş‹QØÉ NÀñXoÏìí´© q©'ª tšr\$°änpì6%ê%lyMbØÊ•(âS)L')€¶Ş¯L²MIŒs {&ñ KHœ@d×l¶wf0Éíx§Ö6§ö~3¯X½h0\"ä»DÎ+òA¬\$‰Â`b‹\$àÇ%2VÅL¾Ì Q\"’¢%’¦ÖR©FVÀNy+F\n ¤	 †%fzŒƒ½+1Z¿ÄØMÉ¾ÀR%@Ú6\"ìbNˆ5.²ä\0æWàÄ¤d€¾4Å'l|9.#`äõeæ†€¶Ø£j6ëÎ¤Ãv ¶ÄÍvÚ¥¤\rh\rÈs7©Œ\"@¾\\DÅ°i8cq8Ä	Â\0Ö¶bL. ¶\rdTb@E è \\2`P( B'ã€¶€º0 ¶/àô|Â–3ú³şì&R.Ss+-¢áàcAi4K˜}é:“¬¾àºÁ\0O9,©B€ä@ÀCC€ÂA'B@N=Ï;“Š7S¿<3ÇDI„ÚMW7³ÒEDö\rÅ¨°v¹”@½DÈº‡9 ñl~\rïdö”ƒ5”z^’r!ñ}I¥”ŒíÅsBè¦\0eT—K!ÁK‚UH´ô/”ƒ¨2ƒi%<=ĞÆØ^ úÃgÙ8ƒr7sÒÆÇ%N³»E@vÃsl5\rpàÇ\$­@¤ Î£PÀÔ\râ\$=Ğ%4‡änX\\XdúÔzÙ¬~Oàæxë:‚”m\" &‰€g5Qn½(àµ•5&rs˜ N\r9ìÔÂ.I‹Y63g6µ]QsvÃb/O ò|È¨@Êy§^ur\"UvIõ{V-MVuOD h`’5…t€úÉ\0ÔÓTõ,	(ßê®qŒR™Gˆ.l6[S0@Ñ%ˆ´¶C}T7æ“85mYë‰ú)õ8ÛCú¹râ;ôØ¦)´M+ÿ4	À ÉÇ4õÌ|©Îª1ÔZJ`×‰5X,¬L\0›7T\rx­çH‘„dR*Ş‡¦ÛJĞ¦\r€Øõ†52˜–Àğ—-Cm1S„R‹éªT`N¢e@'Æ¦*ª*`ø>£€˜\0|¢ğI!®E,¨ag”.€ËcupÆÃ9—`B¸ªaa¶¨Şpê`¤mî6ÒàR~†\0öàÖg-cmO´ñ1ˆ\reIN”QNíqo\rş‡nqˆÜ¶ôR6ùn´Snít¤wÆÃ¦\r ]a¶¤š-Ïa*¬¯—\\5Wpv^ OV`AFÀèœŒ3#82põH'J'nM('M=jÂ9k´ZbBn<î@‚<ç \0¾fe¤:\0ìK(ú™N´õ¼vğõïí-!é—1¶ŞH(›QgôÂÂµÉ—y‘<€’ íd¢\\¥c\\òs,uÖËƒq0­~¢i~ëÎÌe°Ñ¶¢Ò*Ñ~—öÈ ù~ ÆMØmÙÒÓ}WØ˜\rîÄ æ@Ô\"i¤\$Bãòácägä5b?Ê6!wÖÓ+xl1…`¾†`ŞÁ	s„Ø ê÷‹î÷‰¨Ë.ÇvCnhEd QƒÓid\"6µ…´`¨\"&fì˜xµ(\"â2ê˜Qzˆç\$Ä[0%±0lw u×Ú>wë%Ø±‰µ%»w²ZÌ\"-ÿ‹uí%—ì÷ó¤Yˆg±ş>x\\Ë-ï…„×¤¼àà-v—\\˜ıx^'M	‘PùÌİY‘Pëìİù)‘8û%ƒC§ˆ˜@ØDF ÌÒ\r@Ä\0¼\\à¾0Nâñ.„S\$¹ˆYIˆÕCŠI Øi÷>xPÍ¸Í’¹:Í·ò=–ˆT,â'LìùÙÍqĞQ2ÍŒ¼\r¬ñšÌÒÏd¼­‘Î”ĞÙÑ@ÂÑ’ĞŞÒ9F‘¸‹`ùO„˜f¸O•w¾\\hØ=¸}S™jGGW„‡˜AˆíL‡£RJ\$JP+Ó7˜§ŒL¯v,Ó™(Ìµ˜ÇZPìg¸ŞÔÚ&z+ Şájƒ—àË˜7 Í·¦-vAÃw•Ïh Ú^9óTöODù——Z—ºC˜¡˜mŠùŒ`OŒÀR¢yÓ’Ïì!ëGvzs¥˜G•\$IhYñ–•ç–Ùı—58¼¹xFøõ§ø«¨Y9¨š©i…İ8´šUƒöC”Ú[‚Ñe«‘«Zq¥uA«Â1§šÂ?ù…’ÙˆÌ9!°Œ½™:Ú“˜ì¼Ïøb0ƒ˜{\rúQh`Md7á{2ƒ Û²8ÖH`%Æ¸ºã{-ÁlÊCXk³H¡šÓ’Ù|\0Î}àX`ShÕ­XÊÖŒ»\r·™æOûy“¸X¸¤¸ :w7±·ÚÄ×nÆé²ŒÒ#û/¢:4š(MºŒ;­¢øc»DŸ£z;¼Z3¼»¹£›½¢Ò]‡¶ç ›Ø?˜.ªÅ¹€˜\roØbOì¨^`Ïº¶|ªë†×÷ŒÛÉ/ÙX×’]¼|Œ›Šü^œ!%XÙ½³8ÕÃÜ\$Ì;Äáz¹TåªxK·¹-~² 8X)<!Öèyï–x«9ø¯ú·ª:ûÄ Ù‰F‰†‰ú—’®xˆz+Uàºƒÿ¶¼šúA¬E˜; ª'Å%c­ùÛÅYßªœ³ªüw¯<{¦õ9ŸúøV:ıŠ`ÍÊÊ‡<ØáÁüG‹Ø¡ÇYõ¥\0å„Zü÷U Zq\nmx¿)_¿}YÇé_©z›­ ù­y\rÒYšÑ,Ûš3šLàÌÙªÑY²ÎÙ¸Ï»>“MÒí	œMœ™Í	ú)œ¸P\0u8 S!Z{Y¼äÔÜ9Î¸ÙÎúfV3oõOÏ¼şE½Ğ`CĞ­ñĞà¿¿XU¿Õ}Òlw©™0´}©­ÒÌÍ™İ7›Y3Ó¬Ó”Á›4ËİG›İJĞ¾&¹Ã¤ÙÆÍ­(ÙÎÊ-AÖ€V=f|ÒØØ@E/ß%\0r}şŞ®nnÇ\0äÇLy¡Œ„‚¶<+Óàö_¨|Êë#‘AÅö\"C[yÖÚEW³ŸérW²€f(\0æ¾äĞ›“>À)Â ŞÀÌ_ÈUëÂ,UØ\\ù#ı‹eˆ½*r›`ÜN‘YŒ Û*£=aş\\›Ö&œ^g4ŞmÃ¼íç¼ıØe#èî^°|Ş‚¡QXNÜçæüIç>©ç¤\0rÆ‰şí4®š^YèV#æ)éşkì>¥×¾ËêŞÎ™ŞÔšFÀW^…è’%¾İ’\$+˜ÕPÕkY*u¢~ÖÖ,¶ÅMÕ×WÍ‚hhGÀ¿K´\\C¬é¿7HmZæŠÖÀSZ_UÖ%æ\r­õb)õ´gg	qñûíö™ö@@ÕÍëóİÎ…tä\rJ°ÇàÛ”Ó×7sƒÿ¤¹¯”U¬K_1å÷t¾j&SåBi\0ØÂØ &\r¬ Ò`ø:µjÒFÀ~=TÌª¢¾gÏä¾‘íö!û›æ^hÃ^í×•ğ÷—½ë/[{ùB¡†™É(æ/š|ÍÖÈg•„ñj/Èd\\Ş–SÉ—ï­9¡ÁŒG`‰Œu­Ì1ÕMÙÊ?Éı§3}òQ\$qIòm~°”ˆG=‰êoVz¬\0_põ§´!tá„r{ÛÒ^Z&§ü	îüuÓX¸ö1@ÀĞG{äøõĞ¬¾	NIÒäÓÂô¨\$=0ÀBu82ÆS\"ñ6¸®Qpjëov\r<®ÕÉ¶U¥\0.ù¯Õ¨…EÁMÂ–\n8VÒoQ\\…`?ƒà¼L6¬ªÌ=\rŸl±¥¨¶”±ìÀà\"øàëõB2puì&\0åëÂ5¤\rj¥ª0VËA¼µ™…;v\0eH;”Ê‡TJ¢Å6pH?/\\àHµ@!pp¸C¦Ê+5„\\+a™8;’\r(*’³TÇÆ¢;ÉOŠ|¸”^Ld‘&/¨ñNI¥TÈô|#Èï–Gá©`j%Ç—ŠäDÔÙÛàZƒÄ¡4Éni€i­ 4·ó]@tÆÆ#5cõÄ¾÷ğ	ÕZˆ¢RñyR`@à¤\$I{z‹ÿ“’èƒ‡ïé4|’ ¼¦×‰ªÜ€@=hCEöÕH¶, ,ZîÙêƒi‘µµKˆºÃ P¦|,g°z*’ÊÆñáE)AjknK\nºÀC\"J79À}4›f¢€”ƒ*´4ë65‚¶Ãê­×«”Q\\¡†Şc“˜MáÑ\r‚{*Û1j¯„­è­lF‹Šmğ4¬ÅM¨* `âX¹GÀDÀA-qqab‹´±1ª9RÉH’¾Åb¡g8Š+¬l/³œ¦äô¹Å„æ ( Ê€L\" 8Èíè0(Dc‘¿#ihcŸŒ‘`œÇ8‡±¹A1‡\\ùuK(Ğ4!¶‘“ˆŠšd—Ò3¢8ŒûÎ¾ÑˆÑÆ®4¢j;#¯ˆÃ˜€ñ¯Às8ÀÆú5,ucnc€FöNˆ„pPa8ÇG8êrK‘–ÄÒÑÆÇñÏÚk›iÈË•4ˆA€	£8TÒ¨Æ26 ;*iãX‹‘—Â2%MàBJG² &íC*1T\n4	-#è.×%ÆÚ¯'z„#Ê8ó˜A+€@S.0Ó×€üõáII`UºQ°ÎUŒdd\$)¤È*]¼ÍíTééãÆCèè9M*…ğ	\$b+ù·Ñ½Î‘Äydtƒ\0-ÂùL‘ü8\$Âe\$ƒ¯Ú<AÉ!ëd\$p@]’d£¸ß&ÉM+2E’´yßˆ((_|ÅMdÀvU9!ÂeD	Ñ(úªW=„òÆ#øàÀ_é'´bN;¡'£¡\0²O¡<L†iAÉØ Ğ ”TŸ€¸£¡\0¾QÉJ# }Ba(ğ/ÊuƒGB”¼Ÿ%-)€Êhòu„´¥€~\0™IæU°•Pr…+1©’š’ª¤¤%51à„É’L`ÜE'(/€ÂQ‘Ã”¬£%TÉ)9ËOrŠ–TŸã],Ù?Ë<ÓaÕ	„¯Â‚œ€/|À\$Oğ@Z ĞI®XNœ|±%“,¹SK:]ha’”%¥ª)kÊşP\0,·¥»'à0J©:Òÿ	ä—Ã&ô¾šİV£0œÂÒújÙ‡JM¡*”x£ƒôP)™¬jKÈR û¦\\\ru\rÛ(ÃWÔÙáF: k‘ºğ„\0œÆNJ€˜P!Q2 'H *\0‚g T|ÎÀªŸ~g`D,ÍÏ¾\0#¨	³;(\0 À ÌõL‚šôÕf¯5'ÑÖ`'™´Î&t(‰™LögóA™¤Ï\0à'Ÿ’ksi™ñø&‚àÂødómøºP\"Ng`O›&ÄË˜  X@²	£Í%shôäg_Üsb™¨fÏ5ÉËM>s3›@Tİç77À+ŸònS”šdÓ§5'6s\0\\œÔç\0O:“ğNLS@ Pæ{;9ÚÍ¶p“FœÏ@78_Šl³9°\n¦–)Rg³9@aç—:iÛ\0şvS›Dòg®ú\0¸SàÁù\0øsõM\0BÈ\0+O“qš`§×>ÙÄ4	 T9Ûç7=°MâvÓ=qø'y;à'LîfàFšïf´) Ï–wP·TÓfÍ>\0¡OŠ|ïŸˆ€?0)OÖ~à|ŸÌşçæ`#N–´ \0¦ù>€'Ïª}“Õ ¬Íç™>ñ€¢~”Ÿe	\0“? *P…3¡\\ÿæ¥@ÓÍŒô5\r'¿C‰ğP–ˆ O¡E\n†MBÊ#Ğº‰T;Ÿµç»=jPŞƒ49¢¥çùEz#NÆ‰Ù¢”ÎÀFYÒÍÌ\\Ÿ½\0CA QJˆTVŸôí¦˜©­Œé7 \n˜˜vÓ0@–‚_®Q¸LÙRRc!Š†VÀ|”zÒÍ6¿šKKÑîõeS€£Š‘„4¥É\$„aI€ª|P„•A+‡¸.qKD-çS ®EvbCOŠ>Š¡H¬ªÙ<áí\r#äãLPÜ˜€sâ¥ºPÖ­2ƒ0˜ =¥*ÀWL‚“2dàt¤ \0Ø!ù<	àb°q€\\pa@‘Rd o™fKM İÓp ¡±§\0}ÈöñêzŠ\0Ñâéá2€Õ¦š3\"™ ˜)@\\*gÓrM#!Å8éÊöædP4ó%>KÒmAçÔ\$C§jtqP°9ÔÆ¸¨Y¬jP:vTu™ ä†€®T²¤`=¨pã©Îcj‰*¥x˜áêd³m\0ø£…MJjFm¥p¦´æ§A¼F‘QRÜíëÙ6õFÇQ¡lDjº¨¥E¦MSÈ–—4\"\"mÌ@J¥Q¢H˜¤Á@è(‰íòh`Oäåf‘8 >P8	Ó{;57ª,)©äŒ†mSvàg‹Aá“‹œ‘…ò|ÅÎëP€°dÅ÷ÕOéx2ĞÎ.€S¥,á¦¯8™§Î6qõ™,ûNz:ÈÖLÁ–Ø\n(%>éøOÈ N%'æà>\0ö¯•¬ÔUŞ9–§aÃ ¤õÀP€IèOH	Ó9\ne¨5@\0£Íâ²ALS!mªŒºÓqv„¦¥(éN7=ª=Y¸AN¥š´¤®ñR«Â)ÏŠ–´¯4ÎØX©JgSmZu\"N:À*4³*íŠ,¦:¯	ÀÀ	Ñ€¦Àğ¾—ÜÖĞL±‡è‡5Q2¼ÒÈõVXR¤5×©%…ëa@ÕvÃJò°ò‹ˆµaªşXvœ(õuŠjÂëT‚Å6\$±Xèƒ™V°Ø§&Á•Õ½ŠH8z~åy‚^¨k`¢ª?lÄ wuuzÊ@±lS~Ã@.\"¸E¼S‰*í¬µ•©ebM5»–Z{l{aË/ÊÌX³–ÄU€1ªÖ¡ú­ñ…¦aÈXÜUl1¤Ê¢±\\—ĞÖ6s§ˆ‚««:ªÂ§ÁÈY}€ëŞ¨Êï¢äe<,€‘s9.!ÀSV \nìbÚô\nhK#ª°l%g[;Ì¤ÆñX‹Â>Ë™gQ¤\0¾áÓ³¨lêãvÚD¡¾ÖAu€àïX=BÜ*Ğd³óÂÅsëaÚŠÔ•EZ¸Fl~ıbµ{\$_²rÌ\0Mkw‹/„~Õy”‘|C»j©…ª^™5¡D³2%İ[D×•uªxo{DÚ¶İ¶g©å1\0Æ¬‰•>Üôì/º†èëZîÒ™a\ná¸!ëE¼ šAÙd*ÃeŸ@ò}U0 7}h\\ö+†æ1¢®U5½á\0˜õ9RÑáVçanh­µüm b	€¡  =óÎ­b4ä—ŞIO_¸¼[@Jö›’…uâ`}¤ê¡N@ÙÜ³()ƒÀíxS\0 ÓzÀ•ğ¬\r\\jõW 'M°µw>ø[…Ê.KNğxøv\0« \$)–È z}(òZ]b€§áº¼á+XzÖ¾†G­…h?EÊQëÀbºôæ®ÅvK³WQR§KqE~‚I˜T£5ú)…ªnš«\nT-yDÑK{‚¡`P/‚ìV¡:¥ŒI]šnÕiä«×©3ïX‚úğ^~¾¡½ÎïL‘\"(S…¾Ö2°å‚kÇ×\r?µc˜äªlñÄU,;õM¾\n7ê¦–RŞfÂŠº½å½RãyÇÔ”zV.Êko› >Òb´ˆsÌÄ(!Û‹^È=­œÈF\0.×¡MJI.¨HiÙ‹8A3”˜ôßÄ `(\$Ú“\0UÑ€¦?ğ(ù\$Æ~ğD/€pà¥a¾ÏŞ€‘Tp\0ïC¡Z2¾.,.ü}÷ó íÑ°u‘âD4¸X•	™pï3”x+Àæi„ñ»\rÀ€÷Exö	ÆÖláÿ„Ñ˜2)0Ûpr\$¶ÑŒ¨ƒ>% °z3ì‡!Pá(1P¤p‚»L\"\r s\$ïö	¨7%ÉŒúáÁ6“…12œıˆ˜BÁˆl0µ„|.Z(?ìDrƒZ@<ˆ´‡m¸†{š„fC¼,a¸n#>ÃÜÇ2ñ²ğA8` N§ƒ\0‡Uµ·f1„<A+8‹ZqÓja?}ÃFpü:Ö\"8É‡9Ã`•0İ„a·–\0nB7=n\"o‡Ó.‰üài±£J0–œGöÉøŒæbÌ\\l:b§kdXPáğËŠ<)ß\r\"‘ÆK;1Udb‘â·8°¾L©‹V¿ß ¢¯ê7†0H–Qó*c*Ë™WYƒŒXşá¸³-Ñ±à7|Rœÿ8K¨Ü“WtL2<{áúÇÎ5Ùì>áÓÀeÃÆ€{‡ÊŸ3°£\"JÄ*¨R¬BcĞ»VÌÅq<ë+qÌ…'##>Ñ2F0–Û\"X´fE¢{‹|cât˜ÕM5ÅŒÈ·>D\\Xä–Mg“câã	gƒˆÇU'ñ‘ì5ä„\r9%ÇQ•W¢‰¢Ÿ'ÀdÊNOW‘¼£”›#é³Æà~€u”µe´‰´Wß§¾;ğ\\S®ó&¸«ÆÎ,Õ×}øè_|°øíÀ İğ!‚vxä ¡˜]mÊ1í„|¼å¹Dx.ÂBoğ,Y¡	–tÃ¸ Àä]°Y—lÂ/ˆ;ùYA[ïË„uô`7ãÿq³™ğ?ñF´\ràv-Ô@Ù˜À¯\n3âÛhj#°¯K 6N^H—‘é\$†è(|Àè\$¢…‡ıeï'ˆHØÚ%™p•ÄŸ%	l\nÚK ÁŠ¼–àcgŠ£‰ÂBßü—¦\0%ø{E—z³‡¯Yjic&¡5„nG‘g‹å/¥‰Z¨£î} 7G¨\\K¹K²ò-¥Qfé¾×æplÊ¼şş×ã˜¾Àw 8§a¤L¾*—†}š]Ò@¥R¤}JQŸqgçç„‘^a<Ê\\ ág€ïC‚[Ğş‡PRí*4_YØVvª\"eqéœ0?YwÀV\n²İ›2ÉZ¨®óÍ&m™2€ã@hnŒò\"&@Ø³ßH‚ğ•4‘OåUé,;âØ¦“Ë˜#L[Áä¿(TÈú®‚€].›Şvô{|³\$\06{+\0\nÀ/\0ÄtI¶½T–Å¶ó1dé€I¦ô	˜ô¹¦@ãÆ*96›t¥Szi‡ÏMr¹ìˆ§(„\r¶Ó±X°\":ÍAàŞ„Oş™õ\0(	MÀwÓ†¤4èJéãKùVÓîŸó9¨2jfšú~ÙÖ@ôL\rCjyz¡\nö£´ãë^êKRš´Ô°ãµ¼ĞêWVÚ¸¥7¢ÉÍWĞ‹ä„Fğ´5•­cÅGP²=n‚“´ÿû;Å&å8ş+-C®\rûz‡qf••T²“Ó@SĞC¡Í…á5kyk/ĞbEÅ 8}té¤fĞ„oñH{å…§Áÿ¿½I%ŸÁ•£a»˜¾—oÏd\$´ìqzJÕÖÀ€N!oP6âdF§Hˆ½\rqå³ìiv•Œ© éc‡‘E™PÍE•€	®¦Î4Øp)ş}Á+fö¸³wIÍ“Uò¡	fƒõç{ü òùY0âu=¥MwĞ`U*ş™¶Nci´4+Û. Õ­¯m+lL!Ø\0ro}¤é ;a!ŞØÊ'²Æl¹]bYÚàvî%³´»€\rà’ŒZÁ}mô\rø'p²Ë·NÅiòÂOÜIW÷Uay‘„\rësÜ¨v•å¸ğƒn+æÅ+mÄîÍÍdL°©p\nUÃ\nC\rîwqc>ÍQ7c¹\rËøÛ™Úîí÷BUıÏš›v[£7¸Å´`•Q\$gY¶T9·PUıÕ@ãv[ÁÜ¦îµ¸ÇàÅLîPNÎ¾ñn7p&\r/w«=~´í˜ƒrp<@e-ËKõ¶/e§Và=Àõ*òò_™aæ;Úˆ5”e5á™y%¿…JB¿íb-08 .Ö¦V	ª‘ Dg“\$UÍ5Ş4‚Am?@šö\$ˆô\"Üşè'{§ŒÚhÒ85°âÅc¸”Á×¶BîP\0¥®­&L+C÷\0P0'Í0€P?&¹ÃÏ²ãœ#Í’\nåBÄ\rüX'Ès–`!rNhx1dŞBÉdÅÉ¿ˆ;ÛíŞØrJa+ÎCÄFôƒØ.½6)ÁÀ…é‰},&À†±‡Qª/L?¼	ÖÆ¬Wšk½p›`ÛQ,UF³5£=hæ{ZOÀ²eÃW„ŠfÃ¹ ÀşWeÊı\0ØÑ¯oPi<Q™Ì&\$0³7{èş›‚ŞJïöebwª4µ,ı¶[j?Yò¤’\\« ]®#Kœ.·ÚùZˆ·¯Ÿ=\0ZÛ%¡&Q2µ]rû”BÓãˆ·Ğmõ|<¥¡>ªƒ/k¸Ú/Jí½æVk¾lî«\"¡ŞìÊ÷í¬A¸aÜÍo²ú¸Û«ás¼uœâÉo99Êc‘óëG›º\\æÎ‰Ämõpë_RúÙVş¶ôEÀ äpŸŒûRßúÓ@\\^bšmÒ¸&’¾„g÷”Úæ_ÀéÏ.A(4Šä0w´µ¬N~îK™|’xöÁÛÔr(r|ŒÈ'#°GÓÿ‘D½•¶k³ÈWæB˜\\›Á©[ù=ŠıScRcğéÙ[ò78iå1º/¼m¡^…Ìı\"˜w}¡èz¼y\nğ‰p`™6(šmí§ş\0u•#“Neñc³¶TŠä\0xàGZ: 87W­£™£ÌÀV!ÂõË®‡ëhBóEšBÅeÛ’/¹&ú,GÚğ—Bfãàtú£ªµì“¡¼è€·z&£b2Ö}a×B¸ùÁŞÿÜ€Ô¿ ¹˜|\nt“\0t(ºkÌPpüŠãWi	Ï½;ó4×ãv¦Nt@¾qÆÊ£I\\ÕŸ?;…Ç=WtÏV‹ÛH)\0'BËq[1¶vˆ÷Ä3ì²ğ¯±zênŒó#ÔŞNâc}IÖS99ƒ|#¼Ë3í\"43¦™p6–8F€'íğ9X™}Ç^‚ov¡°˜eÓ6œÃÖøCë—Ô6À’B[Á9ìB­â1†ÅµqN§…y&ÏrY\\\"ó\"¿v;µ»ÜpÒ±=¨&Æa\nT¨ß\na‹»ŞïC\\\0002óDåHÑ0´<R½øø¨ä÷\"ÿŒÀèRâc»¼=.Ğ7ËUäâ|FS>ióZ1LÆ¾¥£ƒ_O ğ!!ÙD4¥.fëeŠÅÂ¶ñ÷à±·,ü„Z’”ù@Ä\0<F.—ŒàÄğc>_}¥£‘˜€£Ê p€C<•I·=çŸZ05ÑŒ’°)ŞóÈÆ¿çÁNz,uş€ŒˆØ\\p6ŞŸy-å—=5Æ!T#Gø\0*m¿óõ¢’InÊ©ŞøâĞÿ—»:*úÚıi½`7›hŞş¿İşç½ˆÑøEB.ÖœXÊ‰¢¯`U„q§¬s¯’ïsl[S+{ ³	›¦0úç×{Š7ï†<pÖn»Í¹Öö.ìñ×İ]xŸmúĞb³}¸«<û[İ»£İW‹ıôVh»ÛkY÷¾ğ>½eØ5—\raaûFäğSxÌ¶ØOcÃ¾‘¡á[LşeÔğ—‚¤ 7„„ˆ§>@éàïà+|Šëï©;î¾ı£¿¥.ğ÷@Y'»¿ÿã¡‡È>Eò@0‘Ÿ@@ôíH=C¯¨`Ö€®b®ˆ-Ozóm¶ø²t>=Á=¸—3n!à×ö“ö8DM&‡JÛwÔö„.]\0×O?Ÿ[û4D#~<öOÀú­„‰\"ë	oò‘t¹ÍBcLK1éœ	‚YÄ‰IƒÑxıù“8Qè×e)¯…Ó`ÿÅŞÇLú6+•Ø¬Béùãwbo½L]+VPü¹•ÿBœEÓå¹Šß_VÂÒ±xèr»|[…æ±û\"_~âÂÛâHG…sp‡óCœ\n\$—ÈR`ù€Ç®` í\"é@F?è	Q¿Äq›û²^¤`ÀW\"è;ı\"IPD¥şÇÇ†€2•ÿûş°ÁoCƒnİÂÌ}sÿûxš2“›i wc%ı¿êş£ñ\rù€Nù\0I¬à±äş©C 8€pÓÏáºÒYP!À)¶F5’ š˜	 ¯c¾.ó÷oö7dZôÏ`<*7£ı€äÑÚÖÃ4w(éOˆr“â0n¨Êğ¯ôĞà9«'ÓıtƒX6gŒ6æ\"®V:ÈÍª€ö¼Vä³`\rZ„Z¦ğ“A›&!Û_O\r’O€D®	ˆöá¤ ,œĞ34/´ìo³õm)„‚Í#}¯şğØyÄ¤Òˆ[nª4\0á´Ï<D¿.´‡œç Bîü>\"\rŠC¦°²ğ;À³@ş£³®‰æí\0´ŒD0¾Zÿb\r“€V%\nRRA}‚”Ôƒ\"Á.“ªP >n1Ø4É=&:>‰\$€^RMpQ†ö§\$ÉL\0ö1ñèğ¥€”Òÿ}‡Õ«m‰HÁB€‡	¥²	™£Ah1ÚVÉ=&•LÁ	A””ºT¬!í\\)^Á2Ã¨.°W¬p.ïC€QØ”£‹\0Ü\n`(à.	9Û@†¦`(ì|\0Ê¦ˆ>¯ô˜×#á@2D@Ö’´\$éE\0f¸r…v\nˆ*`£½.’Wás€\\à|À>€H *©ƒAr¥ñQÇjš›úP‰;ÁD<|¡IÁN•È0UAX=)ÒkŒ˜ºX0‰Áv1ØİŒA®U,#…DAc@(P‘AZt¡Ûp[AÏá/°•Œ	0³0_B5Ik˜Á4ĞdÁ©bQğg€T1Á%{Ñ©©=\$”PI=KHğ°†Â¼\"0\\A;	aÒ¢ÑÂ]ü&7A‡\n:YƒÇÂ”•\$)€•Â\n€.°kÂ§	ÄPs\$ù\nÈRp­¤ü¥„+Ğˆ/‹'¨OÂÜ#\$,ğ¡¥°*dĞ€B`Ä  B	âÿ°ÃAHTD1)I¥QtLN©C©=\0¶—Œe“ÿ\n{,±•„<4B…ÔIi%“ô\"PÕ\$BWğh%S¤PËì³Ü30MAE\n(Äp¶AÑ|cÂ›¤)à¹Âë\n”#Ç®'\r8ğX'š[ÚÃ¬à5C‚”¤0àáB<•BS0iBf¥Læx	ä1f	€û@àĞ”C	¬;†æC¾U,8b\0Ş–t)\"2AŸŒiRC›\n‰/°»˜¾³Ü:¤Ã¬ä;	_€X(”Â\rCç\rì;…¹C_(µB,-°dCı\n\\9P¹C™ .Ğè=B–ğ”À-€tê]P‰oä\0ù N	ààÖ³HÚhö‹^€ŞĞF@¬/Ê<Ğá\nDjØ`€ˆˆã‚Œl—¹€ş|/ ! ñ*¿ªœGà3€^ \n@€°'¸	C€Ÿ€„š( ™HàÑ'&‡¼\"¤äê\rLF‘ş¿2ìÑDx¦óè!jDµI@<¦vQ‚À0€§ Ÿ…CªÔXH\0002€^;i©~œ†¾TH©6…5  C–“H5Â“(€î49ÄI Å,SF€_TSÑFÅB5\0Cq!¬`ş¨‘¯Å\0OÄPÑDEWìQ‘PEEˆ?ÑY•™<S1]EYìQÑ_ÅHºÈKX8|X‘\\Å7ÜQqOÅxö€†…©,GÅJš\\I2¥Ç0SQ#€›ša\nÅ,<\\Ñ µ=\\]¢+—%„I\n…ÅÇ\\ïÍ\nÃ\\FiTÄª+ñ,DıÜK±/€™Ñ1ª\$'äZ1T‚§¤U±PE |R@/Å({Q`0¬…\\W‡E9¬UÑ‰Æ+¼c <Æ9DXÑ^E­|R!lF,§ qÑ\"¾H,dñbÅ§<eq‘F]¸ÉFk”UQœFClRq˜(	Àº©D”gñF!De‘ŠFw0Ü±]­le1F%\$eê‡Å¸m¢M£]ÄŸ€ñğÛ““èâÛ€Şxî¥x\0ŒŠ •)ØXm`ˆ[ÄK`®Å™Ü]mP”:iˆSM=@9ÄpSFÄ˜xÕB.«t£¹\0p\0ÙUĞŞ\rNA;–ªÜÒhĞ‚x~ë wÆÌ‘Ò…x™8±ÔÓœs`Š!_ôkÂ\0PÂ1@Æ\$4áw­°Ğø1€¤nÂª° *\0†#Ràƒ0?	€'\0d¡ 	À’(HxàG™ \$ƒù\0 Hş`(¦€Š±î¦x…GD†tHë!’\nÉ ¬Ç€FJJ|á6à>?õXBQFØã©Qş\$`¬\0>06ĞT‰\rˆÂZ\\ã:	(+ ÂFJàQµƒƒdf`>\0ŠÓd1³h«ÌP²ÅpKò\0¾T„@0µBà˜Hà>5 äi 6Æ–Ÿœj¶B8•èZ ò?™¶Š¹†4 7ê¼€¦»°D*Ü<lğÈ’H1 ±¹’¤7Ë5Ò%¨?leQFÈÈ\n`<°bÄŠmr°òßdˆäÊ\0“\"ÉÈàÖÈ¼9‰Ò1+òVÀïGè\0É’1Å?#lŒäÌHÒÙéAl\0øÈÒ¼Ìˆ'ğ°ª3i †ÈüBDàÂë’Ù¦o¯²c’äZXáÂMJ\\€#2¨mbJƒ\nĞk@ÿGèikQf)(&\\~Ák©¼ØÙãÎ“û¬!*E(‚BÉ&ÈT²-É`±Rİƒ\nªÈ\"i‘H\nÉÔÃ\n=˜€W€Ä<—Ñü¾@ *Jzğ7,¶z˜¨! &Œ™\$C.ÿH·\rñ¼¼Fâ¸`+?´¼Œƒ¬€ÎtšÀÖŸÎØÁ’u³Ä]-ÈÁ”~’b%xÒd)Ù\"|^BW¿ÆìâL3S(\nbÀó#è”A¡ÉÒóûR\"ƒÕ&H”CTØ™i¢W¢f¨›IX!qü¿ˆ˜D ı	^HğT²	9(ÌšäÊ\0˜àÒ‡É÷ËúÀ4)) œ\$Š—~0±6#\$’*Ótà#;<DŠ·Ú¤£«Rx¦}*#õ­3ÊúgÅC“Øc4’š³À´©`‡@&¼ …€Ê¸[Çó#ªda?ÇèYÈnü;ôà·x„:Lˆ²´ÅF-ĞC¢_Ê£¬rkÉ 	ò¨º\$âÈ2³²#\\K\nH‹+ëæ¿µ\rÈT’Â;ó+üTR¾2§ì¯ÀÖ\0¬ør¦¦Ë*D<rá­p¬§!@IœËZÈÒ™³¸(ü¦oí¼ÿChOó¼ë„š`ˆ†ëèÑ2Õ\0èşà,]Çò\rxÅ@:@€œò\"¼¡(H+R„ƒë-Ü¡Àí…±+b Ib(ø›aHHÆ# I@)€Ò™\0‡ÒXŒ™.@ÉŒ†™¼È\\²i‘™¼Ìµ€3-`ÌÇ¯‡™ËÊÂ<¶&ËÊÕ ¤Ò\rÉ#ğ`Ò*ÒH\0¶L ²éK©.	i\nv\0Ù/Xe¥‚0¼“\0ªı/TÁ@6KÛ)“ø’\0ÄÔÌ!¼ÉcÔí¬²üğ\n5ÉğEæv\0ø™\0L²Ò¸')šVÒ;:&X²¥@Ú,­\0ıJo,à¤³µä1Lºb’Ê¼Æs‚(ÔÇ“K:É´Çb#Iâ-Ø‰R¹JÜ\"@·“JW¸sË:äÊ)LtœÊ6Ì¬ŒË*±¬Å\\ï€è»Ò;‚!FÀ9	0‹VòG;Í,ÈS»ÿÄËo]L±3›4“:Ê53ÁGLù1h`ÏÁÀh'œ‘Ã¿K‰02Cjo\$N|œHY—1”Î#\$†ï3ÔËIÍ\"õÔšóKJq1ÔºóJÊ0èBWƒ‘1\$0•îÀä¼®ŸÊ\\Õ\$&’Tà”Õ€äL_5\"p4ß5HàÂH˜0ÆÒŒMRÍ¢“[ƒ‘6\0i7Í~	\0iBÍ‡6@#ò¨Í§œ×Àä@`‚¬M*´’MˆK¤LF	%A’Yh\r€!j§”sp,)-(`À,MĞò}­#>¼Â¤Qÿ\r®ÔŒŒWTÒ\n(¼M»‚Aµ€¨i¡l><ÔŞ‹s—{(H\"“MÔè¤Éö¾Âúği’SN¹ü ¯MÔ\r¸+Éöƒâúª‚6zZ@? 7MÕ9\0B‰öµÚÒKí­Š´˜ÀJ‹üIjl¥’qÇ*Œ’RM#T‡'ÎnÙì‡’k)ç9ÜÌ!µ€«\"ü©D¤½Üãƒƒ‘/ÓJO¦/œ+3\rË—.P[ÁH¦ë\n³\r%#X8ÒmÈŞ„¸1ĞZX6¨6z¼xn\0ÂœøHT°|…J4ms³ŠÁ)‚ èK<ªÌœóÉôÚl™CµÈ‚›ÍP¿á.ğ\0006@\$ôº©\rKHÀ>ó²K§ì“ÚÀ‹;p5ªD\0©;t³¸-#\$òÓÍI¦0èä®fÏ8˜¼ô3¶;ú”€3Ê¦/=@á­?g-kAò\"—i4H82aÃÈ%R2+„Fû…“ĞpaòÏ@Ë´pmöIã>\$¹“*?¤™­¿Ó(\0µÇõ|¡\r¦Ï©5 1ş(\r¬û2äË¶`°OqmMJá\\‚–	é>|À³óO¥8À ,:Ÿ8¨ ,ÏßücÚØr_±ü€àY€à€c4Dä&‚\0”¶Êd¼‘B„e0\$ÃTÌşì~S¾&Z\nœ¦RÌ\n	• ¶Kğır÷:ñ<Œ½ÓÏò%|Á¨€ñ9ÀêoPM95 6PQ=¤äÁÿŸÀ:È5³üšË1@9Ğm°\r”Eô`,ö³\rTÄmÌF	P2Q‚¦+}3‰^fè )ŠLnd¦`†\0ä+Ğ¬ÓØ‘\$D·R\0È¨Ñ'ÉJ\rd~Áj\0îùÆÒ›†Õ:\\¤Ì\nJá[€?: ĞåLŞìü:û/+„>(!%1ıˆf“g¦/ñ;;3ƒ‰Îv%|Út<M§CÜÁ³DLCÄÁ¡¿”íJo:Õä³ÓÑ Õ\n@9P¨+È®`ÈÕ»¯®ä™/P5t1J0 OòÈŒu5H…RºMRûŒÕ²Ÿ€É:DÕ*µÈD®óTƒoD\$Õ3´6z™=³Tµ·FTì¡µÍR\r\\®á­F\r@…<È)äo<S\$GL	Ã\$tª4-œXZ]ÎˆŸÊfñÌ2iÀs\$Qò¸%6¥È+G”cSFz¦ğ4+LlA=¢W„.lG¡,˜œ\reÀıR\0¤Ë”­)“3e«Qf½P°ÒVÓ÷„.¬xÔyƒõHÀbDI›H¼xÔ{€ÙGÌû4÷GìÆÔR\0¦õ T‡R\n¥%@‰\nKI}%T…ÒS3\0ô—Mú¬A»ÒEI\$£ÀıQøÃM't•‚\"E'A»ÒI(´›;JH¬b²ÒwHä‚¡»Ò9IıT«GJ˜T­\0‡J5% äQgJõ)s.RÉJu#tœRE˜%Q™Çq.¨çc–ƒKt‡4¸…°È`¬´·Ë‚	U½NS\r'Õ†µFàE€6I›K(ÔÁ€\\\n@%H<ÈæIL¤²<–s\"(%@(L	ãPqÓ\nC:aR¼ÉLØ3Á—&rCÃ&SFZ´Yó0>ÁÄ¸@:53MdúÔ»Àü’R-4x 2I9G9iaGQœg¬ìö£)(äT¹\0ÊFQtâKŒ	LDeÓ”D¥kq¦A¡rAN@wïDB&5l;Û‰8ÚsA\$°,h'€!U€€\nA² \rÊ`(\$#@NúÙı	òª\nÍJx‚04‰ê„Ó÷O|œoÈÈ\$ŒYàØÉËD¡\r)JÆŒ€Ï ×‡ÿ\"(5ààÊóBœæDÜÈï'9Fó0Í­`Iì•5“ïN§°cò?E/Ó’±`=¤‡¤ğK¨0!c0\0Ò¼|‘Å\$ĞHÌFŸÈ0I­FJG&İ\">†´\0ìu!ˆp½4m€ö\0FĞr¯L%HOœD)Aî\0@àbX	b•Ğd/A¤kl)Ğ¥ÓOUÄ@6cM‘b`Õ:À¡U:ÀÓ²O ıãø€¦8üI÷€¤\ng#ğTğ )€®Ÿ¥O\0¤\0`	ä\0˜(şiÀ§Šœ \\ ¦D¸­˜\nu>'¥T…€ Î?j€®b~`&\0‰`4†IBu	L•SAz–Cƒ¥¡ĞÄi~Ã8z`!¶Ä?RµK-%¾µ;53TÁeL@ÅTÉ,9qU\\–Ø )=\0„D»A²•ø`É·<¸ªÜ&š@TzcõÚ(\nò¨ƒ;Ï\0¸Ç4Q’U5Ô©U¸µ+Ô²åWu.KV-M5—ø`'€ÃIVŒs5(‡Öe	¹alUd?p¤U_X\nZõYyU­JµtÕsWeK•^ÃÍ[\0Bö	å_fÄMWJU~Cp–­_5T‚2\0 µ‚yXì;iPBÌ•Ä'qDáW-cQ9‚*•LFË^€ò>p<\0•Ä†à6Öf3\\H,øÆÿYQ—õ}ƒå<Ju˜åY•f‘·„%YÍg`6V{LM¡M4¨)0­C…´™İj`9Fê°¡ÁÕ¬¿Z¡xèYV»ZiÀ<µoZÚœ5±€NH­j­OV®Õm„ÜÖÕ[a†HP¾TmmõµŒ:j'\0<Vç[Ü^Õ¼ÖØ0°à¯!?\\ou°ŠÃ[Íg™Öø^mmUÆ€á[Cu¼©Dmj ÖVÔÕR\$³×zga¨ËZÕsà¢«ìÔn¬\r€ç&€µµ+\0«.Öó]xõÌ†šÉ½vc@Wk[íuÕİ\0[[¨Pİƒë]ív×‰]ª¤ã€Z¨:+¤VÔeyàŠİ€ˆ•¼ƒı^˜OîhVò€<UìLÏ^­tM¤\0OõÊ	%\\æµ×\\`°Q¨½Ë[TúÀ5×ÌÅ|5µ˜Ù]AjÂ2%[S¿§™\0ÜmôßT[P8Šo¦+u\nÂMVÔUA‘4)\\x<5íWë& Z³ûŸô)4üUúŠOñ\0¿]‚,ƒ‘`¹C •TppdÜØ? ·à7\0ó\\x­Í¢\nÜ\rªŸíX4>}jJoXf†µÜWêSğŒ”Jşit•ª€ñb\r‡vØn½´€ÕÉ€Åa{4@èTa=vfÚ\0˜å†öE³a5…jûX­a\r‚ÄÜØ³`í‹‘u•gaEŒjXºÕŒámXµ`Å‹…Y¦vÚh]´¹XI`Í-ØÕaÑ“Dƒ\0ÿİŠìÚXÇcĞV5‹~2@\r\r&ŸËd²VAØácİ…ƒÆ†ñc6GÙ]\0‡5’óDåvF4óaE…VLWËb“¶JXİaU“6¸™Úgi^ŠMdXÁ‚·H¬¥oÕÆYGdà¬±ºÈ·e”Šõ×X­eˆvYµ%[UgCç\nÜN!%6\0GÖ0e‰aM	†(˜gò…±dóUvS¦9d0\"QyN•_ı”ÁgØSf¼]a–W¦3Ø+ÕÛaf ¤Ör’“)üMÏYÏ[Ìxaƒˆú!ğ®Ùà¦ñ–zRıâAYò\nÕv+4Yûg9zL İ v€ÎÈ6\0€ŒnõmVxÚiwuô/ğ\r„UÿØÓb¥›ÖPÙ#cê–?×afíƒvX¼ŒUš‰“ÙAg…‡)Úh½‚¶ÚHe›ìÚY\rfxCRƒØíi¦¢ÉY\reM“`2FëeÍ§–“Và¥* Y­i´]aTÚ“bÍ¨Ğ†×j}¥v¦XYúŸ®fÚ©j]§ÑuÙ²¥š6™X±j­«¶\"@\n€áB\nÌÉE¨¶–X˜	‰ÖÚÛj²gv1Fê}  7J¢À+Í¢,\0ªQ\0è;`B€ÀZÀmªk„ÚZê~ÚëbÅ®ö¼Úöm¯­&…«l°VÂY¹l=}ö®’c´Œà5k°ŠñºŠ5g ›`Y§8ÛO6ÀƒõlÕ²€Û-d\\b‘¯ZÉlü]v»šmv5½‰m¯ÖËÚØØ\r¶#?	g¢VÖÄÚèT­¶@ı)û]üé\rxÚåk³Â>Ùk%\0;€×dº–Ù®¬M65bL±öXÖ­€á¤SshE»Ö›\n`S%h'ÕÍZ Ñ—ÄkZ=˜£5Öjmfõ¦Z|oëQ<|`Uß‚(ø76,Ù‚˜:¶›Ö›]ŠdpV´a€ñÁŸõmm¿!ØólıÃ\$ÚE×pÍÂ\0”Ö¨6EšAdÚòÿÑü¡¼/à5u¶79²,mi·ZÍpzÈ—	Yê:…Ä¢>Šİˆ“b\\ipˆ77Øfİ{µqõ˜×Üƒp\\ÌõåÚ0²×Üc5ÇëÚ4î°6ÆØ_c¥Ãvë\\;d5Ä—+\\.ÒıË!ˆÛŸråÊ5™Ü§oÊ·\"9úh“	XQr 70\\Ûq5Íæö3Irè9œM¥‚·°Í ´W•A]\rÎ÷\\ót|öo\\_rıÊ—!ÜñrÅÏWFØ7s¥’÷6]%tS¸W>\0İsğÍwWOEj”Ü\\æmÔÛhZ¤QÊ£s¥WSÜ9qÎ]_r`CW'—^uÍ7\\ë]ÖÕª]qn-y—(Y½tÀ\"°^¨aá„]Õ×aVgrmØ·^]sUÎ®¥š‹ØO¯Äİ£ZÕØw]Wš½Îwl]2¯±£—XZ•tuÕ\0¯	çtl6øÖ…AÌNQ´VcpÍ¿vşÜ3pj úP§vd”]«w%Ê# \\ïÀà<^vı{À‚İÅv5Ò÷ƒ]U;qÆÖXij·ï^ô­ö~W@€l1ÍS¹Rœv £º>ø\n@Ã¬š`ÉÔü`˜,PS‰f»X*À\"€­Vªˆ#î¼?å@§œB€¢W^|¡Eè\0\"‚™yè	)·¦ãz2€«\0¨@:fá­^Oyà3rº:n)ı€h,˜DÀ\nªÚ.áyr¶£ø€¤8şCï€š…éà!^£z=ê·•ŞÆ»ë´òŞf\nî €¦Qcü¦q\"ğ\n€,®¾Åå——Â™è\nÀ'§ì!r§â(şàÂ«f>è -\0‹yên\0/€®L¨ûà/¦ˆøIÕ ø\\µD\0†>íæ€ŞÛzˆ0£ñÕ½|½ï7ÂŞéy…îÀÑËyµç­_\\œUç·Ù^^\rö·Ô€{\ng \$§	}Ğü—Ş_c{÷÷¯	S}°*Î\0©Ã8{}mù#ïŞ{yıå×¡(Ëz-î\0Ë{	 (§zø`&^vÂˆ!r€§TzWÔ^šZg©¦\0±~Eõ÷ŞÔ‚n  €¤Íõ7ÕŞ~Åÿ7åŞ[}ø™_¡}Q2 #ŞÑ|f\0—å_Ä\n²n!rÈšıú—ğŞ¬eëÎ^·{©‚`¤^Ã}õì£ğ^Ğ×(ÿ·¶^¡z6W¹`~mø7¼`'{Õï—÷^ÿ|åğ@«‘}õğà'_|]ñ «_|ˆô×~Ø	×¢ß½z%å×Ôßêš¨kX#\0¡Ö7şßÿ€a­Uª kCõ§¤ŸÕò×Ì\0Ÿ|Õó€\"ß=ƒô8\$Ç©zô×ùË€kX¦³yMï7Ìß6ŸŞ×ÏàÓ}\r—éàİzv Â§KyíPõD»z@)Õ«İşuI¦}TµøÀ'Ê€	é¬U/„*É›Şn`kCúíyíı8N_æáÀ	€*€š™ö£ú€¯yêz7şŞÑ}MZ #Ş\\%VXU_Ö	óuarœŠÑ	U…N÷¼áC}MúWêaœBz8`aW{ÎµEaTv7Ô§¢?2°Øea@­ıX,^ÜœØfU†vØEa£šoÄÊàW…¥çW”anšI\0ää?ĞÿƒÿHyØ\nWÒÇ©yjfár®ö?eêxzŞü•æãğáûƒ~õ@_© …O€%¦ˆ&cñTø?æw±MÕ}=ıU<«g{@\\£ÿ'£y}õJºö™À	x†&oSÎ\$\0)áM‰şWİ¿‡Y\0ÄU±‡€ÿ  ƒ	‰P¸{\0‡Æ& «aö?û8œŞô? 8__‰Eıõ>bS‰íüiŸĞ °üê?ÆHCùD?@ı@(bY{nÃùâ?‡–*ƒõßoyºë8®â¬\nµøjåá/‹\\¸bßªL¦*Xªâm‰ÆIôÙ‚–Ø€Á‹Fmâ‹ˆ>.—§`€~/\0«\r‹¾/ à‡ ²jÉ¬&zàØRèŸ=Ox°§Ş­¢fãğø`ıŠÁ¦€ºÆwõbÓ….JÜ.®X	8ŠÕ\rTw•*{xŒyDzxÔË‡f68Ôßéˆ–6¡rÆáÙ2˜Œ­ˆÖXLÇ©2ÀıÃøb}Ş%¸¬âaŠæ8£øâQVİN¸Ÿc?é¬b?\"t¸×b7¢p)Ï_ÜšÅæõm§Š›~øì&ˆ?&9ãò`‡zŒ* ã³~P5A¨B?Ë´Ò0?¨\nS,a=Tø*Ø4ßıÈwÑãõ¨x=Ç©Šıçì(ÿwÿU-ˆâ³˜ıä„®ØHãéX*Î.²L¨TŒ&ãV?€âiŠ¾¸—â·‰–BùäG‹Î\$y	d3ŠFR/€¯‘ND˜—aß‘89_n õû¹\0V?\0ıXüäi‰Ö2 *duş5ù^¢°Ù!Ş}VîÕaÏ’rjà*ãC’8n\0’ŸŠf÷´d±‰‚pØÊßİ’^I¹\0§'Ãú§£’şM1á©’Æ\0Y7d™“†/ãğd°½ÿÀ''‘\0şw¤ß)^i c¾Ÿ†é¢bÇ{ÖM\nÂäô™õéøÏ_£1wò.ì­&0a­UVÒfùB*åyB¯	ÔãK€\0\$€ °	„eEƒ[•oe–€ÂÄ¹~&X”AÉ„éùAbıT¦»b£üIØ¨€?nB„\0†^¹B_øšXõe…‰\réÀ'ßİ–2	ş—Óe™’–/@á;ğ*Øèãõ–æDU=d‡ö'ùi(u’Š³ÉÄ_O‰X*ÊÑ_ÆºæT	…Ê	øJË’ˆ	®å—–[¸jT÷{î@8‹€ª7ÀåòáĞ\n÷­Ë0ü\0&½T¦7Éd<úzcòâºÈkIè*åS¾[¸leZF* *\0®-Z²\0¦^iÏfG™.1i¸fP\n²g·©\0œŸl‡ãøŞ¥†È0©ª«“2Æe×Û€±™ØkY„Ï/€¶B‰£á:¡Š¶™dJq¹Kf˜?–g¸LæÖj É{BjÕmæ°†)Äfš.eÍœ+h­´ıØ8o‡v\n9-†µö\n÷Ö“(?p\n€/§\$?m÷œ_<¡ş%9º®³›½N×§Ş×\"˜/Ç¯œoiæÿHænJ¼gœx !æøœoÙ®\nš(Ô_\$ÉââB¶¸~æÅ|¥òÍœL°@HyÑUª*zXğâ¢5érfND‚	ößN?Ö5”ã'>Aµ=Ôú?#ùaÅTE[Ra-‰N¸S+qĞ‰ıä-Òz5=å‘Ò¹Wóä5ˆ\nˆ8šbx~Yºcò›ˆıäÕ±‡fFÓ,\nÈãú“)î|‰ıÇÂg	Â+kŸéı\0HÆ*@ çÈ@Vy¥€†šÂ|×–\0ƒ†Å[é¦çí~®)cıb—yF~Jä“,?FX³hŠzq.í‹ıÊV?(ÿyRU±V®yö+jœ\rôõnè?™úˆ #»VÀÉÏh3Œh	ãó\0iŒf4à*\0qŒf{™Ö\0¥ˆ>†À\"äıŒbjXÆf<>ıı˜È(b?b‰Š'İzH	\0/äµzÍğ)÷(b–~\$ËÕŸÆ}Ú')¢~cóçÍ¢†Š™±äın+¬bp»–.é¨b¢\n°iŸg•\nhsÿ¦‡zh`®°¦…é¡èf«©ÇhòÌËLç“¢2j ¨õz-ÿÊ(£î…ªÚå­ûh”L®‰‰öã€?\0øc€¿ˆTYûâ¤¥ú˜ÍıJÜ…@\$€ˆ\"fùdgåŸ¦Š9õè»{*gºTiW!S‡9QH¿™æ_ØCàíƒ.÷Ğ_Eƒ^~xŠ¨àœò¶éùä¡¦à\$çC‚öBÚd'>è	‰ö¯£:~yúÃ£>Œn&p†BÙ-ıû‡cõ_©~´{XŞåŒ÷ä«k§68ƒò€‰ƒ&mèR?vÕmå3VÆ9ºq€‹0	)ócÍ#:ˆ#òd˜’·‰ëŠÖ  ê	‰[ùäiS¢}¹±£Vu8öUSŞ<ãùßàN÷÷é¯ynt·½å{âj·ê^Ù¶£ØiÏ¨öO€'å¨ö—@#€¿Æ€î€˜ş\"™ÇgY¥ãûcõ{æ’£ÿ_Õ©zp:˜á?Vªå(?jk #‰êúêø`P–~IÅ+kV¢·­U'*~Q.^“ŒUãù`F«¥ü™†e¤]ì8ñ¨\n­ª°Ù‚j¼»À‰¿aK…æ¹ÜåÇ«n?éù'p­Âµù¡gaƒ¸üù²€øÈüº(eòºÎvËÕ‡Æ<é1>ãíÛ9P\"@<Ñˆù“È\$`d€9ÌÖ»g¡\0‚ëF&´\rq×\r1b\rÇï\$;@Úƒ‚-@…óI­LÜ‚ïTP#²;%ˆˆajŸÌ\r}2rMÊÅeLåaRŠÊôåÚàÎõÓƒƒªL´—3Iš­±šçk…ardŠk¦ÈzsÅ#LğISí¡V»zÏ\0Û®Ëi ­k«B&¼¡Ïˆ#X.´†µl­¹Òf†{F :âS†í0À3Î–j¸`©Wİæ¿ºúœ§°úÿkóCe¹±f*8íi|àÂØ£}ÖoY¸ã§˜ÍÀ`\"\0…!P)èá!Pÿ©¸çd˜\nÍœhnFKcù^İˆHş¸‘ŞÍ>…)úê¤Ğ	·³çã°ÍR &†µ¨šzXKåçœ™PlqURşä†F\\Ûìu²BxRáO‡FÈØHìv*}ãúloöÇ;'l‘š’€·ÿl @H	)¦²J)ö^{¤%ñ—ù«DµO\0Ã˜†ù>áHJĞ0è>²;€æÖhUTBP§#´‚{8‡ø\0?Û:kqIaÍò]‚ HĞºèëHá{4ÒJj]è\"°í&€¤ÛF‰)\$E54Ô!e	àä\0í\$ü€ÔJèìŞ•Kxº0äêïC,ÍRAK/Ë¿³0†Î%âQ6yµ–Òr»€¿P‰›ÔØŒ’ÿÛ\\ƒ˜%ÂÁR	01®ÔÎˆ]/(8Ûd´FÑÃÈ\0Æ€dˆØ™½^äTÛ˜L)5s€L\n“ıòmƒG#ÆÒü“Ø#\n+ 4ŒkMíD´à9]N	Ê{R—š*—à>\0ëµˆ5{Uíä·kA³€Æ¿Ä³A˜9€±ôÇÛT•Ñ÷¿ÒT~\0ë…O¸\0asÏ€ÆœyàÖÜ*È•ñúÇï\"|rñıOc¤yö:G¤	¬€rz><€D&¢\rŞä´2H\rC\$œÚÌÈŒrWmà)ÀR]›¹eI€¦îk‚Zµ¯a)€ô¤ L»œ0©®çTØ¤•&;¸8»ömIä.ø}›¦I›E„ŠÒÒn¨+4@ïJeº¸ÖÂMYºæ¸ò¯„”PîÙ1İ‚Yº¤Ÿâ\0¾¨IT©/µ½³“*ÛMha4zí†\rŒ¬[»ë‚‰Àô¤RÅõ!on³/vğ`4TDQ¶äVW†ºTˆ£Ú„M¦èòÇ)„×&ïÛ–n˜ÌZà†Ï¼Æî³Ik¹À}›ÍVg¹Æ¸3•ï#9^¸ƒDïQ9kıôÊk‡½fôâ²îáµN²\0o9JD¤»ºÖg½Îò´YîØõèÈG*ÌÕ›¯n©¾ 5†‹»›ænÃºôõÁ¡ÉkØkBîÈ£Eû{ÙRHkZÇŒ–ä<³F‹Z±=Á€¦‹jÍKü/%|Œ3}ÈÙ'RÈÈğoÑ')‰SÛ–•¿i@6€È¬¼¦Àf8nÙÛÿ¦VZY^ü\0Ã?ø8¤Ìo×·”›õW‚\rÒM0É\rı‘ €ÍXbôŞ…ŠLL\r±ƒ7Ó0›rDWõlşØöLˆT8¢0“k;îşíK\"+Q›ïÈÔ\n…š\$†uºÊyÊ=v˜C[µ¸1·—õ ì%L÷mîß_ŒÌ ıí6ï	!.¼ùÎ[[ÊµvLÄá,ğ}À‡	8ƒraxgÂÊÙìûÃ%©ã\$vÏ#·5a£€ü©6R\"ğØ!pO´ù6zÑ}I\"æ€ì>ûÒ¿íÜ™AµŞOë¶qù9M¸âlßk‡TÔ¢@\$ úÈ‹ÄtFÆD›¸mb&|IDlîø@ÚIpo·×W9uØ±FË5AÕË[€Û´¼&ĞZì„ÛnĞI{ÄÊ8•òÍ²5qe4ÜYlìÂ¬hñşÄÿÂ@šqb%[yšÅğC‡c6ó½T'¤6tø\\ZkÿÅÁ—ó0ÜŒ¼²½q…)/ÒÏq”–û :†;şÓAE\0ĞÖ«|qÅ‘Æ¶ÛÎÎ—fÿ@ÿFµ6 :\0îÓ3#\\FëóLcn»m€æ¬ˆ¯Âù³b‚§È(ë-ŠQ¾l`2kç#Tm»G%{È}=üŠk2Ì'\"›9S†Ëî2\"¯ÿ+7!!,÷.¯|‘#\"W¼’…¨ŒU	áƒ\$7¶>¾frP¯&6ákßm–Á{³k³l¯'ZõëëÉî» ¢ƒaÊ³¼Šr‰æ¿\0<òÊ5¸šìò“Ê8@2/ğ§g)\\¦ë×Ê)ü«k»l¯'6âqOÇØ\r»³r¿Éÿ+À5Ğ‰ÊŒœ±rÍÊE}ÜµrÏÊÈ%|¯ò™Ë-|¸\0ÛÊÿ*<©Ó\rËtÄ|‡r©Ëç& <ìÇpd›Hs¯æÀ¼ÃS‡ÌD¤úÿó°0:ıIÈÏ)<É´Ô`kB˜\n;@ú‰WO-;/Ûx©N Ö5y(•NªEÚíÜÃµ‚y\\	ºP&€3H^‘»Ûw?P×6f‰¿uCÜ.)ÙvìÍ¼Ùšc²şÕ#ƒY·u‚'ñóŒ!Z ‰\0ÇÂ¡ù(È÷p8äØñÕÎÈ`0Ø,o²syÎÏQEó¹Î':üØ–ïÏÔÔËìù¼ìWÏrÏqO˜˜ã‚r‘sÆ!_Û[–Vaz#Üıï:	zM¢q	Ï9ãÈkT…”²İ“° À´wĞ×Rï2a	‚WôÏøö‚ÍÉĞuÂ 4]“\rfÒà‹€ñÏ\\ìİ\ntCÏ\0I³±ôú€!ôËñÏ=P(¸'Ä×C\\ùs©Â÷Ó•ĞM~#˜D¦(!â+“ª•Xé\rø“]ô•gI³IÒ|5œísÌÖ_JœÿƒÏGK;\\vN¥Â¶¸t³Ó£ïlG@WmĞDıƒ€á¶OA7mÁ8 \"\0®ôÓQœ‰|,t¡Ó 5=3ùÒÔ³İÏiU‘ù>ô}E7A#)D=Ña	‘õô\r ö{O€ñBOQ2+s¦á:¦gÑÏ=J…ó©Ôç6u„MÄO=üûtÕ	>ƒ‚i‰²½ô§Ñ¼Î\0Æb`åœY†œø[ıY ­\$&€¶i•é~–§Ö\0­e†´\r`1bXéÕù•÷gƒ5Ö ¹İbG)ÖGD{µ€áº0o`7S\rÖ?8ı%†™·ÍÑf\0º²ˆ¬qu„XùÉÀÂ•ïÍ¿Y%5ÉÌy6¬peÃ8üè„€ì0`×eÅ:8xO»Ÿõ¨9d\n!.À²~F¸=jo­Ğ%¦…OÖ£×;PPÉÖ§V…€á¬+F;V¿d*¡rXóÕ…€ãuçØĞ…t3óÚGo\rQ±g\\À‰uÄb>½qs×¾1mî5ÙLrCt‘Ò‡`ô¹vÖº=%örKvo\rgK`„Ò?[Q¶K4¿·9|½Úmã´øUÚ€<Á½³0/Z%„5È8`¡‰5Ã¯Y}b]+×YÔFWÚ\$\\C¬Q5\0qıbp¶pjºuÅÛdĞ½¹u‰ÁÑi}põˆ#'m¦Wõ8ÿoFW€çÀqQ|1T“£¥otd€AG„\\ª&&07ÃEÑœõÍtª}ÙfË)UÒ¡‚ÊÈ,lÓ=Ê‚.\nÍuvŸÇn“=UWØïpy©!°sã%wu‰®ıÀ€»+¿ekütÑvvFWˆ]·ÈÓwUô<ª”Ã‰ôÑu_ek\rk›Û	•û§P\"\nÀY=qwyº`J4Pj¨TóÊÆ5×àİqG)gpŒ˜RÄügnbpô&Ò—zP(Ğ¿=¬Æ7w€Ù×I\$Áí!İïX ıv\\Y]Ø{Gµß nıé]ÿ´]Àıı÷ü;'X4Hyß¸Ï¢ØJ8'nrvgÖ'pş™^({â>	\0»bW\\EÒvÉHıqVû,÷yb¿÷×‰^PÎ\rĞ‡ÂWô“ám×fÏlk•A>Ğ(¼3ïÜ7R|E¬sÖ#Qø;Fr€2xresQ8Aà@PtR	¯ˆ]bmq uÕªÕŞ•w]qpL—íLi\nEÖ…>3G°©Öy¥•öæ®Ó}Á‚¥áÁNálwáÃ<º¾3wÛİµájFñ0p*uËà,}~,¼hçæW¬ƒãXTäösó8øÍ}qs¬/wUÊY¶ÅHR]¶\rÑOpı©LQ¨ãƒv—C@Ö˜—µs^<ğ[3“ÆT#.¯”A|^”ùj~ô¹ Ô\0ØÏÿå`i\0ØÇ–iœ+lüd—xMmĞIŞ<»H¯¿İOI{C%ÖğhŞ`t¡Öwu%ÒQ:4ŞÚ\\8İ”H¯u>)Û/u>ÀZ3¸@oSÀb @Œù/Ñ¼r €ù¯×Ÿx!¿÷v_\$HïöËç(±­I]·µ3ñ=¥“IiĞO§qøÈY\\ }BtG6Ø\r‡äp]Ò¶Ò=,lfß\\Êè³ş+ğPàİºÒvóé7İÎ±pDvø½î/—yÚàoeèÊeÊáˆY¾&öbaÏ9×#ÑO{S„“iÁwoş‘úTL(¾ot°^7_TÌ[­Î—iÛN‚kÍ]ƒîÑ·D> 	Ãè¬‘A.k) Óı£Yi°K—m&W´—©‚Æ“©êxz¾¨¬ƒÜğM4Ì‚[ÑI•%Mgê‰÷wX1ç7J°Ø›ÄNÒ÷mz«×Z]¤÷t¤f÷­„ê…XĞ-÷Áë‡uiŠğëşÍúùpdË<ÛÉ™.Iü·dîM.®66(bUvşÀôŠ\rzïìpüÀû)Î²üOû(÷NÉúâÏàMÁUzãhY2êúãê¿gşÊ;¶?®<øôM¥Ì<ûZ¸?>Jªqì§´>Ü\0óËDÕµTEß—{b„»-t¨2gû‚¿ ¼ÂwõÂß¹…P·î¹œ%/¿B¸Şè™oÁàT’{“Óƒ\\{ë±x¥¼ZóYÎßKõYÒ÷[!.Œ:ç’Ş€Yï(ÆÁ.¬®—h+Á)„]|·Kvë—ı.{õïŸ…ãúµé=À¬té?gK„•zˆ•ù4wµá>œeÖl–W>˜Æ¾•İ?Pôù›v‘×b¾«ëƒµå¤õ\0ï»ıïiá‚>÷>wuÈ;a6‘Œk¬ïÕüú)Ü?Å½ümdm6½úSÀ‡UùİäˆanCüz^!ƒ4õ=ÚÜ|~M§È?ùßêKñ?#—³½˜O»OğMò8’3úÛ¯c÷mN6bgI&tOåˆ\"Ÿ,\rò9=‚Ap#¼'Á^wÃMºÀÖĞ)ò8T%w•v×^í´óò<ü‹Å¶²\\OôÂç}ı\n¿3í?Ğ¯h?E‚YÑ”ª!½»…çÒEaQ‡ØÇCı3óo¨Ş^ûßò­gA,ıÔWE‘Ğı1õ0%XK4Lûß‚¥.ß	^÷|ß)—o…auSéo×=ÓõiÄ×?@ıy4wˆ4>\n´üM¢Ñ[´lFÿcx~Õ¿½öôEó´½ÕÎ}ì’2™ŞÁ:4	#µ·Öİ,|ºËètqaƒ^‡!ğ…Ì˜i\"ô..÷¥rÁËbg\\œéëªàŸKÔ|ğ#g¿}vÄYX]!w,rš^ùvÒÇßİIÛøH!à;×ã·q¹‚JÅê¸’?zùW×—âß{Ÿ‘ÒÇŞñô¯ò¯ã´`I›øè7DÚ}Ãù\0ñu«Ó˜+Ü2~Uó,~­Ó~]ïO 7Üœğ3EåW_K•ãàı:ƒ‹Ñó^sú¯Ò_‰ÿP~=èÊµrµ£ù”ãöì`6¥õ-ÂT—aüH Œ“	t·íOSÂ1Hyn·ì\ntwÙK–õ=Óÿìªp„§&ßÈ?ƒvuÒÇî>‡õMûŒÔà2n\$Ÿã¡?LO9_¤2AúäÂâ²“i¯àI2Ï|óñWKÑƒw]eÏ¿Œşû¥u_È]?øª›ÀíÿÑ=„]èJvìÕÅ«\$ÏêEŒUû#æñı×Ü·?âLuĞf÷õiŠóÿŸí¢\nèl™£˜pmıÏX‡>tíf\rx–—ıá•äÀ‚s¼§ø€.Ÿ*O˜(Aî½P?4‘Ù]çx\r^ÂE8nJRo\0Â¦ğ%[Ñ\0004Õôw%_êKÕ[SJ^ü¡”nÂOKVUòTP¹‰Ş…9Ü»Á»¶?uF§ùSˆx ¹D”šWµŒ&CxËşr¤€;’˜.8KyUB¸\0CJ	ˆ¢!”ÿÄHeWüJÀIf†äÿš\0şÄoúÕ/¿íV ÿìÚ5Z\0ïÑØ¬UÌKÙY;EğÆjƒwD¤·Õ¢%`n@‰«4FI*o\0`À®Ã åV Ùq)8c5Wz«EFõ\0ÈôBñë~ ¢DFÒê”ôÅ§PÑAšYŒ¯A]˜fµ¼*ë -+Yf<¾àÅ·¦úà/‚_€Â°:DPÀyÖA,Êm––bÈÕqàó 8­­Xè³HÚ·•®Ë\0ä@u\\ÈÀ!ôâdë^OÚ„}%él€>Â×ŠŞVÇÀmYŸ\rÚ½21P\$”5ÑrHêDÖéÂíHÅÀ™€äôhô	E¸e‹!@[Zíò4‚š(£`QF5µ\\(`Oğ*@9\0fDµ~k• \"J×QÅÀBDá\0@\$ßDNˆ¿ \$õ€k1ˆ	î}`(†R€|`PåÄàÑ6\"KXÜ¨5¼*Öû\0PÔ™Ú[(\"fƒ”áÆy,”1\$ÎÑ;\"8\nLµÔYX7”¸ÆV9¸gX\niØHapSI®„Yš,‚º!·äVĞ3€úåî\0C8|ø ¾ÃÀ,€N!ÕIq•jëÈØ)ÜÀæ áÀÒˆZX9ä+jÈ¬Z§Æs,FIÏõgŞÅ¸»K9…QĞIËœ9cÙ«;MÕ[‹ÿ˜Ÿ0U*¿»²ü–DÛÁrÁ0h‘§›åR«ÍÙc±aÔË)ˆLÈ&pG•F€C_\0Ù@94×şÌ„ÙF3Î.²Ë}™{5f^ĞT×¿0âlƒ…ºõAùš’2à‚lÊä­Ã(+0Yàœ¸¸ËÑŒÛ2í,™«•x‚äÈ‰¤BîĞ]Ùé²vd-İŸ˜0Üÿ‡Ş‚8Ém”û%ãg=Ù“±&Á-#ö,ZÙ:±ƒ¼Õc:x/ğeÀ´9düÆÍXeó`@(*İƒ-FôÆEü »±e‚ÛAˆã¸.í`»“X(<Ì\r<UÿÌÁUD€J&ÍÁ—#Iğü\rF	Ä/–clÆ¸~°üù™îAw_øË¨Ÿlæ›ÌZÉóAÎ\0ª½¸­¸20QàÖAœ'ÒW*ì¶xY2'ƒ€ÀÙ˜a\\¸<Œ¹	Ÿ±A‚îÔ™ P6tpg“\0Bô&U©¸>µAçƒWù–Á3v/pzØ7Á•„\0¾Vì :kÏCÿA¥'Œ2†Oëå[\0EexÄQ)[;Ğ	pQYê³ÀbRÆ9„–bìõY_&8pA“#\$0]!x‚@ÎÍ¬\"¦xP^™ãÂ2'Ä„[\"qPh¬3Á„T‹š|÷Öaµ[bï	\nËF¢îĞH Ô²èƒB\0®\rXHğŠ¡&±Ï„˜¿‹<'pH0Á ƒma«T#¦]ŒÁ‰Å0æƒŠ¿U|s#(LLO å5g¹‚x™hE,P×èÁÓiÁ	­‹Lv]P!<Vƒ¹	õš´8PrÚƒA ¥ˆ<(f“lï!Ağh!Êx>à`üÂ5„é}ƒ2í@S`0„\n	³C`	°¨!B\0³&	 µï0ƒQí•ˆ…9©Œ94Æ™Œ˜¦“8gøÅIŠc\"zéØù±Sc\nÌŸD\"Ö)°J1¯cÍ‹;X/¬~Y1…ƒÏ~\$\$1laa!Á”„Œ†¤\$˜G,aa*±ª^–Œf00·aLBã„±6#¶0­XÂÂ_ƒpÅµŠó\$…åqcBhdb¾B	|h9¶!bBq\0†OµŒ+¨NğÀa=AÙ.ìÆü0†Œë÷a„B‚c\nŒ(rpz¡ˆÂ‹†\"É.‹>(X}aIÂ•cÕ9Œ,)æ Ğ¼CÂ¡&ã\n hd0©á’1I^¨ÀÎd ğ)¬aXÃ°[c½ü?95æ1En´ 'ĞVÍ£Bö²n0VarCc4Ò®K2V4í\"—é€Kl8½œ?“/–<,ËšLÁ2j&ÓØš‰8	k¡y‡İ…ëP~FŒ,v`1ê_Ó–•ÿ@áACU¸ËETiCf=Ì{Xˆ—Zd\"Â¨×(&Vù×°¶DfJV›ôæÍ]·°èƒ“é; Âk\"—üB;`)!€«!˜ql:ÂÂm…÷)T\nzè\n\0—¡C›zˆİcö–ø)Ë”Ø¶K••½[| üÌÙ¼å\0´N•§iîŞ½Zvğàé™‘[Vl\$ÜYµ³*fHUì+5æåØÒ¶Zeš¾éŠËWs\\¡ÿk2¥ä-KºÖ†ÏàDGğ7@lõ³ËŠÓf¬ÏÙ5—XfÄÌĞŒÖcëäÚfªØ_ü©î‹:6€Â@,Á'<‘œÙÄ2°XØ³³l'ÚËÑ<»âz,IßBÈgˆ¿ªã\nFxÌ¯Jå¶i›ªC<ö'¬õÙìÁN\0¤ÍÁ© €f‘¢3sˆ(ÍÚüAvopÄ\"\r3qguœ{8`ı‘é±KhLÔÅyc\$mYÒ³Éh Ğ9©\n­Æ„­	Ãù46hi\rˆ›{CÆdŒèJ¸°¡h„]ñ¢)Aö«ãK¬3Êi˜O´ #FRë, ß´xf4OA¥êR²êìğÙ”\$iÔOÉ¤	x„ÌCï3xg\0¼œ+9…-R•³.ˆ¢±˜ñG¶˜İ—Ÿ´Î(BÅİ£<G \$Ö\0(&X`0OÉ¦ÓöÄî`õ4éişÊ, ûP(npW€'\0_ˆJOŸÓ?ÀşpûCøµZj;ğ?ìHf)MS¡Oğ&®½¤«£UuèÚ®²Šd,Õ…<BR°Äü˜rµg’Î•ˆ/E[\r^Wú´jôÙ¡B#f¤|¥%b”nÙå2\\âIV›Š¨z€à%˜TkT\r7!5È(tƒºFå)q”ˆËˆñF¥%›˜ÀĞÌ”ÅÍcÈ6ßèüÁ6£ÿGÖÜr?&Ó)ÇÑÿ·2<êáù´H˜7Öíôßá> m”á)´KŒ¯`ÜÆ6ìRêÛÅ4úaöĞm¡[o„šGòßµ:âMt‡ƒŸZ\0	DÛtFûoÿMÿ9=ÃKL	q:ú]W™¡‡ñ¼ÍÂ(tP«7Ó@ÅBâMq¶ãkğM¼Ts\\Yâ2%¶â?58ÔÁ6‰\r\níM·³øá½”ÀÂ·ok˜ØN«]‡o­Tß¾µŠtñ¿ÚJÌMs©·¤ÄJõ!{µ8u-¿]¿-4ûâ*Xh–é1IÛ¤«ãÌìÓ€0Œc4Á8\0Ì9Ğèğ% †&€R‰v+#nà7*Ä\re^:õæÛ£àÍñâ™?–ŠàŞ±,Ğ]è–ÏwL]®5È Y9à²Io„ö(}> ¯¹(²‹‚&`…’µß	^”Å¬²_‘0\"{ƒHN'öÓ_÷2®Â„m6âüBbÉ\$~Àˆ\$ÍvÔİÈªçÕ‘jª»|‹T’Åãˆ'\r¢ÖEŒk³ZÑg¸­ÔQú™ä¸ˆÑs„G‹=¿*SS¸r0ÑÑ1q_2<1×Ô\\8¹ñp]©¶‹ëX=¤R‡”ñK\"æ%špîáíÃÓ}ä‰ïQÆ;(d‹Èâò1¤Dòÿ\\E§GäYí“‰Ä½Nä:¸Ÿ\r™d(Á`é~büvpîüŒTCâ1\\]§VQ¸TMª<ø¿¯˜F^’:0@0€#€yIšâ@g“‰g‰ƒ\0:8“Nß	Åfè¾A¢\\Orn»¼/‘Á…†Ñn^ˆ=Î½ÈeºãÅ	1Œ>Æ2trá)-“xÇA®c»t`2İßüc§	­¨R>ÉQm5YĞ%7kq\\lCXáXl°\"Wc’Ü-9Œœár-K»d¯›šà¾ëLºè\rùêˆ1xàp_–¦Pmn³^3Ï‡¯VBÊ?SŒÓ’3\\dÅ§/:#1Š®Œ¾êz3|ghÌÅH˜Æy~[632h‘İHk<Œ\\úáYğ]1ÔˆkFˆ›’(jÑ¶Ô¡ãDÆCY¨ti0Ìq£ãE¿Œf4³öğE±¦£Jª\r[9Š4ÙXÇ6ŞÕŞÅ]»x›tÿHk·«}_5»zxÇ²ø#\\8JŒTŒºP•GQ¯_´§\0W\rŞ5£„§KÛcÆÄ6BÊ6Câ¤¹ Cã6™†YPBtµ¶£PÈ'lW^S’6äm—f£áÏw8¤íÃø²ñ¡`ÿªl„tep>'ÈDÚVx†°ØjDoâ†…oŞpM5ôoÓ¯¿À5ÇvˆûexK¤˜¡±\0<:Kmt”\"8\\9èá®”“@ó †²8˜wB®AÌÆW3m-\$p²ºî—\"†Æ.IáùJÙ÷ÜNÑÄºúÎ×É\rdgÀcñÉÛÌT†¶±n´r°\"OEE\r~;ñÚ dP•Ä&>O˜çR: HèÏ*ŸÉ¿4oÅåTtU„QÓE\rE˜İ*:\\\\×ßÎ\n¶GI²İ­ÚJbx¡±ÔŸ6ŒLQ ÷ö:â hìqÒ£©ÇfÂ? ²‹™õ:ê¯\\Ñ“\r|ÿıW3š‚°7œ6¤t—ìôw×ªK§Õ»¸!|	cÔçM¶©£Õğa¥äê7cÇ,ùAÕß‡#Ñå#ÄEå°­™ïßw®±æÀ•G—-V1ª—vÖé4_Çœf<ù«xõ.–Äì)üGó<! ?1ì—m\$)qB=0d°JOJcÆÇÁ‚´CÃóg ñ_i:\0KÜ¨ûIa¤jg‹üFÒ@&÷Q”fu¦É2Ö'º-	MüªÓçó®cèGàwIÙù­¨ÂêğVë=±[?\n+sµW¥±ğcİ\0edêy!LD«Qïê\$)SY<€*“›.õMqØ¢ˆNÊ‡åoálÇö%l1Ù6Ê_µ›q/]Ñ¶·Ip²øÇ1DRà¾\ny´®­(âVÆ’f¾\nY\\Öéµ„9øü ôÛP-ëÒ	ì;uVï€KDë<ª‹0• N @@†&-êzÜş–+¯¦ÖÀDdÆ+nlşËÁÇ\n@Jâé¿*lè“Ùß7DM´£ï=q”\rì!sqQêÂ¦ÈÅ8^xP—Ta‚ÉÆIŒ¤\r®7hÉÀ5‡È£t­`yN9MA#©d’Ò£Ù@ªNÜ¢)’/\"T&µA‚†`²\rigØ!HÇNÒ‹(¶]´øíóû©\0¤ÏŸS´.¤êì‘Ì Jö÷ï ŸºO€BD›†w	NŸn>y0¼)2Öu3)1€ä\nÊüù0¸0Í/y€Û3Hé}ûÔŠ™nŠ¬È¯ŠÔÙÜPLI`£dZ}˜iOˆ6¥ÊÏí”S=¹{B+##_¥µKgß~?Ã†ÿ2’Q`ÀSÛü'l\0ÖÈ![»Ç¶ÎRŸ	È×‘²¢‰QçÅú@İ¸WI„ìlàÄŠˆÚpæ„¤Ç§]vı|ø(³Oæ_ ¿Zu<±E¿`Q6.‰h,ş&N¨]3K´»n‚[È…¤LMö›ò–¾Njä„:­QêîŒ*²ÑYxG3}@ó†6¼§{\0hbQ`”	Àƒ¦ĞkNSßÇ¶p>î4cá0\r¿c¡Åvñ=R8¤eÏ@I#Lü=ékÖ¨Ò(ŒGá“\"Òdav®`|»sÙğÔ’U„Ò	^²‹+TDé.7ËĞD…2TË=t°é.*°]õÏ²Tco+3~!Àóõg‘“ŞMDäªõ5*ÃÜG©¢u\$¼t–üKl‚J\$R¨Ÿ 0Q%1\rcğ’!•€ó>~9R½DNÏ¹xTìâDƒãÇIgD\$D_’’	–J#¥·Éi#‡°uôÖñğÓğ÷íaã «ÙÇ%b2[¥çÃRÉ[7İ&ùU³²¸fó ~šáÈ)zÚåÎšuÔæ¶–×|¥\$º*ñı¥­¢Wm+}‘¶¨İOs¥ÉR@ÆAŒ½#iıY+Ù##­¾U‚Ú‚N¬dê\0Ä_·5	0¥6úF·ìËAÀ®‚§)'lnó‹Y;OÛ•¾Èw“Æémìàé<Rwß·¢u4¡†N£¥¹ÒvdöIòQm'Õï…™;0*\0=õ‘,JSà‰?ò*€:‚SJ0ı~IIA<A‚d&-	&œ ¸ÒRÀ×j@Î59ğ¥€+ÀC@Ê}Ş\n=cC€ÁA£(¶”<çPÒğÌOX–É”Nkmn²ØÀM²Do‹ì[0!qä‘\0ÙÌE\0ğ¸Õè9†•Rƒ£Ú=ŞN–İ˜2Z'À5\"E½âz)-úƒo˜éÏÒÒa&gÒ÷¹ò“î2­‘ùŸÇ¾<çTÒ›VÎÀÄQF„,xäüéó`%Gßµ™I´wñ\$ñÜÈ)Ú_ÆóuS!Pëºˆøğd‡5ÏQ\"OèğF.å?Ê||\"‰)d¨PJO‚5Ê~;*FjLt!câJ’›&ìÃè×M©Ş¥I•2æ=Mñ®IB¦g\\Û‚BuI1ÕšJì‘Ü3˜?*¬c´ªĞaUßÊ!uæ”U‚Æ&ûAwÕàF™JHú¢R´‰i\$ÀÖ£º–~¬	Cšl‡E\n3¤ç»Fs¦‰P„‡ÇÁ8\"Ç£.”±'.Tì|BQéÙ¥>£òsØ2Ş4SÂ”™ÈœŠIĞN¤èØ\\®W¥²º%r½u›Í&œ˜U}ÒkÅÏ>æİä:Ş·»îŠ=JúsníıÌú¤øî¨vàW€\$Riÿ°RJH0É‚“CFKğ—ò«Hjº3?î\$Kpz\" \0		.?ä1,ÿ“šƒ‡ŒÁy?øI\0À×ÿÒÂ¥@–‡XôuV¿ĞËUc,EXD±Eaê±ç\"%D\n«<“ò±Yed» @(D“\0•XÈ‡gú’Ñ•’@¾Vƒ,²Z 4•eĞB¼ìD³R/´EòÁ`\"À0‰-TCµuÀ zA\"¬+Wá-”H¨`ŸÊŞAeÊ\\wJ&[<”`Ãªï%¶Kh:®•aÄ¶InFúÕÂ+†–ã0ê¸¥mKå²Æ²XP‹®[h0°9€qeÃ¬,–Ú²¤\$¸ÙpÅdîË‘XÜ»Å´ùj00e©Ë_V„\"Z¸C0+«eË®ı—DzĞ\$¹„Mòæ¥¥‰E.v<¶	tÀßÙ+•Y½-]Q)0ÃòÔ;@?_.Œú0UëÈ_èˆ#–¸æ¢»Tè|…\rœ\0T^Ğf<0W-Ø‡±…Ù½ˆeP±!š±‰ƒ\r #Ñ2Í?Z1¸cªÅŒò´[³Ã\0«á¼+Ÿ,“î²²\nÊíŒ\\\"\"„ĞQÀ&2Ë‡¿i{ÌJéŒDWûKÿ…2Á½Jô%ÿl˜\0000a/Å€+5ÿs˜*ª{`®Â6¬Àf\nŒßæCB˜-06JùêP\\\n Áh6pÒŒÙÁ@Æ.í3Ñ3¢gJÏüš#1Pÿ«ÎpgZPõšús‡P†™^¶JgN K•I–\"Dl\"Î=ÔApQ™îÂ=…qœ>ôBh…LCbÄhÅ9 y\\¢g)¥êÃ6ˆb½±ŒC?¸”lxZ1¥ˆoİ¡ÔC˜‡±XÄA*äÊ¢ù>b‡áüYä•xavVÔ™ìDeıQ\0'sc8}CG8‰“â'&XcFÑê\"ÉG ıí?Ú[tfÃš#LFrŠ‘Š)•¸iÓHáÜf›²ÉƒDyi¸Ì1¦ã<æ…Ü\">4ñ‚€–e<Q#š~´HiúĞNt!ÇşÌ\n&Dz.´ÊH­ÄÀ\"îñ)	¿°¼(%\"·0Íäm­u…Y©µĞz³g&ÏÀ–EÒ6~[2 b\0U	î\nFE¶JVbJ6Î2kÓ0ŒPÈ‘<]\$KZAs[CĞlÛ3	®Ûg×\r½½8.5Û´HÜí­œå8÷‰ÜÙÑ\$ô…D€r&}¤.k¶Ü­Ä|LàQE&FLIÇ\"Àœ¼0kox»#Æ±7Dş×!V9\rq…À‹Œ©\nËy¸[ˆ½‰Zö*mÈ”Ãšû²ñ\\ÓK½	éÚdzd”oÁß*¾\"ìÁû›yQ‹ÎÙ»0-#åÂ+÷'h•]­ƒ/¾÷È¦ó¨Fâ­ûÓÄŠ7y(S¡\$aMéI\$É\$uMI¿+93œÓ/º£Ê“Ú\r9İN©ÒL‡!šË€* ğÄÇB®ó•ø¼§yø!ó4¨cïÇ\\ÊßX’8Â u™_ñ±£Æ6×0«;ô¥·—O”&:­&.à´ùSù©²³¥d¦0•‚³­0¼eE„ÑµÈ—p>‰òFaøGz	ß¬ûmPŸ=tûÉi²ÑıV}»“|µ}MP( %­œe8u=6‚2¨I€Ìq¡œføfú^MKÂ÷›é«>ø•Ğ’\r´Œ‘ö^ğ7d›vRŠm	ÅIÓHã»9¥—>>]sıRº `Ğs€gUô;˜˜Ô°q“Ì%…!ù€8«ÁŒİÈrÃDü+–,ªnxÍ”5ªÈ€€]–:JıÔ´àIóv¥®ÀÃVrÌ!Í¦ï3Á¢™“Vm)áÈ\$@E`¯º,Î\0æ\0ÄÀàIIŒØœ\0à¨@sfÿ€8\0p\0Ì¹àó·\0005\0d\0Úpdà\0À\0\0005œ2\0ŞpDâ	Ãó‚€\0007œ2mŒœá	Äà\0N%œ38”ÄâÉÅà\rçN&›ı8p|áÉÅæÛ€\09œf\0ä”á)Â“ƒÀNB›ş\0Ü¸`‚çÎ\0d\0İk4àÙÈ“‡§&Îœ;8Ìœá¹Ãó’·N;\0o8¶pùT0s•'	ÎR\0g8ºq¼ä€3™çÎ\n\0s9Fp<äPŠçÎdœ)8„ç9Í3‚€N4›d°9És…§Îœ}9üiÉŠg2N€\0ÊrìäÙÆ‘§\0NH\0i9ús,ã‰È§LÎ1\0b\0Út<éĞ3ƒç;NfœÙ94ÌåùÏS§@\0005œÑ:²pé‰ÀÓ’g%’œÒ\0Şp´ç	ÁÀ\0Î¥œq8¦t¤àË3°gÎYœ^\0ÀŒèIÍ3g&N™œi9:püä`\r p€c \0ÂrLéƒúµçÎœg8p”íIÒS’çoN\r‘8:qüåyÖó¢§fÎ\0æuè	Ég[N:âq|äùÀS´g\rÎ”œ¿:2qœåù×Ó®çÎ^\0s:úq¤ãIŞ³…'E\0006Å:t	,éùÃó¨ÀÎñ\0Ä\0Ô	\$äYá¨ÀNş\0i:w,á©Ís¨g\n\09\0i:öqüæ¹ÅePç…ÎG\0i8*wlßólsÇç’O2œ—9qÜå™×Sg„N¸8¾y0iÎ­\0O!œQ8Fq|èØÓ“@Î©=;Âu\\íùĞ3°gyÏQœ\0ÆqìóYÀSÇçNùœ‘:Ês4â)ÕÍ'€4±8®sö	Ç’gÏ\0i8s	çó²çÏï;†yŒà	è3Şg\\N©Õ9 ¬à9Øà'Î?\0i;úuÀ)ï3‰giÏVõ:^{TàéË3äg\"›cœ°\0ÎyÜâÉä3ˆ'‡Nş+=\"w\\öyÉ³†çSÎ¢›=¶r|öùÅàgÎº;†qà©Ş©¶g5ÏoŸ=2zløéÄ…§ÇNq:\"w¼à	áó¬ç%OB«;rq”ü	Şsê§e€5Õ:vvLæùèÉçfÎòœ#:Fuáiå³¥gÌNœ·8Æ{4ğ9ÑSÊ'IN Ÿ:¦xÄâ9ÌóÕ'/ÏÃœ8‚|\$ñ9Áû§JÎ@Õ<Îx<áùÆ3ì'DNn!=jq,æ¹ò“—çbOrŸèmºt¼á)ûó‹'0+p­8Æ{dö†âç%Ï½I>nuÄòùÄsªh\0Pµ:à	,ù¹Ús gÏÎ&w>ÒvœúÉïó’€P\0œ7=vŒñŠÆ(Î9,\0æxtıyâfØçûÏš<˜ªyÑ3¢èÏú…;6€\\ôYîÓ£èOå a9Vut÷ùåóë(!NŸ	>{,ä¹Í¤q¨\$PœY<vs¤ùiÍ3Ğ§¸Nã±>r„ôšSî'ŠÏ«Ã@~xüê	Ås‹gíÏ€#A\ns\\ãZÇ'éÏ Õ?J}ë…’4·N¡ 9*{äóIûs‡'TÏ›*‡;ârláyÔtèNœ!AŠvtí9Õ3ìgÑN¢œIBZxüá*™çEP} ,m„\\è¤ÿÓ–(UÏLœÇ@ŞqLä‰÷³ÁèVÎ›>*‚¬è¹és³§ÃĞr ;^xtèÙÑ3 §3Ğ.AV†\$åªtgÎ¤¡\rBƒLú:ÛgĞ³;>öuâªÓÉç¤Î‚9.‚|úiÈ³Ğ'OZœQ@’tDûYÙõ@1PêŸù>š„…iÌsß'iĞ¸¡8Æ~uÚ(eĞ»œ)BÊ…œòùûfØç'Ï|œ•>‚vtüZ³™çKĞBŸı?uE)èŒh:ÎàÛ:¦ƒôæ™Ã”8g’ÎH ë@fxì™À3—¨ Î¨*‡:¬ş„äYÆ“Ê§*Î_ÿ9pLïÉÉ%§P¡oCZƒ-*ó»§ÖOTn!>.zœşyÄ)ÎÀOh¢3:fqDëÉËÔç=Ñ 7C*w´ÿú!Óëç³O_DvrTìyëÀÎÓœw9¾€ãÉèsÅhMÎ7œQ;î€u©ìÓïgNÊ;8–Mº\$³³§ZP\0œh\0Æ€üäª(óÃ'Ğôœ[>Rt4ô	é³ı'Ïùœ÷>ƒ¬äª-ÓÃ'ÔÑ‚¢»<|Œğz³Ô§dO«œ9<¶rÌàš(´\$g^ÑaœßDV~uùã´3g\\Q@£%@Rˆ…ÉçSÚ(,Q YF²}ÑT:Ô[ÈãOºœB–rhYóM(‘Îi)>¢{•	ÉôHHãP.GF&sÍiîTXg³OCœFî„îŠsØ¨ºN)=„X‰íóªèQÍ 8umú;3™ç¾NœÇ?êpLğôÛ3Æ'¹Q;Ÿß9B†´ÿ0ó²g~QJ¢5<VÚsÓhÎ ;Ş|\\ï:óüçñNú«EªŒ­º>”6ç¶Î\n£Ã>r}éÁôèÖ«k\0ÇCÅùÉSœ'O¢Í9.‹Äæë”giÎ	 ÏFp\$ëyã³ÛgÀOCá<Jéj“¡@Ï‹œ/=vüøÚ0S§4ÏŸ:Ş~tïéÉT&'R¡±:	abm–â‡gxN[IC45Yâ“ähíNOÑHw´æú;€§ÿNŸ§9€¤÷**s…¨;Î’¤e=&s}ÊK¬çÃÎI¢':\n`Iã³“é0š¹IÎzlô:”èŒNşœIÖ¨JIÔ|¨yOÏ@âˆ5¹é3¸'õNŸEV’ÉÙÓË( N4£¿=š{üáÊ+3ùh²Ò“å>:‹<ğ‰ÍÓßç\\QWœ<²uÔùZ”b'Ò5œFn¥:Ôv¨Ñ ¢ç?	\$û©í49§%Òp¤?=Œ\$åÊsëèjN,¤:ª‘¥‰ğóè¯Q¾œAÂ“m™Â4§àPÄ£?*q¥,ÚBÔ0gP¢¡‡@V|Üï\nWÔR)^ĞØœIê”üåŠtµi^Ï¢=¶tı%ê\"4­ç)Oo \0–ƒm\nêGóÄ'^OâŸ>®|…J\n“ùhóÑ®£¡BÚ“ÌùzW“æ'ÊO˜œ-ˆ‚9½’òQ¯=8p½…yC\r&OÌ¿ƒù/:‡ÆVÑ™“\0	~ìBY£S#e†Ä-É7f–LºÙP2Ë¦Q	‘Ø.VÌôÉ˜…2‡ƒÚ&Y‰óI6\\”ÌÙBfc/Ş™ó%Yj¥[#¯ªfğÍš_Ã.ˆ8ˆõ!Ş±¡cfW%ã?F6ÀR%ö2a\nÚš4æ@f)ª—v¦¯õ™;ğk@R\0êL\0’¡¬{6µù,±%û°Øe¯\r@€¨y\nVé±˜¦Ê\0üLû ÿŒ°á/º„=MùŸc¨24Ïˆ\nanN\$d>ñ2ÔÌš=4Ì_İN&\$u8Æ”ÖÁy.º¾¶+.iPViª1‘‚nÅšı9…ílıİ„g¢ÊeL’9¡óEÚß@o<(BlšÀ«nÕØ\"‡å_Õ\nÒ@’„ÍmnÎå`£\nÚ	¥;öpM	”H~Í˜i9æTÓ™³iU*Ãn+\0àü¡ç0ZßOA‹ô-zzÌœ•´‚©â£ \rHš4DKcòMšŸ3€şDÕIœSu\0uO²\nt3ûW«0èftÉ¬Ô3Ğÿr±\$¤ú¡µ?’jNMÑ¶ 	\0Ğ’¡Ì5¥íVi¡Â4bâÑ T6À0«Ö	¢T\nd‹PY¢c°kQA­1X0õ¹¬’K¦T4óÓ\"eB˜Ì<T\$İI Sî]V--¬Û]p)Š!*Ôg&Mƒ?ÅşqA­1¤fp\0¢¡ó0	”€!ª5¨æ“øRµXÒ³\0jLÍ(è&iÌ×À6+e@!¬Ôw!ä­’TCRb}P5„»¬v¹‹^–õµÈfÀÌÖ<[v½mmLØ…ƒ•Pª£}8&#”Ï*8‡ù§ ‘œ“Ú‰u™³\\¨5Q¹HÀ#X€¶7*Ì`+eélªV±èd‘LÅ—i8ê‘Œyà¡1@¦qQÊ™İI*‘\$ç„ÊT*cÔMÙ•C¶0ÚYaÂeq¶¥Š”°h‰¸Ól÷[,Uæõ)˜GÔ¨aÇµ–İ2\n–ì¹ƒú)D^¡UE’›Ğ(j35ƒYO4lš¬	f@Â§+´Ê¦{6Ğ«ÊåÃ…0\rjeÀ5¢¤àFdÜ%w^\0 ¬ÍM†^œ¡ŒÔâ'\$Ñ—3)P0¬9Y “Y\0©P,Ëú-‰Ö®^´Í˜Pd`Ì·¡‘°èjùO)b%RÍ%Y%² ‚A­9y‚5 ÒŸeÖÙZ\nÕOZ ì\"ƒş¯n¨MTN£½8º’5Eá\0BÔ¾.;I€Ìƒ)•TFª¾3;ªsPŒ /8ƒ‰T‘|*rhÏÀ(6\$\0‡py?~µL\0ÂÏª\rıóæìò`¬µ\0‡L®ŸLuO™‡ãªeTBœ\$#ø0\0*0[¦ûU)‰ô-P\\µaƒT*„…M‡º§Œ(jt2ˆ%M2ªÃÆ‡äÕ`¬°¹ªÇU­c&0ım‘*·±ff#T.`9€L˜‰U<_GLş«Å3–I5&jB—TìªU˜, Š§ğ¥Û&Á_ƒ]Q†«KËôÑXåÕ‹©­	‹eSÆa„à*3fORUˆIv?«ıaÕ‘f¬N¢zıF-,½YÁ ƒ¹Vv#YØ<uO!BU´…½X­TJ¶Ì]™rUC'ö¿¹VíTz¤Õ]áEU¡`3Všq=(>Um*ŒÕ7ª7W0›ƒ36ÆÕi˜8UÔ˜3WReSÆÂui˜!Õ<cä.]@údµ`Xİ÷_ÜÈ²L7*®ğ¯á\0G'ÁF%PØOtÑj×……TPmQj¾¼`Óˆ‡İNF®}_hX•G¡³²äh–Àâ¯Û(v“¤ôª“SË…ˆÏB©U`–A•KK¬Õ/¬WòªröÄzŠêšÕd²ª©Õ=:Âì¡êŸV…¼È–ª[š·áşjàÕH« ÁT©«ÿáJÔy¬FÆÎª˜}êÄÌÊÚUW¬aWòªÓ-Ödõ‹ªõÂÄ¬‚Äş\$%cº¬•[*³2ªÑXı”=dº®,Ì*¾Ö7bÉUÂª+(j®•• ÎÂç_Yr«ueÊ¯õ\n˜ÂÓ:¬mYB	#¦­LêìÖ\\«ÉF¬}dZ¿•›êó°ñ¬YÎµYšË•fë;²‡«=	™½Zª´em*ÒÖh„ıV L¥h&K5k+Õõ­\rj%eÈoU=a‡²8¬¹µˆå[æ\\ĞÃêÛ±öª‰W¨İUÚÇu k>¯î¦6Óşµ(˜c£áÊC«›Yxšº0ûkÕÕ­Õj•<‰{›kGÕÛ­Wv²å]ú¼,‘*V\\F\rYÕ‰^’lÕ]à»ÕòªºÍ§­lÚ¿~—ÉAœ‡ÙWş£Å`5¦aù4Æª“[m—%`J¤WãV­«F°]bæT_+\n/ñ‡Çr°ÍS:Ã•MªœV!fÍª3x25S+OV+^õX¶®eúÜ+V3ªŸ~™äzªÌ´k}1¬Â&«nv#•”ªUg\0\\5—%qI€Œ+‰W¬±UÈ¬ûšãµZª»WƒgYV¸&45Æ*LVg‚ïV³Zôê×s›*6`®IRÚ¹K%šæµe­Öëƒ9Va~½sšÏ•Ë+C²ù­RZ´S–õÅë‘Ö‡HZ%‰òõ† \$Ü+{ÁŞ­Ğİ‰òÿÆµÑ*ˆ×>ƒ·VÆºj÷ªÒÕÎkLU½¬TËš}iØ,§ëV¡ª\\š¥tŠëÌ„+¹W-­µ¾µ}wx3•­¥äÕ×®sZú½l\nç5°šDÕâg%\\æ¶8š½†Y\nVË_£i³P€tË÷C†ƒJËYŸ‹/¥êUåá¦¯g!^l€ö:+Ôƒı\0I\0“^’¬¨¢­3WéA¦_^’iZ&b”÷îUş+E^´›µBa\0Õ\nƒó#Î^¤Zºc2g¬ÚšS{\0–Ä˜•7DŒªP/­hum}Ñ¯2È\0ß×Ñ|ÜÒŠ›Òÿêû5!æ¯­¯_\"›ÓVÀá`S¥ºĞ‚¨59f5î	 Ua*|™¹AäòôéZ™×£§10ú¿ezúşìñ€)Ä†eá^ÑÑ@X-µşk÷Y&ÀÃ¡…€zÖÌô*¶3\0œÎ²-|Ê…´æ,Th§1Lb-1†¥«ò*™\0_^ÑŒŠ.fÂ™½‡äfOF½‚9‰QbKX*fíZú u<z Œ›Ø(ÕAiON }Û5}ª Ø°k1:À%qÆ±˜X×ó°y2¥ƒõú¬¨ªXGUªÇÂSc‹µ	›X;`Š\n½…\n¾À™ÌSÂ¬hPŸemzg„Ó+x“ì¬az©‘>ËdÎ,/Øj'0Á¡m{j“Tíl2€Y+EaÂÃ=_k\r–aSªªj©î›•‡¨X,7,=uj^VÎ¥s<’…)YP4°ÚfÃ‡;Ö,<›5+—b>™å‰5í„û@0Š°şÍ¡™Ì@òn5ü&Ø¡d‚ÏÅTsV¦V(š(3ó«óXˆÙ­ŠÆ‘ëôàêX±d )¤Å«6)Ú“Xƒ&Ã²Ÿxv'¬a´¢°…c¿±W1,\\¬bXËh‡ae™µ@ÄÑìXÒ&ocT«ƒ)vlĞ¨ƒóW0a_P\0?%P\\ĞÖlqV¶®(Ë¬>ê=KÅ+RXØ¨}‰Ã<Ğü WÏS‚ĞÔÒ5VÊDç¬zª”'õÁ›';kÓÚ5T<¨&Nxœ•5*•{ê	±r­\nÃì5…fÊ,Ü™a3™¨6 %¢P~\0	µÿIöXĞe@ÙDA>Àûì:53q¯°É-‘æc5	š6²VÍÙ˜­’øƒlÉ+Ú0¹¯eca…Í“†\"V>“Ğ±Qb½›5“è7¤Ùš³/Õf3_ô›;ül YG²PŞÈ3*\nv+ á£Ç\ni>Àòt:ö–V,“Ô@'dª	w\\ÕüYKVO_F’Ë+\n¢‘³f¼&V®EP+ıÀÂ³òl`Lö¿\"üUæíN@+Â8ªîÖÂ0‚¹j™ª/²ºªz•RèkÕí+\nÔõˆ¢Ê£p(7ÄÎY¹/slÀÁvÌ‘6k\nÖPìÊXÉ³8ÍÆ%–‡VfIœØØ³?aË¡C¦J;+§×X¨ô«V¢šğ	Áú€+”*¨ÜÉA3ÖµğÚ™ØU³=fí“ız•`‰µ°´\0 Õ\$Ó; aZ‹×(ƒMcı„[dÊôösûX°©`?£'¸Qv2@(\0W\0‰f»c{;Ö&™s/æ«?c%SÜÇV\"I¬“?d‚ÁÄ­{F25+šsTèeP¿Q±-Ÿrp‹Ë`lWA—ÏWCR´¨*OÓç`ã2º¥:”`ëÑ0¦b¢ÍšÍ¥1vokèkà“@²àÂu‘=…k462CıAÊ¯gTºÅÕv[ÄHÄ’±ahœ%¢:“„ÏØN²~a:(Ôè•˜AZ06ækÆÑmBªéõÊ!°jy–‹:õÕ	—½ØGn¿š+@üÕÿl§£·\0¨M0³	ÛIëÍ«Ã³Í´¤T¡C	ØRMVŠÄ0‚aMih™ı¡¦:vh˜“Za'‹^ÒÓ+IK*vqÕAûöÒş¥ãGÊœìŞNÚr'Î\nÓ…§P	µíØª×¹´êÄ™HÍ§{NŒ ¢HXô­}d¦}2ªøŒÈæW0è©Ğ½±›5N†­ÖWI¢Zsãiå•tZôLäMM©Ÿ²ÂµX«4Ub¬l/ä8pM2§5Êœ”™³V¾´ÇevÓŒ–µáÀ+,¯Š¾—I'8C íTëµlæÕÀF@–Œm‹\nßrÖ8XÎSjŸ ´Ö¾ÒXînhŸï ”Òÿ†XB°RäpÀ¹MÕ–g7]³ÄÄ®Wñˆ%ÒMähU<`‰„Ÿ4põ®Ğ1Ñ\\ÀÏ*â]åk^ßcGi]Ñ†|	\$­*p0€À!Qx\0Ø–Hy‡w€€’\0002\0^Š‰èYk¤Áå\$ûFèKòÅé3Án­‹Î,¶3lRØİ±`°@\rmô¶*ì,kd²_œ[\0ol®Ù=±ĞÌ-Û(J\0æÙ¢½ûc€I\0’[9^:ììì‹gÀ†È:%!^ZÙ³«bÖĞQ,[H¶MlÖÚ-±ëiÖË-[A¶ImNÙ´eVÖ-¨[A¶_mrÚU´\0\$¶Ìí°[?¶£lâÛ-´C°Ïm´Û-¶ÖÂÛmµYÙÑÅ¡[I¶Ím®Úm·[iöØ@/Î‚¶…mÆØµ·ënˆá-½ÛfÍmşÛÕµKbÖáíÁÛ¶ùnÛÍ¸KqAíÃ€4¶‡l¶Ü=µ{q6ÌmÃÛ[·7mBÜ=µûsöãí±Û—¶9nÛEº;pÓ²\0[¦¶-J¢İpc¹å–ãm»[D¥)nÄüòËrÖà-ÙRø·q<²Üõ¹+f3Ë-Ñ[Â¶¡<²Û%º«vÖÍíßÛ¬·šŸúŞ}»Kp¶Ñ([p·•l¶e½+r`è[‘·mše»»zöÇ([š·mòe¼{öõ(Û¸ Yo&ß=¾y–ï-î[Î·éHnŞµ¿}“Ö-÷Pë·û>²ßı¾k}vö'd[ê·kl¶p¸‹W-£[¸¸ozà=±Ë6ø®Û¢)oæàÁk¶ı(Š\\·§l¶ˆ¥ÁKƒ6Å¨Š\\¸#p	-Àkƒ÷§l[¸¢)oÂácº\"–ên\nÜ(¶ÙoáµÁ‹„÷­¹Ü.·ıprvEÂ+‡`çO\\&¸WpÚáU½¹ÓÖñnÜDÏpú‹ÅÃã°®\\G¸y9â•Áë‰Sá.!\\V¸:¶â•Ä›f3şî&\\CŸ÷pÂâ\n‹Š¶öèT[Ú¶cB¢âÕÆğ\$·n6[¾·éB¢ãµ\nn/P¨¸ÑqößÃ€ÇfØîÜ5¹;\"ã]Ç+—îÜ‚\0¿rã¥È«ˆ×gOÛ¸¹	qòä¥Çû’—\r.&Ñ·[r0•È›j`®\\M¹IqºåYÉvî.R\\‘¹SqÚäuÊK6ß.R\\|XÅpnÛÕË{•vÓ®]\\]¹uqåÕÂ«—W-–,[¥¹ysm²[–÷!`F¬¹s*æ-È»—74n\\Ë¹}sFæÍ˜W4nb,‚¸ŸsæõÅœ3®)Üã4ó>â­Ë{‹9/Ï„¹«sw\rÍ›×.v'Ÿ	snçÍÛœ—îu\\4\0F¦zÄûš7 	î~‰~¹ÿ=bpê	á÷-çA\\ò¹>bèuÎvâDnvÎ)º)sÚvÑp7s î\0sœ—sâè}Ï«¢W?.b‘Æº;8²äıÍ;›ÓÛî“Ï‡º[s¾é]Í{¥w6n•İ ¹Ï=émÒK¥w'ÑÜÅ*‡tpµÔ¦3§İ@ºiuéµÎÙğ7P.]Dºytæ,ä«¤óĞ®©ÛÒ¹o:êÓ+™Ó‡n©İI¹í:*êÔÛœó¨.“ÎºËs¦æ,á¬·PîsÎ_ºËuëmÕë£s’.²İb¹Û=6é<ã¯7Z.oOºóu²çm«¯7\\.Àİrº=8–ëÍ×kÆÛ®“›c»uòætä±·`.{O&»vì­Øj·H®vÎ£»u=cX+£¶Ï.ÑÏ„»KvP\rØK«Fi'İ¥»’ìá2zW7inĞ,XOıt¦Ü%Úz=nÔÜÒ»svÚ‘­Ú;€o&ÿÎÉ»…v¾pÜ+±WonÚ¬‚±vâæmÚË”7hîuİ*Y=bíDßû7q(­İÔ»_9>î¥Ü»»Wsnëİ\r»¹tBï=Õk¶×E®óİ«4¿HÖìÅÚ™Âhç3İô»Év²{5Ú;¥—w.—]ü»³>²ïuÛ)ä÷~®×İ8»ùwŠ}eß{½÷OîîO»Guğmßû¨·ƒnøÍÿŸx>Ş(0> ƒîPÒº¡w:êX,[ÃN¨€3^Cv†ñ*;Äw‡)Î¼Qx\"x­Ú;ªWw.ª^4»Õw:êÅãKÀ7jn®^4»_u‚ñ¥à‹¬—w.³^D»!v²ëUäK¿÷[¯\"^œ­vë¥äKÁ^.î]~¼«wşìå[ÉÓ‡.Ñİˆ»¹v:òıä{½÷d¯/İÿ»-y~òu‹´wg¯/Ş\n&OmèéØÆë#NÀ]Ù;xì5LëËÎ3Ü¯=İ±vvïØÍ×Ÿîf]=Yr†óæû·×tM/Ü¡¼íwî±§›”7…nPİ†¹Cyø5LàÎ78¯HŞ¹Ëzvó}İ;¸“o9Ş’ŸyÆîİêÏ÷=/U©œ/z¢ôÄäĞ3mŒÓS¹k^^EoD]ëœÖ%®ÂÏ%˜½q×¤ô\0%°û®¹YÆ»„Ì5ÎX\\ÕŠ®¸(Ÿƒ\"Šç0p¡3C_˜BÁ¹|&X^q¯k±6®¹Y=íõ#ø+°±\0—bª÷-gšè0tŠ*2Ÿ®¸Çb­MEö{Ú ï2ö½ßR‘–¸€ ğˆë®Y‰ƒÕ{õ†…ì–B¡k®W|®öÄŠ5ğZïÒòk™Áœ¯r¼¼µòÕéÕG©­W\$L´8jìğò*Â\"_sNˆ¼ZiŒLvêXª|Š©K	V'O/_Uä¬NÈÙ})3Ú•ĞH/`Aš®|Ñ§É6fÃ×Ë/b^Æ«M{\"ªô\"«Ùp—‰^Î«M{@›õWæ*ê^Ô½·ÙŒ3#+é·¶¡¯^Ü…îÈ…}SËÜï*Ó4g½É|º¹Ó,;İØ/sW1d\npæ¬-Y{åui« U–„Æ¿Jû]ö\n‘U#«E“C«aTJT¿ÆYl\r¯}ß¾ÁVÖ®COxbv€!C\0P¾ã\nµ‘8JÖ•u&Â¾~Uk†õO/£_`«ËÎûE]«E•w/©Xz']O9T\nı6£ +UÕÊ­[XbõİkªÃ>o“Ö¾[:±“e‹0´Y¼BJ¬É~Ğ?!3›çu—\"^ÅbåY*²õûz¿—²áußE­xÆúEí(]·ÒØÿÖ;¾}Vú…x*Ì·Õ!3Ã`_!{z²åõ»õL¡ï¯X¥­}–ÆııûİUªkŞí­)ûušá•¤¯{_ì©U{æµïÊÒ÷àš_ä§ZÎËŒ2÷ş˜½C#­k]úZ­ÚÖ°Ë/Ì\0D°©µô3Kó°öÉ¯²üd³ÚŸÌ8»\$0Ro—Ø ±²ÙrûÜº‚ĞĞ*“k²ËR®¦½—VÌ¨	¸VšlnÓ‰•E…æĞÙêTËıbRÊ}÷j–ğÜ™mÔ˜€Õ^\n»-ª—u´P1ï`R±}MJe–ªj…#åK4,ĞİÇt\rÖœ:¦ ‰k .lO°lî(â—8£qÀ’í%rY¦à³Fî¶¸O¼ßtN£µ2W¸WT\"uj€ªÍ}Tí@ĞÓ×7k’S VwéÚ€å§®\\€ÚG˜Ä<ï²I\r’ıp:/²â'Ú0ö¦s{M©%\"G%:‰Ú”uà‰Zõjõ³Yg´õÎJÁ>à“,öòW›‡T|é&›5·àÁœbX©‘¹3Ÿ„ŒXëæJv¨sÑTS×½I6ŠM FI&ÁhğYàŠqè’¨PY&Æ¼x!I6\n4!†ğº)&Ô07ÕQ10Ø níÑŠ7ÕX3Háru\00053¤ˆCÚŠ°ß`ø¬@<S_p<•jæ¬è\\Â@‡TÁÄ¨ÍÌöt·*¼ÀŒÓüGñ+»P@M{Û8CÚtøà¿¢×GMV&¼§ÀòÚ­ú’zĞ0±@Àš;‰:Å?Ö4ÌQÿÊïa{„\rËĞH©7Ë“H€ÀØ£`|ÃuL\"À”°s¦™\"\0f’ÔÄPTlFãG.µè\n3²u.X+“TvNDşù\r°\\Ì‡*tb}¥İ€‡3 l[“¹™À®\0c™•!aæg‚ue)mF,®ÖY=Du-½Ó¬q—óM“¬‘àyÍ…ÚÆ7\0—0<ºÚw…nEF'Kîâ‡>ab]“„Ã\nÈ&ä®˜Y¢|‰Zv°pˆ“µƒZ cuTéÅÜ[ü.MxÀ7½»ÂóOü+N—k	KiØ:æà05¨³bœ\rºùw'†2<©A3\\áÃ%60²ØVäu7ğÍ¦jkûƒ„åÀ|w1ïr'½òum¥%@I:d°Ål\\Rş£	¬5ëŸ@Ö¦öpë†ÉiğÃÏ)°5ñÃW©;HÏ¥à`®Ã„0¾¤¿pÇ…”ZŞd3·À˜sœï*mVæ_¨Œ:øs°œR¿Ùi)oDp‡áâolû™qf ŸH°aêŒQbt€xÀÎ ’5\0rGÈáİ/Ì_¨ÂñƒÖ`áû‡¨4X­ì-Å¤¿9—‰ˆú<@s;ÂÛ{ˆ \"êjjŒÂ£0™aÎÂh6LFyÌ%‘óŒ\nÊş·Îå­¾pŸ£éÁ8ÛŒ^k¼:c°¢V\$¦2V'Õ\rßA?8¨ÄXÚßŒ=§ÕšCG¶ù€íÎ¶#å±c_ â='ˆıÖËğ×å@VcÇbB¶,ı¤(Ö\$œIÁñ(J‹WFì›¾%\\+‚·„ğ`:ÄnİÇş#œGÑ‡±1¸—\"g‰”mìba²x{q5€mmò¬ÍóôS˜ùØ‰ñ8Äu‰ÀBh&POxVââpÁ®İ¬&&&ğÇ`Vğ<®QŠ€*™;¸ —„E]İŠN'Ø ¯6¢¼TÍHŠXv	Èª-íÄaEBväĞQ{ª™¡˜©³È>CiÚ7œTopğfâ«Å#LV¼0ÔÑHâuà±>) E¨¨øª±]¡4ˆÛ4ª›ñuB:[Åp›R*T:0)xªñMàòÅ=…ùîî ‹JêèbÈ#*¨ìª 8©(@=;V1cSkÉP\n`a]g`PÂÛShàÆ.„U¸q°à ‚‹X\\:™`¤­\0v“¤ƒ|şìM—Æ`Ö±E+…ÅøØ>¼_âJŠv¸Å¤\n¦VØ0l`m¥¡í©N£Œ*ÖHıñFøHT£ÃÁ\0£¨?,b%Ò±ˆ’-Œ6\n-ld+Ê¬š·ÆT/:º¤^RÀfâK:LXBm­‰ºrÂIvKV,âR²¼eàpq˜ÚùDê‰^&\0Ea&®.ù\0ƒB]#b…€âÕê‰Ò\$à@Ü6¢E­¨ˆf€„o€9äLìé,	Ãè×ÁM\0SJèû­gxÒ‰‹cYY_{P) \n¯y\"ØY-Ír]Ğ#8Û–ácd•4^3°6!\\Ô(Ë‘›‚w¤×\\«Û€æ°\rjf©Æ—k`Ô\0Í3lä™…¾«¹kI/İ‡§›nÍN@:”‰'ópßè\0");}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo"‰PNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6¶\0\0\0000PLTE\0\0\0ƒ—­+NvYt“s‰£®¾´¾ÌÈÒÚü‘üsuüIJ÷ÓÔü/.üü¯±úüúC¥×\0\0\0tRNS\0@æØf\0\0\0	pHYs\0\0\0\0\0šœ\0\0´IDAT8Õ”ÍNÂ@ÇûEáìlÏ¶õ¤p6ˆG.\$=£¥Ç>á	w5r}‚z7²>€‘På#\$Œ³K¡j«7üİ¶¿ÌÎÌ?4m•„ˆÑ÷t&î~À3!0“0Šš^„½Af0Ş\"å½í,Êğ* ç4¼Œâo¥Eè³è×X(*YÓó¼¸	6	ïPcOW¢ÉÎÜŠm’¬rƒ0Ã~/ áL¨\rXj#ÖmÊÁújÀC€]G¦mæ\0¶}ŞË¬ß‘u¼A9ÀX£\nÔØ8¼V±YÄ+ÇD#¨iqŞnKQ8Jà1Q6²æY0§`•ŸP³bQ\\h”~>ó:pSÉ€£¦¼¢ØóGEõQ=îIÏ{’*Ÿ3ë2£7÷\neÊLèBŠ~Ğ/R(\$°)Êç‹ —ÁHQn€i•6J¶	<×-.–wÇÉªjêVm«êüm¿?SŞH ›vÃÌûñÆ©§İ\0àÖ^Õq«¶)ª—Û]÷‹U¹92Ñ,;ÿÇî'pøµ£!XËƒäÚÜÿLñD.»tÃ¦—ı/wÃÓäìR÷	w­dÓÖr2ïÆ¤ª4[=½E5÷S+ñ—c\0\0\0\0IEND®B`‚";}exit;}if($_GET["script"]=="version"){$o=get_temp_dir()."/adminer.version";@unlink($o);$q=file_open_lock($o);if($q)file_write_unlock($q,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));@ini_set("session.use_trans_sid",'0');if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",HTTPS,true);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$ad);if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("precision",'15');function
lang($u,$Ff=null){$ua=func_get_args();$ua[0]=$u;return
call_user_func_array('Adminer\lang_format',$ua);}function
lang_format($Yi,$Ff=null){if(is_array($Yi)){$Jg=($Ff==1?0:1);$Yi=$Yi[$Jg];}$Yi=str_replace("'",'â€™',$Yi);$ua=func_get_args();array_shift($ua);$md=str_replace("%d","%s",$Yi);if($md!=$Yi)$ua[0]=format_number($Ff);return
vsprintf($md,$ua);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$F);abstract
function
quote($Q);abstract
function
select_db($Nb);abstract
function
query($H,$jj=false);function
multi_query($H){return$this->multi=$this->query($H);}function
store_result(){return$this->multi;}function
next_result(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($nc,$V,$F,array$Xf=array()){$Xf[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$Xf[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($nc,$V,$F,$Xf);}catch(\Exception$Ic){return$Ic->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($H,$jj=false){$I=$this->pdo->query($H);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($I);return$I;}function
store_result($I=null){if(!$I){$I=$this->multi;if(!$I)return
false;}if($I->columnCount()){$I->num_rows=$I->rowCount();return$I;}$this->affected_rows=$I->rowCount();return
true;}function
next_result(){$I=$this->multi;if(!is_object($I))return
false;$I->_offset=0;return@$I->nextRowset();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($qf){$J=$this->fetch($qf);return($J?array_map(array($this,'unresource'),$J):$J);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$U=$K->pdo_type;$K->type=($U==\PDO::PARAM_INT?0:15);$K->charsetnr=($U==\PDO::PARAM_LOB||(isset($K->flags)&&in_array("blob",(array)$K->flags))?63:0);return$K;}function
seek($C){for($s=0;$s<$C;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;protected$conn;protected$types=array();var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();static
function
connect($N,$V,$F){$f=new
Db;return($f->attach($N,$V,$F)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($R,array$M,array$Z,array$wd,array$Zf=array(),$z=1,$D=0,$Sg=false){$te=(count($wd)<count($M));$H=adminer()->selectQueryBuild($M,$Z,$wd,$Zf,$z,$D);if(!$H)$H="SELECT".limit(($_GET["page"]!="last"&&$z&&$wd&&$te&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($wd&&$te?"\nGROUP BY ".implode(", ",$wd):"").($Zf?"\nORDER BY ".implode(", ",$Zf):""),$z,($D?$z*$D:0),"\n");$ji=microtime(true);$J=$this->conn->query($H);if($Sg)echo
adminer()->selectQuery($H,$ji,!$J);return$J;}function
delete($R,$bh,$z=0){$H="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$H,$bh):" $H$bh"));}function
update($R,array$O,$bh,$z=0,$Mh="\n"){$Bj=array();foreach($O
as$x=>$X)$Bj[]="$x = $X";$H=table($R)." SET$Mh".implode(",$Mh",$Bj);return
queries("UPDATE".($z?limit1($R,$H,$bh,$Mh):" $H$bh"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$L,array$G){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$Li){}function
convertSearch($u,array$X,array$m){return$u;}function
convertOperator($Tf){return$Tf;}function
value($X,array$m){return(method_exists($this->conn,'value')?$this->conn->value($X,$m):$X);}function
quoteBinary($_h){return
q($_h);}function
warnings(){}function
tableHelp($B,$xe=false){}function
inheritsFrom($R){return
array();}function
inheritedTables($R){return
array();}function
partitionsInfo($R){return
array();}function
hasCStyleEscapes(){return
false;}function
engines(){return
array();}function
supportsIndex(array$S){return!is_view($S);}function
indexAlgorithms(array$ti){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R)."
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'",$this->conn);}function
allFields(){$J=array();if(DB!=""){foreach(get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(JUSH=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->conn)as$K){$K["null"]=($K["nullable"]=="YES");$J[$K["tab"]][]=$K;}}return$J;}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach($o,$V,$F){$this->link=new
\SQLite3($o);$Ej=$this->link->version();$this->server_info=$Ej["versionString"];return'';}function
query($H,$jj=false){$I=@$this->link->query($H);$this->error="";if(!$I){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
Result($I);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".first(unpack('H*',$Q))."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$d=$this->offset++;$U=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($U==SQLITE3_TEXT?15:0),"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__destruct(){$this->result->finalize();}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach($o,$V,$F){$this->dsn(DRIVER.":$o","","");$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach($o,$V,$F){parent::attach($o,$V,$F);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){if(is_readable($o)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a"))return!self::attach($o,'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){if($F!="")return'Database does not support password.';return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");}function
structuredTypes(){return
array_keys($this->types[0]);}function
insertUpdate($R,array$L,array$G){$Bj=array();foreach($L
as$O)$Bj[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($L))).") VALUES\n".implode(",\n",$Bj));}function
tableHelp($B,$xe=false){if($B=="sqlite_sequence")return"fileformat2.html#seqtab";if($B=="sqlite_master")return"fileformat2.html#$B";}function
checkConstraints($R){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn),$Ye);return
array_combine($Ye[2],$Ye[2]);}function
allFields(){$J=array();foreach(tables_list()as$R=>$U){foreach(fields($R)as$m)$J[$R][]=$m;}return$J;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
array();}function
limit($H,$Z,$z,$C=0,$Mh=" "){return" $H$Z".($z?$Mh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Mh="\n"){return(preg_match('~^INTO~',$H)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($H,$Z,1,0,$Mh):" $H WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$Mh."LIMIT 1)");}function
db_collation($j,$jb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name");}function
count_tables($i){return
array();}function
table_status($B=""){$J=array();foreach(get_rows("SELECT name AS Name, type AS Engine, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K){$K["Rows"]=get_val("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){$J=array();$G="";foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$K){$B=$K["name"];$U=strtolower($K["type"]);$k=$K["dflt_value"];$J[$B]=array("field"=>$B,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$K["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["pk"],);if($K["pk"]){if($G!="")$J[$G]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$J[$B]["auto_increment"]=true;$G=$B;}}$di=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$u='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$u.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$di,$Ye,PREG_SET_ORDER);foreach($Ye
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($J[$B])$J[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$di,$Ye,PREG_SET_ORDER);foreach($Ye
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));$J[$B]["default"]=$A[3];$J[$B]["generated"]=strtoupper($A[4]);}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$di=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$g);if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$di,$A)){$J[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$Ye,PREG_SET_ORDER);foreach($Ye
as$A){$J[""]["columns"][]=idf_unescape($A[2]).$A[4];$J[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$J){foreach(fields($R)as$B=>$m){if($m["primary"])$J[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$hi=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$g);foreach(get_rows("PRAGMA index_list(".table($R).")",$g)as$K){$B=$K["name"];$v=array("type"=>($K["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$g)as$zh){$v["columns"][]=$zh["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$hi[$B],$mh)){preg_match_all('/("[^"]*+")+( DESC)?/',$mh[2],$Ye);foreach($Ye[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$J[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$J[""]["columns"]||$v["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$B))$J[$B]=$v;}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$K){$p=&$J[$K["id"]];if(!$p)$p=$K;$p["source"][]=$K["from"];$p["target"][]=$K["to"];}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$Qc="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Qc)\$~",$B)){connection()->error=sprintf('Please use one of the extensions %s.',str_replace("|",", ",$Qc));return
false;}return
true;}function
create_database($j,$c){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach($j,'','');}catch(\Exception$Ic){connection()->error=$Ic->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases($i){connection()->attach(":memory:",'','');foreach($i
as$j){if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($B,$c){if(!check_sqlite_name($B))return
false;connection()->attach(":memory:",'','');connection()->error='File exists.';return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$B,$n,$jd,$ob,$yc,$c,$_a,$E){$vj=($R==""||$jd);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$vj=true;break;}}$b=array();$kg=array();foreach($n
as$m){if($m[1]){$b[]=($vj?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$kg[$m[0]]=$m[1][0];}}if(!$vj){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$B&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($R,$B,$b,$kg,$jd,$_a))return
false;if($_a){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $_a)");queries("COMMIT");}return
true;}function
recreate_table($R,$B,array$n,array$kg,array$jd,$_a="",$w=array(),$jc="",$ja=""){if($R!=""){if(!$n){foreach(fields($R)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$kg[$x]=idf_escape($x);}}$Rg=false;foreach($n
as$m){if($m[6])$Rg=true;}$lc=array();foreach($w
as$x=>$X){if($X[2]=="DROP"){$lc[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$Ae=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$kg[$d])continue
2;$e[]=$kg[$d].($v["descs"][$x]?" DESC":"");}if(!$lc[$Ae]){if($v["type"]!="PRIMARY"||!$Rg)$w[]=array($v["type"],$Ae,$e);}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$jd[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$Ae=>$p){foreach($p["source"]as$x=>$d){if(!$kg[$d])continue
2;$p["source"][$x]=idf_unescape($kg[$d]);}if(!isset($jd[" $Ae"]))$jd[]=" ".format_foreign_key($p);}queries("BEGIN");}$Ua=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($kg[array_search($m[0],$kg)]);$Ua[]="  ".implode($m);}$Ua=array_merge($Ua,array_filter($jd));foreach(driver()->checkConstraints($R)as$Wa){if($Wa!=$jc)$Ua[]="  CHECK ($Wa)";}if($ja)$Ua[]="  CHECK ($ja)";$Fi=($R==$B?"adminer_$B":$B);if(!queries("CREATE TABLE ".table($Fi)." (\n".implode(",\n",$Ua)."\n)"))return
false;if($R!=""){if($kg&&!queries("INSERT INTO ".table($Fi)." (".implode(", ",$kg).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($kg)))." FROM ".table($R)))return
false;$fj=array();foreach(triggers($R)as$dj=>$Mi){$cj=trigger($dj,$R);$fj[]="CREATE TRIGGER ".idf_escape($dj)." ".implode(" ",$Mi)." ON ".table($B)."\n$cj[Statement]";}$_a=$_a?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$B&&!queries("ALTER TABLE ".table($Fi)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($_a)queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));foreach($fj
as$cj){if(!queries($cj))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$B,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$G){if($G[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($Gj){return
apply_queries("DROP VIEW",$Gj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$Gj,$Di){return
false;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$ej=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$ej["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);$Hf=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($Hf?" OF":""),"Of"=>idf_unescape($Hf),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($R){$J=array();$ej=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$K){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$ej["Timing"]).')\s*(.*?)\s+ON\b~i',$K["sql"],$A);$J[$K["name"]]=array($A[1],$A[2]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
begin(){return
queries("BEGIN");}function
last_id($I){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain($f,$H){return$f->query("EXPLAIN QUERY PLAN $H");}function
found_rows($S,$Z){}function
types(){return
array();}function
create_sql($R,$_a,$ni){$J=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$B=>$v){if($B=='')continue;$J
.=";\n\n".index_sql($R,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$J;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($Nb){}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$J=array();foreach(get_rows("PRAGMA pragma_list")as$K){$B=$K["name"];if($B!="pragma_list"&&$B!="compile_options"){$J[$B]=array($B,'');foreach(get_rows("PRAGMA $B")as$K)$J[$B][1].=implode(", ",$K)."\n";}}return$J;}function
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$Wf)$J[]=explode("=",$Wf,2)+array('','');return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Vc){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$Vc);}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($Dc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$j=adminer()->database();set_error_handler(array($this,'_error'));$this->string="host='".str_replace(":","' port='",addcslashes($N,"'\\"))."' user='".addcslashes($V,"'\\")."' password='".addcslashes($F,"'\\")."'";$ii=adminer()->connectSsl();if(isset($ii["mode"]))$this->string
.=" sslmode='".$ii["mode"]."'";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($Q){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$Q):"'".pg_escape_string($this->link,$Q)."'");}function
value($X,array$m){return($m["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($Nb){if($Nb==adminer()->database())return$this->database;$J=@pg_connect("$this->string dbname='".addcslashes($Nb,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->link=$J;return$J;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($H,$jj=false){$I=@pg_query($this->link,$H);$this->error="";if(!$I){$this->error=pg_last_error($this->link);$J=false;}elseif(!pg_num_fields($I)){$this->affected_rows=pg_affected_rows($I);$J=true;}else$J=new
Result($I);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$J;}function
warnings(){return
h(pg_last_notice($this->link));}function
copyFrom($R,array$L){$this->error='';set_error_handler(function($Dc,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$J=pg_copy_from($this->link,$R,$L);restore_error_handler();return$J;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=pg_num_rows($I);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->orgtable=pg_field_table($this->result,$d);$J->name=pg_field_name($this->result,$d);$U=pg_field_type($this->result,$d);$J->type=(preg_match(number_type(),$U)?0:15);$J->charsetnr=($U=="bytea"?63:0);return$J;}function
__destruct(){pg_free_result($this->result);}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach($N,$V,$F){$j=adminer()->database();$nc="pgsql:host='".str_replace(":","' port='",addcslashes($N,"'\\"))."' client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$ii=adminer()->connectSsl();if(isset($ii["mode"]))$nc
.=" sslmode='".$ii["mode"]."'";return$this->dsn($nc,$V,$F);}function
select_db($Nb){return(adminer()->database()==$Nb);}function
query($H,$jj=false){$J=parent::query($H,$jj);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$J;}function
warnings(){}function
copyFrom($R,array$L){$J=$this->pdo->pgsqlCopyFromArray($R,$L);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$J;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($H){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$H),$A)){$L=explode("\n",$A[2]);$this->affected_rows=count($L);return$this->copyFrom($A[1],$L);}return
parent::multi_query($H);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";var$operators=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL");var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f))return$f;$Ej=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$Ej)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$Ej);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f))$this->generated=array("STORED");$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$_c=$this->types['User types'][$m["type"]];return($_c?type_values($_c):"");}function
setUserTypes($ij){$this->types['User types']=array_flip($ij);}function
insertReturning($R){$_a=array_filter(fields($R),function($m){return$m['auto_increment'];});return(count($_a)==1?" RETURNING ".idf_escape(key($_a)):"");}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$rj=array();$Z=array();foreach($O
as$x=>$X){$rj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$rj)." WHERE ".implode(" AND ",$Z))&&connection()->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
slowQuery($H,$Li){$this->conn->query("SET statement_timeout = ".(1000*$Li));$this->conn->timeout=1000*$Li;return$H;}function
convertSearch($u,array$X,array$m){$Ii="char|text";if(strpos($X["op"],"LIKE")===false)$Ii
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$Ii~",$m["type"])?$u:"CAST($u AS text)");}function
quoteBinary($_h){return"'\\x".bin2hex($_h)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($B,$xe=false){$Qe=array("information_schema"=>"infoschema","pg_catalog"=>($xe?"view":"catalog"),);$_=$Qe[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($R){return
get_vals("SELECT relname FROM pg_class JOIN pg_inherits ON inhparent = oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 1");}function
inheritedTables($R){return
get_vals("SELECT relname FROM pg_inherits JOIN pg_class ON inhrelid = oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 1");}function
partitionsInfo($R){$K=connection()->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".driver()->tableOid($R))->fetch_assoc();if($K){$ya=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $K[partrelid] AND attnum IN (".str_replace(" ",", ",$K["partattrs"]).")");$Oa=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Oa[$K["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$ya)),);}return
array();}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
indexAlgorithms(array$ti){static$J=array();if(!$J)$J=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = 'btree' DESC, amname");return$J;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$Qa;if($Qa===null)$Qa=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$Qa;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($H,$Z,$z,$C=0,$Mh=" "){return" $H$Z".($z?$Mh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Mh="\n"){return(preg_match('~^INTO~',$H)?limit($H,$Z,1,0,$Mh):" $H".(is_view(table_status1($R))?$Z:$Mh."WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$Mh."LIMIT 1)"));}function
db_collation($j,$jb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$H="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$H
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$H
.="
ORDER BY 1";return
get_key_vals($H);}function
count_tables($i){$J=array();foreach($i
as$j){if(connection()->select_db($j))$J[$j]=count(tables_list());}return$J;}function
table_status($B=""){static$Fd;if($Fd===null)$Fd=get_val("SELECT 'pg_table_size'::regproc");$J=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Fd?",
	pg_table_size(oid) AS \"Data_length\",
	pg_indexes_size(oid) AS \"Index_length\"":"").",
	obj_description(oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples as \"Rows\",
	inhparent AS inherited,
	current_schema() AS nspname
FROM pg_class
LEFT JOIN pg_inherits ON inhrelid = oid
WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$J=array();$ra=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$A);list(,$U,$y,$K["length"],$ka,$va)=$A;$K["length"].=$va;$Ya=$U.$ka;if(isset($ra[$Ya])){$K["type"]=$ra[$Ya];$K["full_type"]=$K["type"].$y.$va;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$y.$ka.$va;}if(in_array($K['attidentity'],array('a','d')))$K['default']='GENERATED '.($K['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$K["generated"]=($K["attgenerated"]=="s"?"STORED":"");$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['attidentity']||preg_match('~^nextval\(~i',$K["default"])||preg_match('~^unique_rowid\(~',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(preg_match('~(.+)::[^,)]+(.*)~',$K["default"],$A))$K["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$J[$K["field"]]=$K;}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$wi=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $wi AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, (indpred IS NOT NULL)::int as indispartial, pg_am.amname as algorithm, pg_get_expr(pg_index.indpred, pg_index.indrelid, true) AS partial
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $wi
ORDER BY indisprimary DESC, indisunique DESC",$g)as$K){$nh=$K["relname"];$J[$nh]["type"]=($K["indispartial"]?"INDEX":($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX")));$J[$nh]["columns"]=array();$J[$nh]["descs"]=array();$J[$nh]["algorithm"]=$K["algorithm"];$J[$nh]["partial"]=$K["partial"];if($K["indkey"]){foreach(explode(" ",$K["indkey"])as$ee)$J[$nh]["columns"][]=$e[$ee];foreach(explode(" ",$K["indoption"])as$fe)$J[$nh]["descs"][]=(intval($fe)&1?'1':null);}$J[$nh]["lengths"]=array();}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$A)){$K['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$We)){$K['ns']=idf_unescape($We[2]);$K['table']=idf_unescape($We[4]);}$K['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$K['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$We)?$We[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$We)?$We[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
view($B){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")")));}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="information_schema";}function
error(){$J=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$A))$J=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($J);}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" ENCODING ".idf_escape($c):""));}function
drop_databases($i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($B,$c){connection()->close();return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$yc,$c,$_a,$E){$b=array();$ah=array();if($R!=""&&$R!=$B)$ah[]="ALTER TABLE ".table($R)." RENAME TO ".table($B);$Nh="";foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b[]="DROP $d";else{$Aj=$X[5];unset($X[5]);if($m[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$ah[]="ALTER TABLE ".table($B)." RENAME $d TO $X[0]";$b[]="ALTER $d TYPE$X[1]";$Oh=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) STORED~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($Oh).")":"DROP DEFAULT"));if(isset($X[6]))$Nh="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Oh)." OWNED BY ".idf_escape($R).".$X[0]";$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($m[0]!=""||$Aj!="")$ah[]="COMMENT ON COLUMN ".table($B).".$X[0] IS ".($Aj!=""?substr($Aj,9):"''");}}$b=array_merge($b,$jd);if($R==""){$P="";if($E){$eb=(connection()->flavor=='cockroach');$P=" PARTITION BY $E[partition_by]($E[partition])";if($E["partition_by"]=='HASH'){$zg=+$E["partitions"];for($s=0;$s<$zg;$s++)$ah[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $zg, REMAINDER $s)";}else{$Qg="MINVALUE";foreach($E["partition_names"]as$s=>$X){$Y=$E["partition_values"][$s];$vg=" VALUES ".($E["partition_by"]=='LIST'?"IN ($Y)":"FROM ($Qg) TO ($Y)");if($eb)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$vg";else$ah[]="CREATE TABLE ".idf_escape($B."_$X")." PARTITION OF ".idf_escape($B)." FOR$vg";$Qg=$Y;}$P
.=($eb?"\n)":"");}}array_unshift($ah,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");}elseif($b)array_unshift($ah,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($Nh)array_unshift($ah,$Nh);if($ob!==null)$ah[]="COMMENT ON TABLE ".table($B)." IS ".q($ob);foreach($ah
as$H){if(!queries($H))return
false;}return
true;}function
alter_indexes($R,$b){$h=array();$ic=array();$ah=array();foreach($b
as$X){if($X[0]!="INDEX")$h[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$ah[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($h)array_unshift($ah,"ALTER TABLE ".table($R).implode(",",$h));if($ic)array_unshift($ah,"DROP INDEX ".implode(", ",$ic));foreach($ah
as$H){if(!queries($H))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_views($Gj){return
drop_tables($Gj);}function
drop_tables($T){foreach($T
as$R){$P=table_status1($R);if(!queries("DROP ".strtoupper($P["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$Gj,$Di){foreach(array_merge($T,$Gj)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Di)))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$K)$e[]=$K["event_object_column"];$J=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$K){if($e&&$K["Event"]=="UPDATE")$K["Event"].=" OF";$K["Of"]=implode(", ",$e);if($J)$K["Event"].=" OR $J[Event]";$J=$K;}return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$K){$cj=trigger($K["trigger_name"],$R);$J[$cj["Trigger"]]=array($cj["Timing"],$cj["Event"]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$U){$L=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$J=idx($L,0,array());$J["returns"]=array("type"=>$J["type_udt_name"]);$J["fields"]=get_rows('SELECT parameter_name AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($B).'
ORDER BY ordinal_position');return$J;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($B,$K){$J=array();foreach($K["fields"]as$m){$y=$m["length"];$J[]=$m["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$J).")";}function
last_id($I){$K=(is_object($I)?$I->fetch_row():array());return($K?$K[0]:0);}function
explain($f,$H){return$f->query("EXPLAIN $H");}function
found_rows($S,$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$mh))return$mh[1];}function
types(){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".driver()->nsOid."
AND typtype IN ('b','d','e')
AND typelem = 0");}function
type_values($t){$Cc=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Cc?"'".implode("', '",array_map('addslashes',$Cc))."'":"");}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
get_val("SELECT current_schema()");}function
set_schema($Bh,$g=null){if(!$g)$g=connection();$J=$g->query("SET search_path TO ".idf_escape($Bh));driver()->setUserTypes(types());return$J;}function
foreign_keys_sql($R){$J="";$P=table_status1($R);$fd=foreign_keys($R);ksort($fd);foreach($fd
as$ed=>$dd)$J
.="ALTER TABLE ONLY ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." ADD CONSTRAINT ".idf_escape($ed)." $dd[definition] ".($dd['deferrable']?'DEFERRABLE':'NOT DEFERRABLE').";\n";return($J?"$J\n":$J);}function
create_sql($R,$_a,$ni){$sh=array();$Ph=array();$P=table_status1($R);if(is_view($P)){$Fj=view($R);return
rtrim("CREATE VIEW ".idf_escape($R)." AS $Fj[select]",";");}$n=fields($R);if(count($P)<2||empty($n))return
false;$J="CREATE TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." (\n    ";foreach($n
as$m){$tg=idf_escape($m['field']).' '.$m['full_type'].default_value($m).($m['null']?"":" NOT NULL");$sh[]=$tg;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$Ye)){$Oh=$Ye[1];$ci=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Oh)):"SELECT * FROM $Oh"),null,"-- "));$Ph[]=($ni=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Oh;\n":"")."CREATE SEQUENCE $Oh INCREMENT $ci[increment_by] MINVALUE $ci[min_value] MAXVALUE $ci[max_value]".($_a&&$ci['last_value']?" START ".($ci["last_value"]+1):"")." CACHE $ci[cache_value];";}}if(!empty($Ph))$J=implode("\n\n",$Ph)."\n\n$J";$G="";foreach(indexes($R)as$ce=>$v){if($v['type']=='PRIMARY'){$G=$ce;$sh[]="CONSTRAINT ".idf_escape($ce)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$ub=>$wb)$sh[]="CONSTRAINT ".idf_escape($ub)." CHECK $wb";$J
.=implode(",\n    ",$sh)."\n)";$vg=driver()->partitionsInfo($P['Name']);if($vg)$J
.="\nPARTITION BY $vg[partition_by]($vg[partition])";$J
.="\nWITH (oids = ".($P['Oid']?'true':'false').");";if($P['Comment'])$J
.="\n\nCOMMENT ON TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." IS ".q($P['Comment']).";";foreach($n
as$Xc=>$m){if($m['comment'])$J
.="\n\nCOMMENT ON COLUMN ".idf_escape($P['nspname']).".".idf_escape($P['Name']).".".idf_escape($Xc)." IS ".q($m['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($R).($G?" AND indexname != ".q($G):""),null,"-- ")as$K)$J
.="\n\n$K[indexdef];";return
rtrim($J,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
trigger_sql($R){$P=table_status1($R);$J="";foreach(triggers($R)as$bj=>$aj){$cj=trigger($bj,$P['Name']);$J
.="\nCREATE TRIGGER ".idf_escape($cj['Trigger'])." $cj[Timing] $cj[Event] ON ".idf_escape($P["nspname"]).".".idf_escape($P['Name'])." $cj[Type] $cj[Statement];;\n";}return$J;}function
use_sql($Nb){return"\connect ".idf_escape($Nb);}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Vc){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Vc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("oracle","Oracle (beta)");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($Dc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$this->link=@oci_new_connect($V,$F,$N,"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return$l["message"];}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($Nb){$this->_current_db=$Nb;return
true;}function
query($H,$jj=false){$I=oci_parse($this->link,$H);$this->error="";if(!$I){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
Result($I);$this->affected_rows=oci_num_rows($I);oci_free_statement($I);}return$J;}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$K[$x]=$X->load();}return$K;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->name=oci_field_name($this->result,$d);$J->type=oci_field_type($this->result,$d);$J->charsetnr=(preg_match("~raw|blob|bfile~",$J->type)?63:0);return$J;}function
__destruct(){oci_free_statement($this->result);}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach($N,$V,$F){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$V,$F);}function
select_db($Nb){$this->_current_db=$Nb;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$rj=array();$Z=array();foreach($O
as$x=>$X){$rj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$rj)." WHERE ".implode(" AND ",$Z))&&connection()->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
hasCStyleEscapes(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($H,$Z,$z,$C=0,$Mh=" "){return($C?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $H$Z) t WHERE rownum <= ".($z+$C).") WHERE rnum > $C":($z?" * FROM (SELECT $H$Z) WHERE rownum <= ".($z+$C):" $H$Z"));}function
limit1($R,$H,$Z,$Mh="\n"){return" $H$Z";}function
db_collation($j,$jb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;unset(connection()->_current_db);return$j;}function
where_owner($Og,$ng="owner"){if(!$_GET["ns"])return'';return"$Og$ng = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$ng=where_owner('');return"(SELECT $e FROM all_views WHERE ".($ng?:"rownum < 0").")";}function
tables_list(){$Fj=views_table("view_name");$ng=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$ng
UNION SELECT view_name, 'view' FROM $Fj
ORDER BY 1");}function
count_tables($i){$J=array();foreach($i
as$j)$J[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$J;}function
table_status($B=""){$J=array();$Fh=q($B);$j=get_current_db();$Fj=views_table("view_name");$ng=where_owner(" AND ");foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q($j).$ng.($B!=""?" AND table_name = $Fh":"")."
UNION SELECT view_name, 'view', 0, 0 FROM $Fj".($B!=""?" WHERE view_name = $Fh":"")."
ORDER BY 1")as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$J=array();$ng=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)."$ng ORDER BY column_id")as$K){$U=$K["DATA_TYPE"];$y="$K[DATA_PRECISION],$K[DATA_SCALE]";if($y==",")$y=$K["CHAR_COL_DECL_LENGTH"];$J[$K["COLUMN_NAME"]]=array("field"=>$K["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),);}return$J;}function
indexes($R,$g=null){$J=array();$ng=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)."$ng
ORDER BY ac.constraint_type, aic.column_position",$g)as$K){$ce=$K["INDEX_NAME"];$lb=$K["DATA_DEFAULT"];$lb=($lb?trim($lb,'"'):$K["COLUMN_NAME"]);$J[$ce]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$ce]["columns"][]=$lb;$J[$ce]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$ce]["descs"][]=($K["DESCEND"]&&$K["DESCEND"]=="DESC"?'1':null);}return$J;}function
view($B){$Fj=views_table("view_name, text");$L=get_rows('SELECT text "select" FROM '.$Fj.' WHERE view_name = '.q($B));return
reset($L);}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain($f,$H){$f->query("EXPLAIN PLAN FOR $H");return$f->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$yc,$c,$_a,$E){$b=$ic=array();$gg=($R?fields($R):array());foreach($n
as$m){$X=$m[1];if($X&&$m[0]!=""&&idf_escape($m[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($m[0])." TO $X[0]");$fg=$gg[$m[0]];if($X&&$fg){$Jf=process_field($fg,$fg);if($X[2]==$Jf[2])$X[2]="";}if($X)$b[]=($R!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$ic[]=idf_escape($m[0]);}if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$ic).")"))&&($R==$B||queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)));}function
alter_indexes($R,$b){$ic=array();$ah=array();foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$h=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($ah,"ALTER TABLE ".table($R).$h);}elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$ah[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($ic)array_unshift($ah,"DROP INDEX ".implode(", ",$ic));foreach($ah
as$H){if(!queries($H))return
false;}return
true;}function
foreign_keys($R){$J=array();$H="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($H)as$K)$J[$K['NAME']]=array("db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>array($K['SRC_COLUMN']),"target"=>array($K['DEST_COLUMN']),"on_delete"=>$K['ON_DELETE'],"on_update"=>null,);return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Gj){return
apply_queries("DROP VIEW",$Gj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id($I){return
0;}function
schemas(){$J=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($J?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($Dh,$g=null){if(!$g)$g=connection();return$g->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($Dh));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$J=array();$L=get_rows('SELECT * FROM v$instance');foreach(reset($L)as$x=>$X)$J[]=array($x,$X);return$J;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Vc){return
preg_match('~^(columns|database|drop_col|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$Vc);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach($N,$V,$F){$vb=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8");$ii=adminer()->connectSsl();if(isset($ii["Encrypt"]))$vb["Encrypt"]=$ii["Encrypt"];if(isset($ii["TrustServerCertificate"]))$vb["TrustServerCertificate"]=$ii["TrustServerCertificate"];$j=adminer()->database();if($j!="")$vb["Database"]=$j;$this->link=@sqlsrv_connect(preg_replace('~:~',',',$N),$vb);if($this->link){$ge=sqlsrv_server_info($this->link);$this->server_info=$ge['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){$kj=strlen($Q)!=strlen(utf8_decode($Q));return($kj?"N":"")."'".str_replace("'","''",$Q)."'";}function
select_db($Nb){return$this->query(use_sql($Nb));}function
query($H,$jj=false){$I=sqlsrv_query($this->link,$H);$this->error="";if(!$I){$this->get_error();return
false;}return$this->store_result($I);}function
multi_query($H){$this->result=sqlsrv_query($this->link,$H);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($I=null){if(!$I)$I=$this->result;if(!$I)return
false;if(sqlsrv_field_metadata($I))return
new
Result($I);$this->affected_rows=sqlsrv_rows_affected($I);return
true;}function
next_result(){return$this->result?!!sqlsrv_next_result($this->result):false;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'DateTime'))$K[$x]=$X->format("Y-m-d H:i:s");}return$K;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$J=new
\stdClass;$J->name=$m["Name"];$J->type=($m["Type"]==1?254:15);$J->charsetnr=0;return$J;}function
seek($C){for($s=0;$s<$C;$s++)sqlsrv_fetch($this->result);}function
__destruct(){sqlsrv_free_stmt($this->result);}}function
last_id($I){return
get_val("SELECT SCOPE_IDENTITY()");}function
explain($f,$H){$f->query("SET SHOWPLAN_ALL ON");$J=$f->query($H);$f->query("SET SHOWPLAN_ALL OFF");return$J;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($Nb){return$this->query(use_sql($Nb));}function
lastInsertId(){return$this->pdo->lastInsertId();}}function
last_id($I){return
connection()->lastInsertId();}function
explain($f,$H){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach($N,$V,$F){return$this->dsn("sqlsrv:Server=".str_replace(":",",",$N),$V,$F);}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach($N,$V,$F){return$this->dsn("dblib:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\d)~',';port=\1',$N)),$V,$F);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";static
function
connect($N,$V,$F){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$F);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),);}function
insertUpdate($R,array$L,array$G){$n=fields($R);$rj=array();$Z=array();$O=reset($L);$e="c".implode(", c",range(1,count($O)));$Pa=0;$me=array();foreach($O
as$x=>$X){$Pa++;$B=idf_unescape($x);if(!$n[$B]["auto_increment"])$me[$x]="c$Pa";if(isset($G[$B]))$Z[]="$x = c$Pa";else$rj[]="$x = c$Pa";}$Bj=array();foreach($L
as$O)$Bj[]="(".implode(", ",$O).")";if($Z){$Rd=queries("SET IDENTITY_INSERT ".table($R)." ON");$J=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$Bj)."\n) AS source ($e) ON ".implode(" AND ",$Z).($rj?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$rj):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Rd?$O:$me)).") VALUES (".($Rd?$e:implode(", ",$me)).");");if($Rd)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$J=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$Bj));return$J;}function
begin(){return
queries("BEGIN TRANSACTION");}function
tableHelp($B,$xe=false){$Qe=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$Qe[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($H,$Z,$z,$C=0,$Mh=" "){return($z?" TOP (".($z+$C).")":"")." $H$Z";}function
limit1($R,$H,$Z,$Mh="\n"){return
limit($H,$Z,1,0,$Mh);}function
db_collation($j,$jb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($i){$J=array();foreach($i
as$j){connection()->select_db($j);$J[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($B=""){$J=array();foreach(get_rows("SELECT ao.name AS Name, ao.type_desc AS Engine, (SELECT value FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$qb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$J=array();$ui=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($ui))as$K){$U=$K["type"];$y=(preg_match("~char|binary~",$U)?intval($K["max_length"])/($U[0]=='n'?2:1):($U=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=array("field"=>$K["name"],"full_type"=>$U.($y?"($y)":""),"type"=>$U,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$K["default"],$A)?str_replace("''","'",$A[1]):$K["default"]),"default_constraint"=>$K["default_constraint"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["is_primary_key"],"comment"=>$qb[$K["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($ui))as$K){$J[$K["name"]]["generated"]=($K["is_persisted"]?"PERSISTED":"VIRTUAL");$J[$K["name"]]["default"]=$K["definition"];}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$g)as$K){$B=$K["name"];$J[$B]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$B]["lengths"]=array();$J[$B]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$B]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$J=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$c)$J[preg_replace('~_.*~','',$c)][]=$c;return$J;}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$c)?" COLLATE $c":""));}function
drop_databases($i){return
queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($B,$c){if(preg_match('~^[a-z0-9_]+$~i',$c))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $c");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$B,$n,$jd,$ob,$yc,$c,$_a,$E){$b=array();$qb=array();$gg=fields($R);foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);$qb[$m[0]]=$X[5];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?substr($jd[$X[0]],16+strlen($X[0])):"");else{$k=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$fg=$gg[$m[0]];if(default_value($fg)!=$k){if($fg["default"]!==null)$b["DROP"][]=" ".idf_escape($fg["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($R=="")return
queries("CREATE TABLE ".table($B)." (".implode(",",(array)$b["ADD"])."\n)");if($R!=$B)queries("EXEC sp_rename ".q(table($R)).", ".q($B));if($jd)$b[""]=$jd;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$X)))return
false;}foreach($qb
as$x=>$X){$ob=substr($X,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $ob,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($B).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($R,$b){$v=array();$ic=array();foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$ic[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$ic)));}function
found_rows($S,$Z){}function
foreign_keys($R){$J=array();$Qf=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q(get_schema()))as$K){$p=&$J[$K["FK_NAME"]];$p["db"]=$K["PKTABLE_QUALIFIER"];$p["ns"]=$K["PKTABLE_OWNER"];$p["table"]=$K["PKTABLE_NAME"];$p["on_update"]=$Qf[$K["UPDATE_RULE"]];$p["on_delete"]=$Qf[$K["DELETE_RULE"]];$p["source"][]=$K["FKCOLUMN_NAME"];$p["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Gj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Gj)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables($T,$Gj,$Di){return
apply_queries("ALTER SCHEMA ".idf_escape($Di)." TRANSFER",array_merge($T,$Gj));}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$J=reset($L);if($J)$J["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$J["text"]);return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$K)$J[$K["name"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($Bh){$_GET["ns"]=$Bh;return
true;}function
create_sql($R,$_a,$ni){if(is_view(table_status1($R))){$Fj=view($R);return"CREATE VIEW ".table($R)." AS $Fj[select]";}$n=array();$G=false;foreach(fields($R)as$B=>$m){$X=process_field($m,$m);if($X[6])$G=true;$n[]=implode("",$X);}foreach(indexes($R)as$B=>$v){if(!$G||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$n[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$B=>$Wa)$n[]="CONSTRAINT ".idf_escape($B)." CHECK ($Wa)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($R){$n=array();foreach(foreign_keys($R)as$jd)$n[]=ltrim(format_foreign_key($jd));return($n?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($Nb){return"USE ".idf_escape($Nb);}function
trigger_sql($R){$J="";foreach(triggers($R)as$B=>$cj)$J
.=create_trigger(" ON ".table($R),trigger($B,$R)).";";return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Vc){return
preg_match('~^(check|comment|columns|database|drop_col|dump|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$Vc);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.3.0")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($hd=true){return
get_databases($hd);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){return
schemas();}function
queryTimeout(){return
2;}function
headers(){}function
csp(array$Gb){return$Gb;}function
head($Kb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$J=array();foreach(array("","-dark")as$qf){$o="adminer$qf.css";if(file_exists($o)){$Zc=file_get_contents($o);$J["$o?v=".crc32($Zc)]=($qf?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Zc)?'':'light'));}}return$J;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,"loginDriver(this);")),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>','<input name="auth[server]" value="'.h(SERVER).'" title="hostname[:port]" placeholder="localhost" autocapitalize="off">'),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("const authDriver = qs('#username').form['auth[driver]']; authDriver && authDriver.onchange();")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($B,$Hd,$Y){return$Hd.$Y."\n";}function
login($Se,$F){if($F=="")return
sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',target_blank());return
true;}function
tableName(array$ti){return
h($ti["Name"]);}function
fieldName(array$m,$Zf=0){$U=$m["full_type"];$ob=$m["comment"];return'<span title="'.h($U.($ob!=""?($U?": ":"").$ob:'')).'">'.h($m["field"]).'</span>';}function
selectLinks(array$ti,$O=""){$B=$ti["Name"];echo'<p class="links">';$Qe=array("select"=>'Select data');if(support("table")||support("indexes"))$Qe["table"]='Show structure';$xe=false;if(support("table")){$xe=is_view($ti);if($xe)$Qe["view"]='Alter view';else$Qe["create"]='Alter table';}if($O!==null)$Qe["edit"]='New item';foreach($Qe
as$x=>$X)echo" <a href='".h(ME)."$x=".urlencode($B).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($B,$xe)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$si){return
array();}function
backwardKeysPrint(array$Da,array$K){}function
selectQuery($H,$ji,$Tc=false){$J="</p>\n";if(!$Tc&&($Jj=driver()->warnings())){$t="warnings";$J=", <a href='#$t'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."$J<div id='$t' class='hidden'>\n$Jj</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$H))."</code> <span class='time'>(".format_time($ji).")</span>".(support("sql")?" <a href='".h(ME)."sql=".urlencode($H)."'>".'Edit'."</a>":"").$J;}function
sqlCommandQuery($H){return
shorten_utf8(trim($H),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$L,array$kd){return$L;}function
selectLink($X,array$m){}function
selectVal($X,$_,array$m,$jg){$J=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$X</code>":(preg_match('~json~',$m["type"])?"<code class='jush-js'>$X</code>":$X)));if(preg_match('~blob|bytea|raw|file~',$m["type"])&&!is_utf8($X))$J="<i>".lang_format(array('%d byte','%d bytes'),strlen($jg))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$J</a>":$J);}function
editVal($X,array$m){return$X;}function
config(){return
array();}function
tableStructurePrint(array$n,$ti=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."</thead>\n";$mi=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$U=h($m["full_type"]);$c=h($m["collation"]);echo"<td><span title='$c'>".(in_array($U,(array)$mi['User types'])?"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>":$U.($c&&isset($ti["Collation"])&&$c!=$ti["Collation"]?" $c":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":"");$k=h($m["default"]);echo(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>$k</code>":$k)."</b>]</span>":""),(support("comment")?"<td>".h($m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$ti){$ug=false;foreach($w
as$B=>$v)$ug|=!!$v["partial"];echo"<table>\n";$Sb=first(driver()->indexAlgorithms($ti));foreach($w
as$B=>$v){ksort($v["columns"]);$Sg=array();foreach($v["columns"]as$x=>$X)$Sg[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".$v["lengths"][$x].")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>$v[type]".($Sb&&$v['algorithm']!=$Sb?" ($v[algorithm])":""),"<td>".implode(", ",$Sg);if($ug)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",'Select',$M);$s=0;$M[""]=array();foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]'",$e,$X["col"],($x!==""?"selectFieldChange":"selectAddRow"));echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]).on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'",1).script("qsl('select').onchange = function () { helpClose();".($x!==""?"":" qsl('select, input', this.parentNode).onchange();")." };","")."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."'>",script("qsl('input').oninput = selectFieldChange;",""),checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"),"</div>\n";}$Ta="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],adminer()->operators())))echo"<div>".select_input(" name='where[$s][col]'",$e,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".'anywhere'.")"),html_select("where[$s][op]",adminer()->operators(),$X["op"],$Ta),"<input type='search' name='where[$s][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $Ta }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});",""),"</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$Zf,array$e,array$w){print_fieldset("sort",'Sort',$Zf);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".select_input(" name='order[$s]'",$e,$X,"selectFieldChange"),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]'",$e,"","selectAddRow"),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".intval($z)."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
selectLengthPrint($Ji){if($Ji!==null)echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Ji)."'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$Jb=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$Jb)$e[$Jb]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$vc,array$e){}function
selectColumnsProcess(array$e,array$w){$M=array();$wd=array();foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$wd[]=$M[$x];}}return
array($M,$wd);}function
selectSearchProcess(array$n,array$w){$J=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$J[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($_GET["fulltext"][$s]).(isset($_GET["boolean"][$s])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$x=>$X){$hb=$X["col"];if("$hb$X[val]"!=""&&in_array($X["op"],adminer()->operators())){$sb=array();foreach(($hb!=""?array($hb=>$n[$hb]):$n)as$B=>$m){$Og="";$rb=" $X[op]";if(preg_match('~IN$~',$X["op"])){$Wd=process_length($X["val"]);$rb
.=" ".($Wd!=""?$Wd:"(NULL)");}elseif($X["op"]=="SQL")$rb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$A))$rb=" $A[1] ".adminer()->processInput($m,"%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$Og="$X[op](".q($X["val"]).", ";$rb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$rb
.=" ".adminer()->processInput($m,$X["val"]);if($hb!=""||(isset($m["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$X["op"])?',':'').']+$~',$X["val"])||!preg_match('~'.number_type().'|bit~',$m["type"]))&&(!preg_match("~[\x80-\xFF]~",$X["val"])||preg_match('~char|text|enum|set~',$m["type"]))&&(!preg_match('~date|timestamp~',$m["type"])||preg_match('~^\d+-\d+-\d+~',$X["val"]))))$sb[]=$Og.driver()->convertSearch(idf_escape($B),$X,$m).$rb;}$J[]=(count($sb)==1?$sb[0]:($sb?"(".implode(" OR ",$sb).")":"1 = 0"));}}return$J;}function
selectOrderProcess(array$n,array$w){$J=array();foreach((array)$_GET["order"]as$x=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC":"");}return$J;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$kd){return
false;}function
selectQueryBuild(array$M,array$Z,array$wd,array$Zf,$z,$D){return"";}function
messageQuery($H,$Ki,$Tc=false){restart_session();$Jd=&get_session("queries");if(!idx($Jd,$_GET["db"]))$Jd[$_GET["db"]]=array();if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\nâ€¦";$Jd[$_GET["db"]][]=array($H,time(),$Ki);$fi="sql-".count($Jd[$_GET["db"]]);$J="<a href='#$fi' class='toggle'>".'SQL command'."</a>\n";if(!$Tc&&($Jj=driver()->warnings())){$t="warnings-".count($Jd[$_GET["db"]]);$J="<a href='#$t' class='toggle'>".'Warnings'."</a>, $J<div id='$t' class='hidden'>\n$Jj</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $J<div id='$fi' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($H,1000)."</code></pre>".($Ki?" <span class='time'>($Ki)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Jd[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
editRowPrint($R,array$n,$K,$rj){}function
editFunctions(array$m){$J=($m["null"]?"NULL/":"");$rj=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$rd){if(!$x||(!isset($_GET["call"])&&$rj)){foreach($rd
as$Cg=>$X){if(!$Cg||preg_match("~$Cg~",$m["type"]))$J
.="/$X";}}if($x&&$rd&&!preg_match('~set|blob|bytea|raw|file|bool~',$m["type"]))$J
.="/SQL";}if($m["auto_increment"]&&!$rj)$J='Auto Increment';return
explode("/",$J);}function
editInput($R,array$m,$ya,$Y){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$ya value='-1' checked><i>".'original'."</i></label> ":"").($m["null"]?"<label><input type='radio'$ya value=''".($Y!==null||isset($_GET["select"])?"":" checked")."><i>NULL</i></label> ":"").enum_input("radio",$ya,$m,$Y,$Y===0?0:null);return"";}function
editHint($R,array$m,$Y){return"";}function
processInput(array$m,$Y,$r=""){if($r=="SQL")return$Y;$B=$m["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$J="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$J=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$J=idf_escape($B)." $r $J";elseif(preg_match('~^[+-] interval$~',$r))$J=idf_escape($B)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$J="$r(".idf_escape($B).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$J="$r($J)";return
unconvert_field($m,$J);}function
dumpOutput(){$J=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$J['gz']='gzip';return$J;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($j){}function
dumpTable($R,$ni,$xe=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($ni)dump_csv(array_keys(fields($R)));}else{if($xe==2){$n=array();foreach(fields($R)as$B=>$m)$n[]=idf_escape($B)." $m[full_type]";$h="CREATE TABLE ".table($R)." (".implode(", ",$n).")";}else$h=create_sql($R,$_POST["auto_increment"],$ni);set_utf8mb4($h);if($ni&&$h){if($ni=="DROP+CREATE"||$xe==1)echo"DROP ".($xe==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($xe==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($R,$ni,$H){if($ni){$af=(JUSH=="sqlite"?0:1048576);$n=array();$Sd=false;if($_POST["format"]=="sql"){if($ni=="TRUNCATE+INSERT")echo
truncate_sql($R).";\n";$n=fields($R);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Sd=true;break;}}}}$I=connection()->query($H,1);if($I){$me="";$Na="";$Be=array();$sd=array();$pi="";$Wc=($R!=''?'fetch_assoc':'fetch_row');$Cb=0;while($K=$I->$Wc()){if(!$Be){$Bj=array();foreach($K
as$X){$m=$I->fetch_field();if(idx($n[$m->name],'generated')){$sd[$m->name]=true;continue;}$Be[]=$m->name;$x=idf_escape($m->name);$Bj[]="$x = VALUES($x)";}$pi=($ni=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Bj):"").";\n";}if($_POST["format"]!="sql"){if($ni=="table"){dump_csv($Be);$ni="INSERT";}dump_csv($K);}else{if(!$me)$me="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$Be)).") VALUES";foreach($K
as$x=>$X){if($sd[$x]){unset($K[$x]);continue;}$m=$n[$x];$K[$x]=($X!==null?unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($X)?$X:q(($X===false?0:$X))):"NULL");}$_h=($af?"\n":" ")."(".implode(",\t",$K).")";if(!$Na)$Na=$me.$_h;elseif(JUSH=='mssql'?$Cb%1000!=0:strlen($Na)+4+strlen($_h)+strlen($pi)<$af)$Na
.=",$_h";else{echo$Na.$pi;$Na=$me.$_h;}}$Cb++;}if($Na)echo$Na.$pi;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Sd)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Qd){return
friendly_url($Qd!=""?$Qd:(SERVER!=""?SERVER:"localhost"));}function
dumpHeaders($Qd,$sf=false){$mg=$_POST["output"];$Oc=(preg_match('~sql~',$_POST["format"])?"sql":($sf?"tar":"csv"));header("Content-Type: ".($mg=="gz"?"application/x-gzip":($Oc=="tar"?"application/x-tar":($Oc=="sql"||$mg!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($mg=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$Oc;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");return
true;}function
navigation($pf){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$_f=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$_f)<0?h($_f):"")."</a>","</span></h1>\n";if($pf=="auth"){$mg="";foreach((array)$_SESSION["pwds"]as$Dj=>$Rh){foreach($Rh
as$N=>$zj){$B=h(get_setting("vendor-$Dj-$N")?:get_driver($Dj));foreach($zj
as$V=>$F){if($F!==null){$Qb=$_SESSION["db"][$Dj][$N][$V];foreach(($Qb?array_keys($Qb):array(""))as$j)$mg
.="<li><a href='".h(auth_url($Dj,$N,$V,$j))."'>($B) ".h($V.($N!=""?"@".adminer()->serverName($N):"").($j!=""?" - $j":""))."</a>\n";}}}}if($mg)echo"<ul id='logins'>\n$mg</ul>\n".script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");}else{$T=array();if($_GET["ns"]!==""&&!$pf&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($pf);$ia=array();if(DB==""||!$pf){if(support("sql")){$ia[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ia[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ia[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Xd=$_GET["ns"]!==""&&!$pf&&DB!="";if($Xd)$ia[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";echo($ia?"<p class='links'>\n".implode("\n",$ia)."\n":"");if($Xd){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=5.3.0",true);if(support("sql")){echo"<script".nonce().">\n";if($T){$Qe=array();foreach($T
as$R=>$U)$Qe[]=preg_quote($R,'/');echo"var jushLinks = { ".JUSH.": [ '".js_escape(ME).(support("table")?"table=":"select=")."\$&', /\\b(".implode("|",$Qe).")\\b/g ] };\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$_i=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$n){foreach($n
as$m)$_i[$R][]=$m["field"];}echo"addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape("")."', ".json_encode($_i)."); });\n";}}echo"</script>\n";}echo
script("syntaxHighlighting('".preg_replace('~^(\d\.?\d).*~s','\1',connection()->server_info)."', '".connection()->flavor."');");}function
databasesPrint($pf){$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$Ob=script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB).$Ob:"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($pf!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"])."$Ob</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
tablesPrint(array$T){echo"<ul id='tables'>".script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$R=>$P){$R="$R";$B=adminer()->tableName($P);if($B!=""&&!$P["inherited"])echo'<li><a href="'.h(ME).'select='.urlencode($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.urlencode($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$error='';private$hooks=array();function
__construct($Hg){if($Hg===null){$Hg=array();$Ha="adminer-plugins";if(is_dir($Ha)){foreach(glob("$Ha/*.php")as$o)$Yd=include_once"./$o";}$Id=" href='https://www.adminer.org/plugins/#use'".target_blank();if(file_exists("$Ha.php")){$Yd=include_once"./$Ha.php";if(is_array($Yd)){foreach($Yd
as$Gg)$Hg[get_class($Gg)]=$Gg;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Ha.php</b>",$Id)."<br>";}foreach(get_declared_classes()as$db){if(!$Hg[$db]&&preg_match('~^Adminer\w~i',$db)){$kh=new
\ReflectionClass($db);$xb=$kh->getConstructor();if($xb&&$xb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$Id,"<b>$db</b>","<b>$Ha.php</b>")."<br>";else$Hg[$db]=new$db;}}}$this->plugins=$Hg;$la=new
Adminer;$Hg[]=$la;$kh=new
\ReflectionObject($la);foreach($kh->getMethods()as$nf){foreach($Hg
as$Gg){$B=$nf->getName();if(method_exists($Gg,$B))$this->hooks[$B][]=$Gg;}}}function
__call($B,array$rg){$ua=array();foreach($rg
as$x=>$X)$ua[]=&$rg[$x];$J=null;foreach($this->hooks[$B]as$Gg){$Y=call_user_func_array(array($Gg,$B),$ua);if($Y!==null){if(!self::$append[$B])return$Y;$J=$Y+(array)$J;}}return$J;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$Ff=null){$ua=func_get_args();$ua[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$ua);}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\MySQLi{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Md,$Ig)=explode(":",$N,2);$ii=adminer()->connectSsl();if($ii)$this->ssl_set($ii['key'],$ii['cert'],$ii['ca'],'','');$J=@$this->real_connect(($N!=""?$Md:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($Ig)?intval($Ig):ini_get("mysqli.default_port")),(is_numeric($Ig)?null:$Ig),($ii?($ii['verify']!==false?2048:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,false);return($J?'':$this->error);}function
set_charset($Va){if(parent::set_charset($Va))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Va");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$F){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),("$N$V"!=""?$V:ini_get("mysql.default_user")),("$N$V$F"!=""?$F:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($Va){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Va,$this->link))return
true;mysql_set_charset('utf8',$this->link);}return$this->query("SET NAMES $Va");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Nb){return
mysql_select_db($Nb,$this->link);}function
query($H,$jj=false){$I=@($jj?mysql_unbuffered_query($H,$this->link):mysql_query($H,$this->link));$this->error="";if(!$I){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($I===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($I);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=mysql_num_rows($I);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$J=mysql_fetch_field($this->result,$this->offset++);$J->orgtable=$J->table;$J->charsetnr=($J->blob?63:0);return$J;}function
__destruct(){mysql_free_result($this->result);}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$F){$Xf=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$ii=adminer()->connectSsl();if($ii){if($ii['key'])$Xf[\PDO::MYSQL_ATTR_SSL_KEY]=$ii['key'];if($ii['cert'])$Xf[\PDO::MYSQL_ATTR_SSL_CERT]=$ii['cert'];if($ii['ca'])$Xf[\PDO::MYSQL_ATTR_SSL_CA]=$ii['ca'];if(isset($ii['verify']))$Xf[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$ii['verify'];}return$this->dsn("mysql:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\d)~',';port=\1',$N)),$V,$F,$Xf);}function
set_charset($Va){return$this->query("SET NAMES $Va");}function
select_db($Nb){return$this->query("USE ".idf_escape($Nb));}function
query($H,$jj=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$jj);return
parent::query($H,$jj);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($_h=iconv("windows-1250","utf-8",$f))>strlen($f))$f=$_h;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version(9,'',$f)){$this->types['Numbers']["vector"]=16383;$this->insertFunctions['vector']='string_to_vector';}if(min_version(5.1,'',$f))$this->partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):(preg_match("~geometry|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":"")));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$L,array$G){$e=array_keys(reset($L));$Og="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$Bj=array();foreach($e
as$x)$Bj[$x]="$x = VALUES($x)";$pi="\nON DUPLICATE KEY UPDATE ".implode(", ",$Bj);$Bj=array();$y=0;foreach($L
as$O){$Y="(".implode(", ",$O).")";if($Bj&&(strlen($Og)+$y+strlen($Y)+strlen($pi)>1e6)){if(!queries($Og.implode(",\n",$Bj).$pi))return
false;$Bj=array();$y=0;}$Bj[]=$Y;$y+=strlen($Y)+2;}return
queries($Og.implode(",\n",$Bj).$pi);}function
slowQuery($H,$Li){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$Li FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($Li*1000).") */ $A[2]";}}function
convertSearch($u,array$X,array$m){return(preg_match('~char|text|enum|set~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u);}function
warnings(){$I=$this->conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();print_select_result($I);return
ob_get_clean();}}function
tableHelp($B,$xe=false){$Ue=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower("information-schema-".($Ue?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="mysql")return($Ue?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($R){$pd="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$I=connection()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $pd ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=array();list($J["partition_by"],$J["partition"],$J["partitions"])=$I->fetch_row();$zg=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $pd AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$J["partition_names"]=array_keys($zg);$J["partition_values"]=array_values($zg);return$J;}function
hasCStyleEscapes(){static$Qa;if($Qa===null){$gi=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Qa=(strpos($gi,'NO_BACKSLASH_ESCAPES')===false);}return$Qa;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
indexAlgorithms(array$ti){return(preg_match('~^(MEMORY|NDB)$~',$ti["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($hd){$J=get_session("dbs");if($J===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$J=($hd?slow_query($H):get_vals($H));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($H,$Z,$z,$C=0,$Mh=" "){return" $H$Z".($z?$Mh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Mh="\n"){return
limit($H,$Z,1,0,$Mh);}function
db_collation($j,array$jb){$J=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$J=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$J=$jb[$A[1]][-1];return$J;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$J=array();foreach($i
as$j)$J[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$J;}function
table_status($B="",$Uc=false){$J=array();foreach(get_rows($Uc?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name"):"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($B!="")$K["Name"]=$B;$J[$K["Name"]]=$K;}return$J;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
fields($R){$Ue=(connection()->flavor=='maria');$J=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$K){$m=$K["COLUMN_NAME"];$U=$K["COLUMN_TYPE"];$td=$K["GENERATION_EXPRESSION"];$Rc=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Rc,$sd);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$Xe);$k=$K["COLUMN_DEFAULT"];if($k!=""){$we=preg_match('~text|json~',$Xe[1]);if(!$Ue&&$we)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($Ue||$we){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$Ue&&preg_match('~binary~',$Xe[1])&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$J[$m]=array("field"=>$m,"full_type"=>$U,"type"=>$Xe[1],"length"=>$Xe[2],"unsigned"=>ltrim($Xe[3].$Xe[4]),"default"=>($sd?($Ue?$td:stripslashes($td)):$k),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Rc=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Rc,$A)?$A[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$K[PRIVILEGES],where,order")),"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($sd[1]=="PERSISTENT"?"STORED":$sd[1]),);}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$g)as$K){$B=$K["Key_name"];$J[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?($K["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$J[$B]["columns"][]=$K["Column_name"];$J[$B]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$B]["descs"][]=null;$J[$B]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($R){static$Cg='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=array();$Db=get_val("SHOW CREATE TABLE ".table($R),1);if($Db){preg_match_all("~CONSTRAINT ($Cg) FOREIGN KEY ?\\(((?:$Cg,? ?)+)\\) REFERENCES ($Cg)(?:\\.($Cg))? \\(((?:$Cg,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Db,$Ye,PREG_SET_ORDER);foreach($Ye
as$A){preg_match_all("~$Cg~",$A[2],$ai);preg_match_all("~$Cg~",$A[5],$Di);$J[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$ai[0]),"target"=>array_map('Adminer\idf_unescape',$Di[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$x=>$X)sort($J[$x]);return$J;}function
information_schema($j){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" COLLATE ".q($c):""));}function
drop_databases(array$i){$J=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($B,$c){$J=false;if(create_database($B,$c)){$T=array();$Gj=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$Gj[]=$R;else$T[]=$R;}$J=(!$T&&!$Gj)||move_tables($T,$Gj,$B);drop_databases($J?array(DB):array());}return$J;}function
auto_increment(){$Aa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Aa="";break;}if($v["type"]=="PRIMARY")$Aa=" UNIQUE";}}return" AUTO_INCREMENT$Aa";}function
alter_table($R,$B,array$n,array$jd,$ob,$yc,$c,$_a,$E){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($R!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($R!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$jd);$P=($ob!==null?" COMMENT=".q($ob):"").($yc?" ENGINE=".q($yc):"").($c?" COLLATE ".q($c):"").($_a!=""?" AUTO_INCREMENT=$_a":"");if($E){$zg=array();if($E["partition_by"]=='RANGE'||$E["partition_by"]=='LIST'){foreach($E["partition_names"]as$x=>$X){$Y=$E["partition_values"][$x];$zg[]="\n  PARTITION ".idf_escape($X)." VALUES ".($E["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $E[partition_by]($E[partition])";if($zg)$P
.=" (".implode(",",$zg)."\n)";elseif($E["partitions"])$P
.=" PARTITIONS ".(+$E["partitions"]);}elseif($E===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");if($R!=$B)$b[]="RENAME TO ".table($B);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$Ua=array();foreach($b
as$X)$Ua[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$Ua));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$Gj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Gj)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$Gj,$Di){$oh=array();foreach($T
as$R)$oh[]=table($R)." TO ".idf_escape($Di).".".table($R);if(!$oh||queries("RENAME TABLE ".implode(", ",$oh))){$Wb=array();foreach($Gj
as$R)$Wb[table($R)]=view($R);connection()->select_db($Di);$j=idf_escape(DB);foreach($Wb
as$B=>$Fj){if(!queries("CREATE VIEW $B AS ".str_replace(" $j."," ",$Fj["select"]))||!queries("DROP VIEW $j.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$Gj,$Di){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$B=($Di==DB?table("copy_$R"):idf_escape($Di).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($R))||!queries("INSERT INTO $B SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K){$cj=$K["Trigger"];if(!queries("CREATE TRIGGER ".($Di==DB?idf_escape("copy_$cj"):idf_escape($Di).".".idf_escape($cj))." $K[Timing] $K[Event] ON $B FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($Gj
as$R){$B=($Di==DB?table("copy_$R"):idf_escape($Di).".".table($R));$Fj=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Fj[select]"))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$U){$ra=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$bi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$_c=driver()->enumLength;$hj="((".implode("|",array_merge(array_keys(driver()->types()),$ra)).")\\b(?:\\s*\\(((?:[^'\")]|$_c)++)\\))?"."\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";$Cg="$bi*(".($U=="FUNCTION"?"":driver()->inout).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$hj";$h=get_val("SHOW CREATE $U ".idf_escape($B),2);preg_match("~\\(((?:$Cg\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$hj\\s+":"")."(.*)~is",$h,$A);$n=array();preg_match_all("~$Cg\\s*,?~is",$A[1],$Ye,PREG_SET_ORDER);foreach($Ye
as$qg)$n[]=array("field"=>str_replace("``","`",$qg[2]).$qg[3],"type"=>strtolower($qg[5]),"length"=>preg_replace_callback("~$_c~s",'Adminer\normalize_enum',$qg[6]),"unsigned"=>strtolower(preg_replace('~\s+~',' ',trim("$qg[8] $qg[7]"))),"null"=>true,"full_type"=>$qg[4],"inout"=>strtoupper($qg[1]),"collation"=>strtolower($qg[9]),);return
array("fields"=>$n,"comment"=>get_val("SELECT ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = ".q($B)),)+($U!="FUNCTION"?array("definition"=>$A[11]):array("returns"=>array("type"=>$A[12],"length"=>$A[13],"unsigned"=>$A[15],"collation"=>$A[16]),"definition"=>$A[17],"language"=>"SQL",));}function
routines(){return
get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($B,array$K){return
idf_escape($B);}function
last_id($I){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$H){return$f->query("EXPLAIN ".(min_version(5.1)&&!min_version(5.7)?"PARTITIONS ":"").$H);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$_a,$ni){$J=get_val("SHOW CREATE TABLE ".table($R),1);if(!$_a)$J=preg_replace('~ AUTO_INCREMENT=\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Nb){return"USE ".idf_escape($Nb);}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){if(preg_match("~binary~",$m["type"]))return"HEX(".idf_escape($m["field"]).")";if($m["type"]=="bit")return"BIN(".idf_escape($m["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$J){if(preg_match("~binary~",$m["type"]))$J="UNHEX($J)";if($m["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"])){$Og=(min_version(8)?"ST_":"");$J=$Og."GeomFromText($J, $Og"."SRID($m[field]))";}return$J;}function
support($Vc){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(5.1)?'|event':'').(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').')$~',$Vc);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types(){return
array();}function
type_values($t){return"";}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Bh,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',$_GET[DRIVER]);define('Adminer\DB',$_GET["db"]);define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));function
page_header($Ni,$l="",$Ma=array(),$Oi=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$Pi=$Ni.($Oi!=""?": $Oi":"");$Qi=strip_tags($Pi.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$Qi,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=5.3.0"),'">
';$Hb=adminer()->css();if(is_int(key($Hb)))$Hb=array_fill_keys($Hb,'light');$Ed=in_array('light',$Hb)||in_array('',$Hb);$Cd=in_array('dark',$Hb)||in_array('',$Hb);$Kb=($Ed?($Cd?null:false):($Cd?:null));$gf=" media='(prefers-color-scheme: dark)'";if($Kb!==false)echo"<link rel='stylesheet'".($Kb?"":$gf)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=5.3.0")."'>\n";echo"<meta name='color-scheme' content='".($Kb===null?"light dark":($Kb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=5.3.0");if(adminer()->head($Kb))echo"<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.3.0")."'>\n";foreach($Hb
as$tj=>$qf){$ya=($qf=='dark'&&!$Kb?$gf:($qf=='light'&&$Cd?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$ya href='".h($tj)."'>\n";}echo"\n<body class='".'ltr'." nojs";adminer()->bodyClass();echo"'>\n";$o=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($o)&&filemtime($o)+86400>time()){$Ej=unserialize(file_get_contents($o));$Yg="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($Ej["version"],base64_decode($Ej["signature"]),$Yg)==1)$_COOKIE["adminer_version"]=$Ej["version"];}echo
script("mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick".(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '".VERSION."', '".js_escape(ME)."', '".get_token()."')")."});
document.body.classList.replace('nojs', 'js');
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon("move","","menu","")."</span>".script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");if($Ma!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> Â» ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ma===false)echo"$N\n";else{echo"<a href='".h($_)."' accesskey='1' title='Alt+Shift+1'>$N</a> Â» ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ma)))echo'<a href="'.h($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> Â» ';if(is_array($Ma)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> Â» ';foreach($Ma
as$x=>$X){$Yb=(is_array($X)?$X[1]:h($X));if($Yb!="")echo"<a href='".h(ME."$x=").urlencode(is_array($X)?$X[0]:$X)."'>$Yb</a> Â» ";}}echo"$Ni\n";}}echo"<h2>$Pi</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Gb){$Gd=array();foreach($Gb
as$x=>$X)$Gd[]="$x $X";header("Content-Security-Policy: ".implode("; ",$Gd));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$Bf;if(!$Bf)$Bf=base64_encode(rand_string());return$Bf;}function
page_messages($l){$sj=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$mf=idx($_SESSION["messages"],$sj);if($mf){echo"<div class='message'>".implode("</div>\n<div class='message'>",$mf)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$sj]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($pf=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($pf);echo"</div>\n";if($pf!="auth")echo'<form action="" method="post">
<p class="logout">
<span>',h($_GET["username"])."\n",'</span>
<input type="submit" name="logout" value="Logout" id="logout">
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($uf){while($uf>=2147483648)$uf-=4294967296;while($uf<=-2147483649)$uf+=4294967296;return(int)$uf;}function
long2str(array$W,$Ij){$_h='';foreach($W
as$X)$_h
.=pack('V',$X);if($Ij)return
substr($_h,0,end($W));return$_h;}function
str2long($_h,$Ij){$W=array_values(unpack('V*',str_pad($_h,4*ceil(strlen($_h)/4),"\0")));if($Ij)$W[]=strlen($_h);return$W;}function
xxtea_mx($Pj,$Oj,$qi,$_e){return
int32((($Pj>>5&0x7FFFFFF)^$Oj<<2)+(($Oj>>3&0x1FFFFFFF)^$Pj<<4))^int32(($qi^$Oj)+($_e^$Pj));}function
encrypt_string($li,$x){if($li=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($li,true);$uf=count($W)-1;$Pj=$W[$uf];$Oj=$W[0];$Zg=floor(6+52/($uf+1));$qi=0;while($Zg-->0){$qi=int32($qi+0x9E3779B9);$pc=$qi>>2&3;for($og=0;$og<$uf;$og++){$Oj=$W[$og+1];$tf=xxtea_mx($Pj,$Oj,$qi,$x[$og&3^$pc]);$Pj=int32($W[$og]+$tf);$W[$og]=$Pj;}$Oj=$W[0];$tf=xxtea_mx($Pj,$Oj,$qi,$x[$og&3^$pc]);$Pj=int32($W[$uf]+$tf);$W[$uf]=$Pj;}return
long2str($W,false);}function
decrypt_string($li,$x){if($li=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($li,false);$uf=count($W)-1;$Pj=$W[$uf];$Oj=$W[0];$Zg=floor(6+52/($uf+1));$qi=int32($Zg*0x9E3779B9);while($qi){$pc=$qi>>2&3;for($og=$uf;$og>0;$og--){$Pj=$W[$og-1];$tf=xxtea_mx($Pj,$Oj,$qi,$x[$og&3^$pc]);$Oj=int32($W[$og]-$tf);$W[$og]=$Oj;}$Pj=$W[$uf];$tf=xxtea_mx($Pj,$Oj,$qi,$x[$og&3^$pc]);$Oj=int32($W[0]-$tf);$W[0]=$Oj;$qi=int32($qi-0x9E3779B9);}return
long2str($W,true);}$Eg=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$Eg[$x]=$X;}}function
add_invalid_login(){$Fa=get_temp_dir()."/adminer.invalid";foreach(glob("$Fa*")?:array($Fa)as$o){$q=file_open_lock($o);if($q)break;}if(!$q)$q=file_open_lock("$Fa-".rand_string());if(!$q)return;$re=unserialize(stream_get_contents($q));$Ki=time();if($re){foreach($re
as$se=>$X){if($X[0]<$Ki)unset($re[$se]);}}$qe=&$re[adminer()->bruteForceKey()];if(!$qe)$qe=array($Ki+30*60,0);$qe[1]++;file_write_unlock($q,serialize($re));}function
check_invalid_login(array&$Eg){$re=array();foreach(glob(get_temp_dir()."/adminer.invalid*")as$o){$q=file_open_lock($o);if($q){$re=unserialize(stream_get_contents($q));file_unlock($q);break;}}$qe=idx($re,adminer()->bruteForceKey(),array());$Af=($qe[1]>29?$qe[0]-time():0);if($Af>0)auth_error(lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($Af/60)),$Eg);}$za=$_POST["auth"];if($za){session_regenerate_id();$Dj=$za["driver"];$N=$za["server"];$V=$za["username"];$F=(string)$za["password"];$j=$za["db"];set_password($Dj,$N,$V,$F);$_SESSION["db"][$Dj][$N][$V][$j]=true;if($za["permanent"]){$x=implode("-",array_map('base64_encode',array($Dj,$N,$V,$j)));$Tg=adminer()->permanentLogin(true);$Eg[$x]="$x:".base64_encode($Tg?encrypt_string($F,$Tg):"");cookie("adminer_permanent",implode(" ",$Eg));}if(count($_POST)==1||DRIVER!=$Dj||SERVER!=$N||$_GET["username"]!==$V||DB!=$j)redirect(auth_url($Dj,$N,$V,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Eg);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($Eg&&!$_SESSION["pwds"]){session_regenerate_id();$Tg=adminer()->permanentLogin();foreach($Eg
as$x=>$X){list(,$cb)=explode(":",$X);list($Dj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));set_password($Dj,$N,$V,decrypt_string(base64_decode($cb),$Tg));$_SESSION["db"][$Dj][$N][$V][$j]=true;}}function
unset_permanent(array&$Eg){foreach($Eg
as$x=>$X){list($Dj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));if($Dj==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$j==DB)unset($Eg[$x]);}cookie("adminer_permanent",implode(" ",$Eg));}function
auth_error($l,array&$Eg){$Sh=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Sh]||$_GET[$Sh])&&!$_SESSION["token"])$l='Session expired, please login again.';else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent($Eg);}}if(!$_COOKIE[$Sh]&&$_GET[$Sh]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$rg=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$rg["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Eg);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){list($Md,$Ig)=explode(":",SERVER,2);if(preg_match('~^\s*([-+]?\d+)~',$Ig,$A)&&($A[1]<1024||$A[1]>65535))auth_error('Connecting to privileged ports is not allowed.',$Eg);check_invalid_login($Eg);$Fb=adminer()->credentials();$f=Driver::connect($Fb[0],$Fb[1],$Fb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Se=null;if(!is_object($f)||($Se=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($Se)?$Se:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':'');auth_error($l,$Eg);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($za&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){$je="max_input_vars";$ef=ini_get($je);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$x){$X=ini_get($x);if($X&&(!$ef||$X<$ef)){$je=$x;$ef=$X;}}}$l=(!$_POST["token"]&&$ef?sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"'$je'"):'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.');}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$l=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"'post_max_size'");if(isset($_GET["sql"]))$l
.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
print_select_result($I,$g=null,array$dg=array(),$z=0){$Qe=array();$w=array();$e=array();$Ka=array();$ij=array();$J=array();for($s=0;(!$z||$s<$z)&&($K=$I->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($ye=0;$ye<count($K);$ye++){$m=$I->fetch_field();$B=$m->name;$cg=(isset($m->orgtable)?$m->orgtable:"");$bg=(isset($m->orgname)?$m->orgname:$B);if($dg&&JUSH=="sql")$Qe[$ye]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($cg!=""){if(isset($m->table))$J[$m->table]=$cg;if(!isset($w[$cg])){$w[$cg]=array();foreach(indexes($cg,$g)as$v){if($v["type"]=="PRIMARY"){$w[$cg]=array_flip($v["columns"]);break;}}$e[$cg]=$w[$cg];}if(isset($e[$cg][$bg])){unset($e[$cg][$bg]);$w[$cg][$bg]=$ye;$Qe[$ye]=$cg;}}if($m->charsetnr==63)$Ka[$ye]=true;$ij[$ye]=$m->type;echo"<th".($cg!=""||$m->name!=$bg?" title='".h(($cg!=""?"$cg.":"").$bg)."'":"").">".h($B).($dg?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$x=>$X){$_="";if(isset($Qe[$x])&&!$e[$Qe[$x]]){if($dg&&JUSH=="sql"){$R=$K[array_search("table=",$Qe)];$_=ME.$Qe[$x].urlencode($dg[$R]!=""?$dg[$R]:$R);}else{$_=ME."edit=".urlencode($Qe[$x]);foreach($w[$Qe[$x]]as$hb=>$ye)$_
.="&where".urlencode("[".bracket_escape($hb)."]")."=".urlencode($K[$ye]);}}elseif(is_url($X))$_=$X;if($X===null)$X="<i>NULL</i>";elseif($Ka[$x]&&!is_utf8($X))$X="<i>".lang_format(array('%d byte','%d bytes'),strlen($X))."</i>";else{$X=h($X);if($ij[$x]==254)$X="<code>$X</code>";}if($_)$X="<a href='".h($_)."'".(is_url($_)?target_blank():'').">$X</a>";echo"<td".($ij[$x]<=9||$ij[$x]==246?" class='number'":"").">$X";}}echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$J;}function
referencable_primary($Kh){$J=array();foreach(table_status('',true)as$vi=>$R){if($vi!=$Kh&&fk_support($R)){foreach(fields($vi)as$m){if($m["primary"]){if($J[$vi]){unset($J[$vi]);break;}$J[$vi]=$m;}}}}return$J;}function
textarea($B,$Y,$L=10,$kb=80){echo"<textarea name='".h($B)."' rows='$L' cols='$kb' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($ya,array$Xf,$Y="",$Rf="",$Fg=""){$Ci=($Xf?"select":"input");return"<$Ci$ya".($Xf?"><option value=''>$Fg".optionlist($Xf,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Fg'>").($Rf?script("qsl('$Ci').onchange = $Rf;",""):"");}function
json_row($x,$X=null){static$bd=true;if($bd)echo"{";if($x!=""){echo($bd?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\"\\/").'"':'null');$bd=false;}else{echo"\n}\n";$bd=true;}}function
edit_type($x,array$m,array$jb,array$ld=array(),array$Sc=array()){$U=$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'>";if($U&&!array_key_exists($U,driver()->types())&&!isset($ld[$U])&&!in_array($U,$Sc))$Sc[]=$U;$mi=driver()->structuredTypes();if($ld)$mi['Foreign keys']=$ld;echo
optionlist(array_merge($Sc,$mi),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($jb?"<input list='collations' name='".h($x)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'")." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($ld?"<select name='".h($x)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
process_length($y){$Bc=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$Bc(?:\\s*,\\s*$Bc)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$Bc~",$y,$Ye)?"(".implode(",",$Ye[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_type(array$m,$ib="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~char|text|enum|set~',$m["type"])&&$m["collation"]?" $ib ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$gj){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($gj),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){$k=$m["default"];$sd=$m["generated"];return($k===null?"":(in_array($sd,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($sd=="VIRTUAL"?"":" $sd")."":" GENERATED ALWAYS AS ($k) $sd"):" DEFAULT ".(!preg_match('~^GENERATED ~i',$k)&&(preg_match('~char|binary|text|json|enum|set~',$m["type"])||preg_match('~^(?![a-z])~i',$k))?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}function
edit_fields(array$n,array$jb,$U="TABLE",array$ld=array()){$n=array_values($n);$Tb=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$pb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$Tb>".'Default value',(support("comment")?"<td id='label-comment'$pb>".'Comment':"");echo"<td>".icon("plus","add[".(support("move_col")?0:count($n))."]","+",'Add next'),"</thead>\n<tbody>\n",script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");foreach($n
as$s=>$m){$s++;$eg=$m[($_POST?"orig":"field")];$ec=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$eg=="");echo"<tr".($ec?"":" style='display: none;'").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>";if($ec)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'>";echo
input_hidden("fields[$s][orig]",$eg);edit_type("fields[$s]",$m,$jb,$ld);if($U=="TABLE")echo"<td>".checkbox("fields[$s][null]",1,$m["null"],"","","block","label-null"),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Tb>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default")),"<input name='fields[$s][default]' value='".h($m["default"])."' aria-labelledby='label-default'>",(support("comment")?"<td$pb><input name='fields[$s][comment]' value='".h($m["comment"])."' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'>":"");echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ".icon("up","up[$s]","â†‘",'Move up')." ".icon("down","down[$s]","â†“",'Move down')." ":""),($eg==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){$C=0;if($_POST["up"]){$He=0;foreach($n
as$x=>$m){if(key($_POST["up"])==$x){unset($n[$x]);array_splice($n,$He,0,array($m));break;}if(isset($m["field"]))$He=$C;$C++;}}elseif($_POST["down"]){$nd=false;foreach($n
as$x=>$m){if(isset($m["field"])&&$nd){unset($n[key($_POST["down"])]);array_splice($n,$C,0,array($nd));break;}if(key($_POST["down"])==$x)$nd=$m;$C++;}}elseif($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum(array$A){$X=$A[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($ud,array$Vg,$e,$Of){if(!$Vg)return
true;if($Vg==array("ALL PRIVILEGES","GRANT OPTION"))return($ud=="GRANT"?queries("$ud ALL PRIVILEGES$Of WITH GRANT OPTION"):queries("$ud ALL PRIVILEGES$Of")&&queries("$ud GRANT OPTION$Of"));return
queries("$ud ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$Vg).$e).$Of);}function
drop_create($ic,$h,$kc,$Gi,$mc,$Re,$lf,$jf,$kf,$Lf,$yf){if($_POST["drop"])query_redirect($ic,$Re,$lf);elseif($Lf=="")query_redirect($h,$Re,$kf);elseif($Lf!=$yf){$Eb=queries($h);queries_redirect($Re,$jf,$Eb&&queries($ic));if($Eb)queries($kc);}else
queries_redirect($Re,$jf,queries($Gi)&&queries($mc)&&queries($ic)&&queries($h));}function
create_trigger($Of,array$K){$Mi=" $K[Timing] $K[Event]".(preg_match('~ OF~',$K["Event"])?" $K[Of]":"");return"CREATE TRIGGER ".idf_escape($K["Trigger"]).(JUSH=="mssql"?$Of.$Mi:$Mi.$Of).rtrim(" $K[Type]\n$K[Statement]",";").";";}function
create_routine($wh,array$K){$O=array();$n=(array)$K["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,"CHARACTER SET");}$Vb=rtrim($K["definition"],";");return"CREATE $wh ".idf_escape(trim($K["name"]))." (".implode(", ",$O).")".($wh=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(JUSH=="pgsql"?" AS ".q($Vb):"\n$Vb;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key(array$p){$j=$p["db"];$Cf=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($Cf!=""&&$Cf!=$_GET["ns"]?idf_escape($Cf).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"");}function
tar_file($o,$Ri){$J=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Ri->size),decoct(time()));$bb=8*32;for($s=0;$s<strlen($J);$s++)$bb+=ord($J[$s]);$J
.=sprintf("%06o",$bb)."\0 ";echo$J,str_repeat("\0",512-strlen($J));$Ri->send();echo
str_repeat("\0",511-($Ri->size+511)%512);}function
ini_bytes($je){$X=ini_get($je);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
doc_link(array$Bg,$Hi="<sup>?</sup>"){$Qh=connection()->server_info;$Ej=preg_replace('~^(\d\.?\d).*~s','\1',$Qh);$uj=array('sql'=>"https://dev.mysql.com/doc/refman/$Ej/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$Ej)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$Qh)."&id=",);if(connection()->flavor=='maria'){$uj['sql']="https://mariadb.com/kb/en/";$Bg['sql']=(isset($Bg['mariadb'])?$Bg['mariadb']:str_replace(".html","/",$Bg['sql']));}return($Bg[JUSH]?"<a href='".h($uj[JUSH].$Bg[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$Ej":""))."'".target_blank().">$Hi</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$J=0;foreach(table_status()as$S)$J+=$S["Data_length"]+$S["Index_length"];return
format_number($J);}function
set_utf8mb4($h){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$h)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$Dh=support("scheme");$jb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<thead><tr>".(support("database")?"<td>":"")."<th>".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'>".'Compute'."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$T){$vh=h(ME)."db=".urlencode($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$vh' id='$t'>".h($j)."</a>";$c=h(db_collation($j,$jb));echo"<td>".(support("database")?"<a href='$vh".($Dh?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$c</a>":$c),"<td align='right'><a href='$vh&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n".input_hidden("all").script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };")."<input type='submit' name='drop' value='".'Drop'."'>".confirm()."\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}if(!empty(adminer()->plugins)){echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach(adminer()->plugins
as$Gg){$Zb=(method_exists($Gg,'description')?$Gg->description():"");if(!$Zb){$kh=new
\ReflectionObject($Gg);if(preg_match('~^/[\s*]+(.+)~',$kh->getDocComment(),$A))$Zb=$A[1];}$Eh=(method_exists($Gg,'screenshot')?$Gg->screenshot():"");echo"<li><b>".get_class($Gg)."</b>".h($Zb?": $Zb":"").($Eh?" (<a href='".h($Eh)."'".target_blank().">".'screenshot'."</a>)":"")."\n";}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~ns=[^&]*&~','',ME)."ns=".get_schema());if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header('Schema'.": ".h($_GET["ns"]),'Invalid schema.',true);page_footer("ns");exit;}}}class
TmpFile{private$handler;var$size;function
__construct(){$this->handler=tmpfile();}function
write($zb){$this->size+=strlen($zb);fwrite($this->handler,$zb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=driver()->select($a,$M,array(where($_GET,$n)),$M);$K=($I?$I->fetch_row():array());echo
driver()->value($K[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=error()?:'No tables.';$S=table_status1($a);$B=adminer()->tableName($S);page_header(($n&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($B!=""?$B:h($a)),$l);$uh=array();foreach($n
as$x=>$m)$uh+=$m["privileges"];adminer()->selectLinks($S,(isset($uh["insert"])||!support("table")?"":null));$ob=$S["Comment"];if($ob!="")echo"<p class='nowrap'>".'Comment'.": ".h($ob)."\n";function
tables_links($T){echo"<ul>\n";foreach($T
as$R)echo"<li><a href='".h(ME."table=".urlencode($R))."'>".h($R)."</a>";echo"</ul>\n";}$ie=driver()->inheritsFrom($a);if($ie){echo"<h3>".'Inherits from'."</h3>\n";tables_links($ie);}elseif($n)adminer()->tableStructurePrint($n,$S);if(support("indexes")&&driver()->supportsIndex($S)){echo"<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.'Alter indexes'."</a>\n";}if(!is_view($S)){if(fk_support($S)){echo"<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$ld=foreign_keys($a);if($ld){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td></thead>\n";foreach($ld
as$B=>$p){echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".urlencode($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($B)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.'Add foreign key'."</a>\n";}if(support("check")){echo"<h3 id='checks'>".'Checks'."</h3>\n";$Xa=driver()->checkConstraints($a);if($Xa){echo"<table>\n";foreach($Xa
as$x=>$X)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.'Create check'."</a>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<h3 id='triggers'>".'Triggers'."</h3>\n";$fj=triggers($a);if($fj){echo"<table>\n";foreach($fj
as$x=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.'Add trigger'."</a>\n";}$he=driver()->inheritedTables($a);if($he){echo"<h3 id='partitions'>".'Partitions'."</h3>\n";$vg=driver()->partitionsInfo($a);if($vg)echo"<p><code class='jush-".JUSH."'>BY ".h("$vg[partition_by]($vg[partition])")."</code>\n";tables_links($he);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));$xi=array();$yi=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$Ye,PREG_SET_ORDER);foreach($Ye
as$s=>$A){$xi[$A[1]]=array($A[2],$A[3]);$yi[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$Ui=0;$Ga=-1;$Bh=array();$jh=array();$Le=array();$sa=driver()->allFields();foreach(table_status('',true)as$R=>$S){if(is_view($S))continue;$Jg=0;$Bh[$R]["fields"]=array();foreach($sa[$R]as$m){$Jg+=1.25;$m["pos"]=$Jg;$Bh[$R]["fields"][$m["field"]]=$m;}$Bh[$R]["pos"]=($xi[$R]?:array($Ui,0));foreach(adminer()->foreignKeys($R)as$X){if(!$X["db"]){$Je=$Ga;if(idx($xi[$R],1)||idx($xi[$X["table"]],1))$Je=min(idx($xi[$R],1,0),idx($xi[$X["table"]],1,0))-1;else$Ga-=.1;while($Le[(string)$Je])$Je-=.0001;$Bh[$R]["references"][$X["table"]][(string)$Je]=array($X["source"],$X["target"]);$jh[$X["table"]][$R][(string)$Je]=$X["target"];$Le[(string)$Je]=true;}}$Ui=max($Ui,$Bh[$R]["pos"][0]+2.5+$Jg);}echo'<div id="schema" style="height: ',$Ui,'em;">
<script',nonce(),'>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {',implode(",",$yi)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$Ui,';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'',js_escape(DB),'\');
</script>
';foreach($Bh
as$B=>$R){echo"<div class='table' style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($B).'"><b>'.h($B)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($R["fields"]as$m){$X='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$Ei=>$lh){foreach($lh
as$Je=>$gh){$Ke=$Je-idx($xi[$B],1);$s=0;foreach($gh[0]as$ai)echo"\n<div class='references' title='".h($Ei)."' id='refs$Je-".($s++)."' style='left: $Ke"."em; top: ".$R["fields"][$ai]["pos"]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$Ke)."em;'></div></div>";}}foreach((array)$jh[$B]as$Ei=>$lh){foreach($lh
as$Je=>$e){$Ke=$Je-idx($xi[$B],1);$s=0;foreach($e
as$Di)echo"\n<div class='references arrow' title='".h($Ei)."' id='refd$Je-".($s++)."' style='left: $Ke"."em; top: ".$R["fields"][$Di]["pos"]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Ke)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($Bh
as$B=>$R){foreach((array)$R["references"]as$Ei=>$lh){foreach($lh
as$Je=>$gh){$of=$Ui;$cf=-10;foreach($gh[0]as$x=>$ai){$Kg=$R["pos"][0]+$R["fields"][$ai]["pos"];$Lg=$Bh[$Ei]["pos"][0]+$Bh[$Ei]["fields"][$gh[1][$x]]["pos"];$of=min($of,$Kg,$Lg);$cf=max($cf,$Kg,$Lg);}echo"<div class='references' id='refl$Je' style='left: $Je"."em; top: $of"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($cf-$of)."em;'></div></div>\n";}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".urlencode($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){save_settings(array_intersect_key($_POST,array_flip(array("output","format","db_style","types","routines","events","table_style","auto_increment","triggers","data_style"))),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Oc=dump_headers((count($T)==1?key($T):DB),(DB==""||count($T)>1));$ve=preg_match('~sql~',$_POST["format"]);if($ve){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$ni=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($ve&&preg_match('~CREATE~',$ni)&&($h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1))){set_utf8mb4($h);if($ni=="DROP+CREATE")echo"DROP DATABASE IF EXISTS ".idf_escape($j).";\n";echo"$h;\n";}if($ve){if($ni)echo
use_sql($j).";\n\n";$lg="";if($_POST["types"]){foreach(types()as$t=>$U){$Cc=type_values($t);if($Cc)$lg
.=($ni!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($Cc);\n\n";else$lg
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$B=$K["ROUTINE_NAME"];$wh=$K["ROUTINE_TYPE"];$h=create_routine($wh,array("name"=>$B)+routine($K["SPECIFIC_NAME"],$wh));set_utf8mb4($h);$lg
.=($ni!='DROP+CREATE'?"DROP $wh IF EXISTS ".idf_escape($B).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($h);$lg
.=($ni!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$h;;\n\n";}}echo($lg&&JUSH=='sql'?"DELIMITER ;;\n\n$lg"."DELIMITER ;\n\n":$lg);}if($_POST["table_style"]||$_POST["data_style"]){$Gj=array();foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));$Lb=(DB==""||in_array($B,(array)$_POST["data"]));if($R||$Lb){$Ri=null;if($Oc=="tar"){$Ri=new
TmpFile;ob_start(array($Ri,'write'),1e5);}adminer()->dumpTable($B,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$Gj[]=$B;elseif($Lb){$n=fields($B);adminer()->dumpData($B,$_POST["data_style"],"SELECT *".convert_fields($n,$n)." FROM ".table($B));}if($ve&&$_POST["triggers"]&&$R&&($fj=trigger_sql($B)))echo"\nDELIMITER ;;\n$fj\nDELIMITER ;\n";if($Oc=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$B.csv",$Ri);}elseif($ve)echo"\n";}}if(function_exists('Adminer\foreign_keys_sql')){foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));if($R&&!is_view($S))echo
foreign_keys_sql($B);}}foreach($Gj
as$Fj)adminer()->dumpTable($Fj,$_POST["table_style"],1);if($Oc=="tar")echo
pack("x512");}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$Pb=array('','USE','DROP+CREATE','CREATE');$zi=array('','DROP+CREATE','CREATE');$Mb=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$Mb[]='INSERT+UPDATE';$K=get_settings("adminer_export");if(!$K)$K=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");if(!isset($K["events"])){$K["routines"]=$K["events"]=($_GET["dump"]=="");$K["triggers"]=$K["table_style"];}echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$K["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$K["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$Pb,$K["db_style"]).(support("type")?checkbox("types",1,$K["types"],'User types'):"").(support("routine")?checkbox("routines",1,$K["routines"],'Routines'):"").(support("event")?checkbox("events",1,$K["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$zi,$K["table_style"]).checkbox("auto_increment",1,$K["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$K["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Mb,$K["data_style"]),'</table>
<p><input type="submit" value="Export">
',input_token(),'
<table>
',script("qsl('table').onclick = dumpClick;");$Pg=array();if(DB!=""){$Za=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Za>".'Tables'."</label>".script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$Za></label>".script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Gj="";$Ai=tables_list();foreach($Ai
as$B=>$U){$Og=preg_replace('~_.*~','',$B);$Za=($a==""||$a==(substr($a,-1)=="%"?"$Og%":$B));$Sg="<tr><td>".checkbox("tables[]",$B,$Za,$B,"","block");if($U!==null&&!preg_match('~table~i',$U))$Gj
.="$Sg\n";else
echo"$Sg<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$Za)."</label>\n";$Pg[$Og]++;}echo$Gj;if($Ai)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-databases'".($a==""?" checked":"").">".'Database'."</label>",script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);",""),"</thead>\n";$i=adminer()->databases();if($i){foreach($i
as$j){if(!information_schema($j)){$Og=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$Og%",$j,"","block")."\n";$Pg[$Og]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$bd=true;foreach($Pg
as$x=>$X){if($x!=""&&$X>1){echo($bd?"<p>":" ")."<a href='".h(ME)."dump=".urlencode("$x%")."'>".h($x)."</a>";$bd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$I=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$ud=$I;if(!$I)$I=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($ud?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<th></thead>\n";while($K=$I->fetch_assoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.'Edit'."</a>\n";if(!$ud||DB!="")echo"<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();exit;}restart_session();$Kd=&get_session("queries");$Jd=&$Kd[DB];if(!$l&&$_POST["clear"]){$Jd=array();redirect(remove_from_uri("history"));}stop_session();page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$Pe='--'.(JUSH=='sql'?' ':'');if(!$l&&$_POST){$q=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$ei=adminer()->importServerPath();$q=@fopen((file_exists($ei)?$ei:"compress.zlib://$ei.gz"),"rb");$H=($q?fread($q,1e6):false);}else$H=get_file("sql_file",true,";");if(is_string($H)){if(function_exists('memory_get_usage')&&($hf=ini_bytes("memory_limit"))!="-1")@ini_set("memory_limit",max($hf,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$Zg=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$Jd||first(end($Jd))!=$Zg){restart_session();$Jd[]=array($Zg,time());set_session("queries",$Kd);stop_session();}}$bi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$Pe)[^\n]*\n?|--\r?\n)";$Xb=";";$C=0;$xc=true;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$nb=0;$Ec=array();$sg='[\'"'.(JUSH=="sql"?'`#':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Pe.'|$'.(JUSH=="pgsql"?'|\$[^$]*\$':'');$Vi=microtime(true);$ma=get_settings("adminer_import");$oc=adminer()->dumpFormat();unset($oc["sql"]);while($H!=""){if(!$C&&preg_match("~^$bi*+DELIMITER\\s+(\\S+)~i",$H,$A)){$Xb=preg_quote($A[1]);$H=substr($H,strlen($A[0]));}elseif(!$C&&JUSH=='pgsql'&&preg_match("~^($bi*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$A)){$Xb="\n\\\\\\.\r?\n";$C=strlen($A[0]);}else{preg_match("($Xb\\s*|$sg)",$H,$A,PREG_OFFSET_CAPTURE,$C);list($nd,$Jg)=$A[0];if(!$nd&&$q&&!feof($q))$H
.=fread($q,1e5);else{if(!$nd&&rtrim($H)=="")break;$C=$Jg+strlen($nd);if($nd&&!preg_match("(^$Xb)",$nd)){$Ra=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($Jg>0&&strtolower($H[$Jg-1])=="e"));$Cg=($nd=='/*'?'\*/':($nd=='['?']':(preg_match("~^$Pe|^#~",$nd)?"\n":preg_quote($nd).($Ra?'|\\\\.':''))));while(preg_match("($Cg|\$)s",$H,$A,PREG_OFFSET_CAPTURE,$C)){$_h=$A[0][0];if(!$_h&&$q&&!feof($q))$H
.=fread($q,1e5);else{$C=$A[0][1]+strlen($_h);if(!$_h||$_h[0]!="\\")break;}}}else{$xc=false;$Zg=substr($H,0,$Jg+($Xb[0]=="\n"?3:0));$nb++;$Sg="<pre id='sql-$nb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($Zg)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$bi*+ATTACH\\b~i",$Zg,$A)){echo$Sg,"<p class='error'>".'ATTACH queries are not supported.'."\n";$Ec[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Sg;ob_flush();flush();}$ji=microtime(true);if(connection()->multi_query($Zg)&&$g&&preg_match("~^$bi*+USE\\b~i",$Zg))$g->query($Zg);do{$I=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Sg:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".error()."\n";$Ec[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break
2;}else{$Ki=" <span class='time'>(".format_time($ji).")</span>".(strlen($Zg)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Zg))."'>".'Edit'."</a>":"");$oa=connection()->affected_rows;$Jj=($_POST["only_errors"]?"":driver()->warnings());$Kj="warnings-$nb";if($Jj)$Ki
.=", <a href='#$Kj'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$Kj');","");$Mc=null;$dg=null;$Nc="explain-$nb";if(is_object($I)){$z=$_POST["limit"];$dg=print_select_result($I,$g,array(),$z);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$Ef=$I->num_rows;echo"<p class='sql-footer'>".($Ef?($z&&$Ef>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$Ef):""),$Ki;if($g&&preg_match("~^($bi|\\()*+SELECT\\b~i",$Zg)&&($Mc=explain($g,$Zg)))echo", <a href='#$Nc'>Explain</a>".script("qsl('a').onclick = partial(toggle, '$Nc');","");$t="export-$nb";echo", <a href='#$t'>".'Export'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."<span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ma["output"])." ".html_select("format",$oc,$ma["format"]).input_hidden("query",$Zg)."<input type='submit' name='export' value='".'Export'."'>".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$bi*+(CREATE|DROP|ALTER)$bi++(DATABASE|SCHEMA)\\b~i",$Zg)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$oa)."$Ki\n";}echo($Jj?"<div id='$Kj' class='hidden'>\n$Jj</div>\n":"");if($Mc){echo"<div id='$Nc' class='hidden explain'>\n";print_select_result($Mc,$g,$dg);echo"</div>\n";}}$ji=microtime(true);}while(connection()->next_result());}$H=substr($H,$C);$C=0;}}}}if($xc)echo"<p class='message'>".'No commands to execute.'."\n";elseif($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$nb-count($Ec))," <span class='time'>(".format_time($Vi).")</span>\n";elseif($Ec&&$nb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$Ec)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form">
';$Kc="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$Zg=$_GET["sql"];if($_POST)$Zg=$_POST["query"];elseif($_GET["history"]=="all")$Zg=$Jd;elseif($_GET["history"]!="")$Zg=idx($Jd[$_GET["history"]],0);echo"<p>";textarea("query",$Zg,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"<p>";adminer()->sqlPrintAfter();echo"$Kc\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<fieldset><legend>".'File upload'."</legend><div>";$_d=(extension_loaded("zlib")?"[.gz]":"");echo(ini_bool("file_uploads")?"SQL$_d (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>\n$Kc":'File uploads are disabled.'),"</div></fieldset>\n";$Vd=adminer()->importServerPath();if($Vd)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Vd)."$_d</code>"),' <input type="submit" name="webfile" value="'.'Run file'.'">',"</div></fieldset>\n";echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$Jd){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($Jd);$X;$X=prev($Jd)){$x=key($Jd);list($Zg,$Ki,$sc)=$X;echo'<a href="'.h(ME."sql=&history=$x").'">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$Ki)."'>".@date("H:i:s",$Ki)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$Pe).*~m",'',$Zg)))),80,"</code>").($sc?" <span class='time'>($sc)</span>":"")."<br>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$rj=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$B=>$m){if(!isset($m["privileges"][$rj?"update":"insert"])||adminer()->fieldName($m)==""||$m["generated"])unset($n[$B]);}if($_POST&&!$l&&!isset($_GET["select"])){$Re=$_POST["referer"];if($_POST["insert"])$Re=($rj?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Re))$Re=ME."select=".urlencode($a);$w=indexes($a);$mj=unique_array($_GET["where"],$w);$ch="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Re,'Item has been deleted.',driver()->delete($a,$ch,$mj?0:1));else{$O=array();foreach($n
as$B=>$m){$X=process_input($m);if($X!==false&&$X!==null)$O[idf_escape($B)]=$X;}if($rj){if(!$O)redirect($Re);queries_redirect($Re,'Item has been updated.',driver()->update($a,$O,$ch,$mj?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$I=driver()->insert($a,$O);$Ie=($I?last_id($I):0);queries_redirect($Re,sprintf('Item%s has been inserted.',($Ie?" $Ie":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($n
as$B=>$m){if(isset($m["privileges"]["select"])){$wa=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$M[]=($wa?"$wa AS ":"").idf_escape($B);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$l=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$n){if(!$Z){$I=driver()->select($a,array("*"),array(),array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array(driver()->primary=>"");}if($K){foreach($K
as$x=>$X){if(!$Z)$K[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}edit_form($a,$n,$K,$rj,$l);}elseif(isset($_GET["create"])){$a=$_GET["create"];$xg=driver()->partitionBy;$_g=driver()->partitionsInfo($a);$ih=referencable_primary($a);$ld=array();foreach($ih
as$vi=>$m)$ld[str_replace("`","``",$vi)."`".str_replace("`","``",$m["field"])]=$vi;$gg=array();$S=array();if($a!=""){$gg=fields($a);$S=table_status1($a);if(count($S)<2)$l='No tables.';}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($K["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$sa=array();$vj=false;$jd=array();$fg=reset($gg);$qa=" FIRST";foreach($K["fields"]as$x=>$m){$p=$ld[$m["type"]];$gj=($p!==null?$ih[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$Xg=process_field($m,$gj);$sa[]=array($m["orig"],$Xg,$qa);if(!$fg||$Xg!==process_field($fg,$fg)){$n[]=array($m["orig"],$Xg,$qa);if($m["orig"]!=""||$qa)$vj=true;}if($p!==null)$jd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$ld[$m["type"]],'source'=>array($m["field"]),'target'=>array($gj["field"]),'on_delete'=>$m["on_delete"],));$qa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$vj=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$fg=next($gg);if(!$fg)$qa="";}}$E=array();if(in_array($K["partition_by"],$xg)){foreach($K
as$x=>$X){if(preg_match('~^partition~',$x))$E[$x]=$X;}foreach($E["partition_names"]as$x=>$B){if($B==""){unset($E["partition_names"][$x]);unset($E["partition_values"][$x]);}}$E["partition_names"]=array_values($E["partition_names"]);$E["partition_values"]=array_values($E["partition_values"]);if($E==$_g)$E=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$E=null;$if='Table has been altered.';if($a==""){cookie("adminer_engine",$K["Engine"]);$if='Table has been created.';}$B=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($B),$if,alter_table($a,$B,(JUSH=="sqlite"&&($vj||$jd)?$sa:$n),$jd,($K["Comment"]!=$S["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$S["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$S["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$E));}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a));if(!$_POST){$ij=driver()->types();$K=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($ij["int"])?"int":(isset($ij["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$K=$S;$K["name"]=$a;$K["fields"]=array();if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($gg
as$m){$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$K["fields"][]=$m;}if($xg){$K+=$_g;$K["partition_names"][]="";$K["partition_values"][]="";}}}$jb=collations();if(is_array(reset($jb)))$jb=call_user_func_array('array_merge',array_values($jb));$zc=driver()->engines();foreach($zc
as$yc){if(!strcasecmp($yc,$K["Engine"])){$K["Engine"]=$yc;break;}}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($K["name"])."' autocapitalize='off'>\n",($zc?html_select("Engine",array(""=>"(".'engine'.")")+$zc,$K["Engine"]).on_help("event.target.value",1).script("qsl('select').onchange = helpClose;")."\n":"");if($jb)echo"<datalist id='collations'>".optionlist($jb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($K["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$jb,"TABLE",$ld);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($K["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',"columnShow(this.checked, 5)","jsonly");$qb=($_POST?$_POST["comments"]:get_setting("comments"));echo(support("comment")?checkbox("comments",1,$qb,'Comment',"editingCommentsClick(this, true);","jsonly").' '.(preg_match('~\n~',$K["Comment"])?"<textarea name='Comment' rows='2' cols='20'".($qb?"":" class='hidden'").">".h($K["Comment"])."</textarea>":'<input name="Comment" value="'.h($K["Comment"]).'" data-maxlength="'.(min_version(5.5)?2048:60).'"'.($qb?"":" class='hidden'").'>'):''),'<p>
<input type="submit" value="Save">
';}echo'
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));if($xg&&(JUSH=='sql'||$a=="")){$yg=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset("partition",'Partition by',$K["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$xg),$K["partition_by"]).on_help("event.target.value.replace(/./, 'PARTITION BY \$&')",1).script("qsl('select').onchange = partitionByChange;"),"(<input name='partition' value='".h($K["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($yg||!$K["partition_by"]?" hidden":"")."' value='".h($K["partitions"])."'>\n","<table id='partition-table'".($yg?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."</thead>\n";foreach($K["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off">',($x==count($K["partition_names"])-1?script("qsl('input').oninput = partitionNameChange;"):''),'<td><input name="partition_values[]" value="'.h(idx($K["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$de=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$ae=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="SPATIAL";$w=indexes($a);$G=array();if(JUSH=="mongo"){$G=$w["_id_"];unset($de[0]);unset($w["_id_"]);}$K=$_POST;if($K)save_settings(array("index_options"=>$K["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($K["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$de)){$e=array();$Ne=array();$ac=array();$be=(support("partial_indexes")?$v["partial"]:"");$Zd=(in_array($v["algorithm"],$ae)?$v["algorithm"]:"");$O=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$Yb=idx($v["descs"],$x);$O[]=idf_escape($d).($y?"(".(+$y).")":"").($Yb?" DESC":"");$e[]=$d;$Ne[]=($y?:null);$ac[]=$Yb;}}$Lc=$w[$B];if($Lc){ksort($Lc["columns"]);ksort($Lc["lengths"]);ksort($Lc["descs"]);if($v["type"]==$Lc["type"]&&array_values($Lc["columns"])===$e&&(!$Lc["lengths"]||array_values($Lc["lengths"])===$Ne)&&array_values($Lc["descs"])===$ac&&$Lc["partial"]==$be&&(!$ae||$Lc["algorithm"]==$Zd)){unset($w[$B]);continue;}}if($e)$b[]=array($v["type"],$B,$O,$Zd,$be);}}foreach($w
as$B=>$Lc)$b[]=array($Lc["type"],$B,"DROP");if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$n=array_keys(fields($a));if($_POST["add"]){foreach($K["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$K["indexes"][$x]["columns"][]="";}$v=end($K["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$K["indexes"][]=array("columns"=>array(1=>""));}if(!$K){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$K["indexes"]=$w;}$Ne=(JUSH=="sql"||JUSH=="mssql");$Vh=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">Index Type
';$Td=" class='idxopts".($Vh?"":" hidden")."'";if($ae)echo"<th id='label-algorithm'$Td>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" class="wayoff">','Columns'.($Ne?"<span$Td> (".'length'.")</span>":"");if($Ne||support("descidx"))echo
checkbox("options",1,$Vh,'Options',"indexOptionsShow(this.checked)","jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Td>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
</thead>
';if($G){echo"<tr><td>PRIMARY<td>";foreach($G["columns"]as$x=>$d)echo
select_input(" disabled",$n,$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$ye=1;foreach($K["indexes"]as$v){if(!$_POST["drop_col"]||$ye!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$ye][type]",array(-1=>"")+$de,$v["type"],($ye==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type");if($ae)echo"<td$Td>".html_select("indexes[$ye][algorithm]",array_merge(array(""),$ae),$v['algorithm'],"label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$ye][columns][$s]' title='".'Column'."'",($n?array_combine($n,$n):$n),$d,"partial(".($s==count($v["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(JUSH=="sql"?"":$_GET["indexes"]."_")."')"),"<span$Td>",($Ne?"<input type='number' name='indexes[$ye][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":""),(support("descidx")?checkbox("indexes[$ye][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"</span> </span>";$s++;}echo"<td><input name='indexes[$ye][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Td><input name='indexes[$ye][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$ye]","x",'Remove').script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");}$ye++;}echo'</table>
</div>
<p>
<input type="submit" value="Save">
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!$l&&!$_POST["add"]){$B=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif(DB!==$B){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($B),'Database has been renamed.',rename_database($B,$K["collation"]));}else{$i=explode("\n",str_replace("\r","",$B));$oi=true;$He="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,$K["collation"]))$oi=false;$He=$j;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($He),'Database has been created.',$oi);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$jb=collations();$B=DB;if($_POST)$B=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,$jb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$ud){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$ud,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n".($jb?html_select("collation",array(""=>"(".'collation'.")")+$jb,$K["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):""),'<input type="submit" value="Save">
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',DB))."\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$K=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$B=trim($K["name"]);$_
.=urlencode($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$_,'Schema has been created.');elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$K)$K["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$_GET["ns"]))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$l);$wh=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Wd=array();$lg=array();foreach($wh["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$lg[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Wd[]=$s;}if(!$l&&$_POST){$Sa=array();foreach($wh["fields"]as$x=>$m){$X="";if(in_array($x,$Wd)){$X=process_input($m);if($X===false)$X="''";if(isset($lg[$x]))connection()->query("SET @".idf_escape($m["field"])." = $X");}if(isset($lg[$x]))$Sa[]="@".idf_escape($m["field"]);elseif(in_array($x,$Wd))$Sa[]=$X;}$H=(isset($_GET["callf"])?"SELECT":"CALL")." ".table($ba)."(".implode(", ",$Sa).")";$ji=microtime(true);$I=connection()->multi_query($H);$oa=connection()->affected_rows;echo
adminer()->selectQuery($H,$ji,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$I=connection()->store_result();if(is_object($I))print_select_result($I,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$oa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($lg)print_select_result(connection()->query("SELECT ".implode(", ",$lg)));}}echo'
<form action="" method="post">
';if($Wd){echo"<table class='layout'>\n";foreach($Wd
as$x){$m=$wh["fields"][$x];$B=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$Y=idx($_POST["fields"],$B);if($Y!=""){if($m["type"]=="set")$Y=implode(",",$Y);}input($m,$Y,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type="submit" value="Call">
',input_token(),'</form>

<pre>
';function
pre_tr($_h){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($_h))));}$R='(\+--[-+]+\+\n)';$K='(\| .* \|\n)';echo
preg_replace_callback("~^$R?$K$R?($K*)$R?~m",function($A){$cd=pre_tr($A[2]);return"<table>\n".($A[1]?"<thead>$cd</thead>\n":$cd).pre_tr($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($wh['comment']))));echo'</pre>
';}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$K=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$Di=array();foreach($K["source"]as$x=>$X)$Di[$x]=$K["target"][$x];$K["target"]=$Di;}if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(" $B"=>($K["drop"]?"":" ".format_foreign_key($K))));else{$b="ALTER TABLE ".table($a);$I=($B==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$K["drop"])$I=queries("$b ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Foreign key has been dropped.':($B!=""?'Foreign key has been altered.':'Foreign key has been created.')),$I);if(!$K["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';}page_header('Foreign key',$l,array("table"=>$a),h($a));if($_POST){ksort($K["source"]);if($_POST["add"])$K["source"][]="";elseif($_POST["change"]||$_POST["change-js"])$K["target"]=array();}elseif($B!=""){$ld=foreign_keys($a);$K=$ld[$B];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=array("");}echo'
<form action="" method="post">
';$ai=array_keys(fields($a));if($K["db"]!="")connection()->select_db($K["db"]);if($K["ns"]!=""){$hg=get_schema();set_schema($K["ns"]);}$hh=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$Di=array_keys(fields(in_array($K["table"],$hh)?$K["table"]:reset($hh)));$Rf="this.form['change-js'].value = '1'; this.form.submit();";echo"<p><label>".'Target table'.": ".html_select("table",$hh,$K["table"],$Rf)."</label>\n";if(support("scheme")){$Ch=array_filter(adminer()->schemas(),function($Bh){return!preg_match('~^information_schema$~i',$Bh);});echo"<label>".'Schema'.": ".html_select("ns",$Ch,$K["ns"]!=""?$K["ns"]:$_GET["ns"],$Rf)."</label>";if($K["ns"]!="")set_schema($hg);}elseif(JUSH!="sqlite"){$Qb=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$Qb[]=$j;}echo"<label>".'DB'.": ".html_select("db",$Qb,$K["db"]!=""?$K["db"]:$_GET["db"],$Rf)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type="submit" name="change" value="Change"></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target</thead>
';$ye=0;foreach($K["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$ai,$X,($ye==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Di,idx($K["target"],$x),"","label-target");$ye++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$K["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$K["on_update"]),'</label>
',doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",)),'<p>
<input type="submit" value="Save">
<noscript><p><input type="submit" name="add" value="Add column"></noscript>
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$ig="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$ig=strtoupper($P["Engine"]);}if($_POST&&!$l){$B=trim($K["name"]);$wa=" AS\n$K[select]";$Re=ME."table=".urlencode($B);$if='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$U=="VIEW"&&$ig=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$wa,$Re,$if);else{$Fi=$B."_adminer_".uniqid();drop_create("DROP $ig ".table($a),"CREATE $U ".table($B).$wa,"DROP $U ".table($B),"CREATE $U ".table($Fi).$wa,"DROP $U ".table($Fi),($_POST["drop"]?substr(ME,0,-1):$Re),'View has been dropped.',$if,'View has been created.',$a,$B);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($ig!="VIEW");if(!$l)$l=error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$K["materialized"],'Materialized view'):""),'<p>';textarea("select",$K["select"]);echo'<p>
<input type="submit" value="Save">
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$pe=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$ki=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$K=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($K["INTERVAL_FIELD"],$pe)&&isset($ki[$K["STATUS"]])){$Ah="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Ah.($aa!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Ah)."\n".$ki[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l);if(!$K&&$aa!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$K=reset($L);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($K["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$K[EXECUTE_AT]$K[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($K["ENDS"]),'">
<tr><th>Every<td><input type="number" name="INTERVAL_VALUE" value="',h($K["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$pe,$K["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$ki,$K["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($K["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo'<p>
<input type="submit" value="Save">
';if($aa!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$aa));echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$wh=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])&&!$l){$eg=routine($_GET["procedure"],$wh);$Fi="$K[name]_adminer_".uniqid();foreach($K["fields"]as$x=>$m){if($m["field"]=="")unset($K["fields"][$x]);}drop_create("DROP $wh ".routine_id($ba,$eg),create_routine($wh,$K),"DROP $wh ".routine_id($K["name"],$K),create_routine($wh,array("name"=>$Fi)+$K),"DROP $wh ".routine_id($Fi,$K),substr(ME,0,-1),'Routine has been dropped.','Routine has been altered.','Routine has been created.',$ba,$K["name"]);}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l);if(!$_POST){if($ba=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$wh);$K["name"]=$ba;}}$jb=get_vals("SHOW CHARACTER SET");sort($jb);$xh=routine_languages();echo($jb?"<datalist id='collations'>".optionlist($jb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',($xh?"<label>".'Language'.": ".html_select("language",$xh,$K["language"])."</label>\n":""),'<input type="submit" value="Save">
<div class="scrollable">
<table class="nowrap">
';edit_fields($K["fields"],$jb,$wh);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$K["returns"],$jb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$K["definition"]);echo'<p>
<input type="submit" value="Save">
';if($ba!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$ba));echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($K["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,'Sequence has been created.');elseif($da!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($B),$_,'Sequence has been altered.');else
redirect($_);}page_header($da!=""?'Alter sequence'.": ".h($da):'Create sequence',$l);if(!$K)$K["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$da))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){$ea=$_GET["type"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($ea),$_,'Type has been dropped.');else
query_redirect("CREATE TYPE ".idf_escape(trim($K["name"]))." $K[as]",$_,'Type has been created.');}page_header($ea!=""?'Alter type'.": ".h($ea):'Create type',$l);if(!$K)$K["as"]="AS ";echo'
<form action="" method="post">
<p>
';if($ea!=""){$ij=driver()->types();$Cc=type_values($ij[$ea]);if($Cc)echo"<code class='jush-".JUSH."'>ENUM (".h($Cc).")</code>\n<p>";echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$ea))."\n";}else{echo'Name'.": <input name='name' value='".h($K['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"datatype-enum.html",),"?");textarea("as",$K["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B=$_GET["name"];$K=$_POST;if($K&&!$l){if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(),"",array(),"$B",($K["drop"]?"":$K["clause"]));else{$I=($B==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($B)));if(!$K["drop"])$I=queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Check has been dropped.':($B!=""?'Check has been altered.':'Check has been created.')),$I);}page_header(($B!=""?'Alter check'.": ".h($B):'Create check'),$l,array("table"=>$a));if(!$K){$ab=driver()->checkConstraints($a);$K=array("name"=>$B,"clause"=>$ab[$B]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($K["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$K["clause"]);echo'<p><input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$ej=trigger_options();$K=(array)trigger($B,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$ej["Timing"])&&in_array($_POST["Event"],$ej["Event"])&&in_array($_POST["Type"],$ej["Type"])){$Of=" ON ".table($a);$ic="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$Of:"");$Re=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($ic,$Re,'Trigger has been dropped.');else{if($B!="")queries($ic);queries_redirect($Re,($B!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Of,$_POST)));if($B!="")queries(create_trigger($Of,$K+array("Type"=>reset($ej["Type"]))));}}$K=$_POST;}page_header(($B!=""?'Alter trigger'.": ".h($B):'Create trigger'),$l,array("table"=>$a));echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time<td>',html_select("Timing",$ej["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);"),'<tr><th>Event<td>',html_select("Event",$ej["Event"],$K["Event"],"this.form['Timing'].onchange();"),(in_array("UPDATE OF",$ej["Event"])?" <input name='Of' value='".h($K["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$ej["Type"],$K["Type"]),'</table>
<p>Name: <input name="Trigger" value="',h($K["Trigger"]),'" data-maxlength="64" autocapitalize="off">
',script("qs('#form')['Timing'].onchange();"),'<p>';textarea("Statement",$K["Statement"]);echo'<p>
<input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){$fa=$_GET["user"];$Vg=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$_b)$Vg[$_b][$K["Privilege"]]=$K["Comment"];}$Vg["Server Admin"]+=$Vg["File access on server"];$Vg["Databases"]["Create routine"]=$Vg["Procedures"]["Create routine"];unset($Vg["Procedures"]["Create routine"]);$Vg["Columns"]=array();foreach(array("Select","Insert","Update","References")as$X)$Vg["Columns"][$X]=$Vg["Tables"][$X];unset($Vg["Server Admin"]["Usage"]);foreach($Vg["Tables"]as$x=>$X)unset($Vg["Databases"][$x]);$xf=array();if($_POST){foreach($_POST["objects"]as$x=>$X)$xf[$X]=(array)$xf[$X]+idx($_POST["grants"],$x,array());}$vd=array();$Mf="";if(isset($_GET["host"])&&($I=connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])))){while($K=$I->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$Ye,PREG_SET_ORDER)){foreach($Ye
as$X){if($X[1]!="USAGE")$vd["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$vd["$A[2]$X[2]"]["GRANT OPTION"]=true;}}if(preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~",$K[0],$A))$Mf=$A[1];}}if($_POST&&!$l){$Nf=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Nf",ME."privileges=",'User has been dropped.');else{$zf=q($_POST["user"])."@".q($_POST["host"]);$Ag=$_POST["pass"];if($Ag!=''&&!$_POST["hashed"]&&!min_version(8)){$Ag=get_val("SELECT PASSWORD(".q($Ag).")");$l=!$Ag;}$Eb=false;if(!$l){if($Nf!=$zf){$Eb=queries((min_version(5)?"CREATE USER":"GRANT USAGE ON *.* TO")." $zf IDENTIFIED BY ".(min_version(8)?"":"PASSWORD ").q($Ag));$l=!$Eb;}elseif($Ag!=$Mf)queries("SET PASSWORD FOR $zf = ".q($Ag));}if(!$l){$th=array();foreach($xf
as$Gf=>$ud){if(isset($_GET["grant"]))$ud=array_filter($ud);$ud=array_keys($ud);if(isset($_GET["grant"]))$th=array_diff(array_keys(array_filter($xf[$Gf],'strlen')),$ud);elseif($Nf==$zf){$Kf=array_keys((array)$vd[$Gf]);$th=array_diff($Kf,$ud);$ud=array_diff($ud,$Kf);unset($vd[$Gf]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Gf,$A)&&(!grant("REVOKE",$th,$A[2]," ON $A[1] FROM $zf")||!grant("GRANT",$ud,$A[2]," ON $A[1] TO $zf"))){$l=true;break;}}}if(!$l&&isset($_GET["host"])){if($Nf!=$zf)queries("DROP USER $Nf");elseif(!isset($_GET["grant"])){foreach($vd
as$Gf=>$th){if(preg_match('~^(.+)(\(.*\))?$~U',$Gf,$A))grant("REVOKE",array_keys($th),$A[2]," ON $A[1] FROM $zf");}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),!$l);if($Eb)connection()->query("DROP USER $zf");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')));$K=$_POST;if($K)$vd=$xf;else{$K=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$K["pass"]=$Mf;if($Mf!="")$K["hashed"]=true;$vd[(DB==""||$vd?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($K["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($K["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($K["pass"]),'" autocomplete="new-password">
',($K["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8)?"":checkbox("hashed",1,$K["hashed"],'Hashed',"typePassword(this.form['pass'], this.checked);")),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($vd
as$Gf=>$ud){echo'<th>'.($Gf!="*.*"?"<input name='objects[$s]' value='".h($Gf)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"</thead>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Columns"=>'Column',"Procedures"=>'Routine',)as$_b=>$Yb){foreach((array)$Vg[$_b]as$Ug=>$ob){echo"<tr><td".($Yb?">$Yb<td":" colspan='2'").' lang="en" title="'.h($ob).'">'.h($Ug);$s=0;foreach($vd
as$Gf=>$ud){$B="'grants[$s][".h(strtoupper($Ug))."]'";$Y=$ud[strtoupper($Ug)];if($_b=="Server Admin"&&$Gf!=(isset($vd["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($Y?" checked":"").($Ug=="All privileges"?" id='grants-$s-all'>":">".($Ug=="Grant option"?"":script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$s-all'); };"))),"</label>";$s++;}}}echo"</table>\n",'<p>
<input type="submit" value="Save">
';if(isset($_GET["host"]))echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',"$fa@$_GET[host]"));echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$De=0;foreach((array)$_POST["kill"]as$X){if(kill_process($X))$De++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$De),$De||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
',script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");$s=-1;foreach(process_list()as$s=>$K){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$x=>$X)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",));echo"</thead>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[JUSH=="sql"?"Id":"pid"],0):"");foreach($K
as$x=>$X)echo"<td>".((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$K["Command"])&&$X!="")||(JUSH=="pgsql"&&$x=="current_query"&&$X!="<IDLE>")||(JUSH=="oracle"&&$x=="sql_text"&&$X!="")?"<code class='jush-".JUSH."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.'Clone'.'</a>':h($X));echo"\n";}echo'</table>
</div>
<p>
';if(support("kill"))echo($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$n=fields($a);$ld=column_foreign_keys($a);$If=$S["Oid"];$na=get_settings("adminer_import");$uh=array();$e=array();$Gh=array();$ag=array();$Ji="";foreach($n
as$x=>$m){$B=adminer()->fieldName($m);$vf=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$B!=""){$e[$x]=$vf;if(is_shortable($m))$Ji=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$B!="")$Gh[$x]=$vf;if(isset($m["privileges"]["order"])&&$B!="")$ag[$x]=$vf;$uh+=$m["privileges"];}list($M,$wd)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$wd=array_unique($wd);$te=count($wd)<count($M);$Z=adminer()->selectSearchProcess($n,$w);$Zf=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$nj=>$K){$wa=convert_field($n[key($K)]);$M=array($wa?:idf_escape(key($K)));$Z[]=where_check($nj,$n);$J=driver()->select($a,$M,$Z,$M);if($J)echo
first($J->fetch_row());}exit;}$G=$pj=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$G=array_flip($v["columns"]);$pj=($M?$G:array());foreach($pj
as$x=>$X){if(in_array(idf_escape($x),$M))unset($pj[$x]);}break;}}if($If&&!$G){$G=$pj=array($If=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($If));}if($_POST&&!$l){$Mj=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ab=array();foreach($_POST["check"]as$Wa)$ab[]=where_check($Wa,$n);$Mj[]="((".implode(") OR (",$ab)."))";}$Mj=($Mj?"\nWHERE ".implode(" AND ",$Mj):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$pd=($M?implode(", ",$M):"*").convert_fields($e,$n,$M)."\nFROM ".table($a);$yd=($wd&&$te?"\nGROUP BY ".implode(", ",$wd):"").($Zf?"\nORDER BY ".implode(", ",$Zf):"");$H="SELECT $pd$Mj$yd";if(is_array($_POST["check"])&&!$G){$lj=array();foreach($_POST["check"]as$X)$lj[]="(SELECT".limit($pd,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n).$yd,1).")";$H=implode(" UNION ALL ",$lj);}adminer()->dumpData($a,"table",$H);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$ld)){if($_POST["save"]||$_POST["delete"]){$I=true;$oa=0;$O=array();if(!$_POST["delete"]){foreach($_POST["fields"]as$B=>$X){$X=process_input($n[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}if($_POST["delete"]||$O){$H=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($G&&is_array($_POST["check"]))||$te){$I=($_POST["delete"]?driver()->delete($a,$Mj):($_POST["clone"]?queries("INSERT $H$Mj".driver()->insertReturning($a)):driver()->update($a,$O,$Mj)));$oa=connection()->affected_rows;if(is_object($I))$oa+=$I->num_rows;}else{foreach((array)$_POST["check"]as$X){$Lj="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n);$I=($_POST["delete"]?driver()->delete($a,$Lj,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Lj)):driver()->update($a,$O,$Lj,1)));if(!$I)break;$oa+=connection()->affected_rows;}}}$if=lang_format(array('%d item has been affected.','%d items have been affected.'),$oa);if($_POST["clone"]&&$I&&$oa==1){$Ie=last_id($I);if($Ie)$if=sprintf('Item%s has been inserted.'," $Ie");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$if,$I);if(!$_POST["delete"]){$Mg=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$Mg),$Mg,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$l='Ctrl+click on a value to modify it.';else{$I=true;$oa=0;foreach($_POST["val"]as$nj=>$K){$O=array();foreach($K
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$X!=""?adminer()->processInput($n[$x],$X):"NULL");}$I=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($nj,$n),($te||$G?0:1)," ");if(!$I)break;$oa+=connection()->affected_rows;}queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$oa),$I);}}elseif(!is_string($Zc=get_file("csv_file",true)))$l=upload_error($Zc);elseif(!preg_match('~~u',$Zc))$l='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$na["output"],"format"=>$_POST["separator"]),"adminer_import");$I=true;$kb=array_keys($n);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Zc,$Ye);$oa=count($Ye[0]);driver()->begin();$Mh=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Ye[0]as$x=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Mh]*)$Mh~",$X.$Mh,$Ze);if(!$x&&!array_diff($Ze[1],$kb)){$kb=$Ze[1];$oa--;}else{$O=array();foreach($Ze[1]as$s=>$hb)$O[idf_escape($kb[$s])]=($hb==""&&$n[$kb[$s]]["null"]?"NULL":q(preg_match('~^".*"$~s',$hb)?str_replace('""','"',substr($hb,1,-1)):$hb));$L[]=$O;}}$I=(!$L||driver()->insertUpdate($a,$L,$G));if($I)driver()->commit();queries_redirect(remove_from_uri("page"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$oa),$I);driver()->rollback();}}}$vi=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $vi",$l);$O=null;if(isset($uh["insert"])||!support("table")){$rg=array();foreach((array)$_GET["where"]as$X){if(isset($ld[$X["col"]])&&count($ld[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$rg["set"."[".bracket_escape($X["col"])."]"]=$X["val"];}$O=$rg?"&".http_build_query($rg):"";}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($n?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$Gh,$w);adminer()->selectOrderPrint($Zf,$ag,$w);adminer()->selectLimitPrint($z);adminer()->selectLengthPrint($Ji);adminer()->selectActionPrint($w);echo"</form>\n";$D=$_GET["page"];$od=null;if($D=="last"){$od=get_val(count_rows($a,$Z,$te,$wd));$D=floor(max(0,intval($od)-1)/$z);}$Hh=$M;$xd=$wd;if(!$Hh){$Hh[]="*";$Ab=convert_fields($e,$n,$M);if($Ab)$Hh[]=substr($Ab,2);}foreach($M
as$x=>$X){$m=$n[idf_unescape($X)];if($m&&($wa=convert_field($m)))$Hh[$x]="$wa AS $X";}if(!$te&&$pj){foreach($pj
as$x=>$X){$Hh[]=idf_escape($x);if($xd)$xd[]=idf_escape($x);}}$I=driver()->select($a,$Hh,$Z,$xd,$Zf,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(JUSH=="mssql"&&$D)$I->seek($z*$D);$wc=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&JUSH=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z&&$wd&&$te&&JUSH=="sql")$od=get_val(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".'No rows.'."\n";else{$Ea=adminer()->backwardKeys($a,$vi);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$wd&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$wf=array();$rd=array();reset($M);$eh=1;foreach($L[0]as$x=>$X){if(!isset($pj[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$B=($m?adminer()->fieldName($m,$eh):($X["fun"]?"*":h($x)));if($B!=""){$eh++;$wf[$x]=$B;$d=idf_escape($x);$Nd=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($x);$Yb="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($x))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$qd=apply_sql_function($X["fun"],$B);$Zh=isset($m["privileges"]["order"])||$qd;echo($Zh?"<a href='".h($Nd.($Zf[0]==$d||$Zf[0]==$x||(!$Zf&&$te&&$wd[0]==$d)?$Yb:''))."'>$qd</a>":$qd),"<span class='column hidden'>";if($Zh)echo"<a href='".h($Nd.$Yb)."' title='".'descending'."' class='text'> â†“</a>";if(!$X["fun"]&&isset($m["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($x)."');");echo"</span>";}$rd[$x]=$X["fun"];next($M);}}$Ne=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$x=>$X)$Ne[$x]=max($Ne[$x],min(40,strlen(utf8_decode($X))));}}echo($Ea?"<th>".'Relations':"")."</thead>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($L,$ld)as$uf=>$K){$mj=unique_array($L[$uf],$w);if(!$mj){$mj=array();reset($M);foreach($L[$uf]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$mj[$x]=$X;next($M);}}$nj="";foreach($mj
as$x=>$X){$m=(array)$n[$x];if((JUSH=="sql"||JUSH=="pgsql")&&preg_match('~char|text|enum|set~',$m["type"])&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".(JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($X);}$nj
.="&".($X!==null?urlencode("where[".bracket_escape($x)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($x));}echo"<tr>".(!$wd&&$M?"":"<td>".checkbox("check[]",substr($nj,1),in_array(substr($nj,1),(array)$_POST["check"])).($te||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$nj)."' class='edit'>".'edit'."</a>"));reset($M);foreach($K
as$x=>$X){if(isset($wf[$x])){$d=current($M);$m=(array)$n[$x];$X=driver()->value($X,$m);if($X!=""&&(!isset($wc[$x])||$wc[$x]!=""))$wc[$x]=(is_mail($X)?$wf[$x]:"");$_="";if(preg_match('~blob|bytea|raw|file~',$m["type"])&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($x).$nj;if(!$_&&$X!==null){foreach((array)$ld[$x]as$p){if(count($ld[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$ai)$_
.=where_link($s,$p["target"][$s],$L[$uf][$ai]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$mj))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($mj
as$_e=>$W)$_
.=where_link($s++,$_e,$W);}$Od=select_value($X,$_,$m,$Ji);$t=h("val[$nj][".bracket_escape($x)."]");$Ng=idx(idx($_POST["val"],$nj),bracket_escape($x));$rc=!is_array($K[$x])&&is_utf8($Od)&&$L[$uf][$x]==$K[$x]&&!$rd[$x]&&!$m["generated"];$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$Hi=preg_match('~text|json|lob~',$U);$ue=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($ue&&($X===null||is_numeric(strip_tags($Od))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$rc&&$X!==null)||$Ng!==null){$Ad=h($Ng!==null?$Ng:$K[$x]);echo">".($Hi?"<textarea name='$t' cols='30' rows='".(substr_count($K[$x],"\n")+1)."'>$Ad</textarea>":"<input name='$t' value='$Ad' size='$Ne[$x]'>");}else{$Te=strpos($Od,"<i>â€¦</i>");echo" data-text='".($Te?2:($Hi?1:0))."'".($rc?"":" data-warning='".h('Use edit link to modify this value.')."'").">$Od";}}next($M);}if($Ea)echo"<td>";adminer()->backwardKeysPrint($Ea,$L[$uf]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Jc=true;if($_GET["page"]!="last"){if(!$z||(count($L)<$z&&($L||!$D)))$od=($D?$D*$z:0)+count($L);elseif(JUSH!="sql"||!$te){$od=($te?false:found_rows($S,$Z));if(intval($od)<max(1e4,2*($D+1)*$z))$od=first(slow_query(count_rows($a,$Z,$te,$wd)));else$Jc=false;}}$pg=($z&&($od===false||$od>$z||$D));if($pg)echo(($od===false?count($L)+1:$od-$D*$z)>$z?'<p><a href="'.h(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $z, '".'Loading'."â€¦');",""):''),"\n";echo"<div class='footer'><div>\n";if($pg){$bf=($od===false?$D+(count($L)>=$z?2:1):floor(($od-1)/$z));echo"<fieldset>";if(JUSH!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" â€¦":"");for($s=max(1,$D-4);$s<min($bf,$D+5);$s++)echo
pagination($s,$D);if($bf>0)echo($D+5<$bf?" â€¦":""),($Jc&&$od!==false?pagination($bf,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$bf'>".'last'."</a>");}else
echo"<legend>".'Page'."</legend>",pagination(0,$D).($D>1?" â€¦":""),($D?pagination($D,$D):""),($bf>$D?pagination($D+1,$D).($bf>$D+1?" â€¦":""):"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$fc=($Jc?"":"~ ").$od;$Sf="const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$fc' : checked); selectCount('selected2', this.checked || !checked ? '$fc' : checked);";echo
checkbox("all",1,0,($od!==false?($Jc?"":"~ ").lang_format(array('%d row','%d rows'),$od):""),$Sf)."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>Modify</legend><div>
<input type="submit" value="Save"',($_GET["modify"]?'':' title="'.'Ctrl+click on a value to modify it.'.'"'),'>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">',confirm(),'</div></fieldset>
';$md=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($md['sql']);break;}}if($md){print_fieldset("export",'Export'." <span id='selected2'></span>");$mg=adminer()->dumpOutput();echo($mg?html_select("output",$mg,$na["output"])." ":""),html_select("format",$md,$na["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($wc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import'>".'Import'."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import'".($_POST["import"]?"":" class='hidden'").">: ","<input type='file' name='csv_file'> ",html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$na["format"])," <input type='submit' name='import' value='".'Import'."'>","</span>";echo
input_token(),"</form>\n",(!$wd&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$Cj=($P?show_status():show_variables());if(!$Cj)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($Cj
as$K){echo"<tr>";$x=array_shift($K);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($K
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$ri=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$B=>$S){json_row("Comment-$B",h($S["Comment"]));if(!is_view($S)){foreach(array("Engine","Collation")as$x)json_row("$x-$B",h($S[$x]));foreach($ri+array("Auto_increment"=>0,"Rows"=>0)as$x=>$X){if($S[$x]!=""){$X=format_number($S[$x]);if($X>=0)json_row("$x-$B",($x=="Rows"&&$X&&$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")?"~ $X":$X));if(isset($ri[$x]))$ri[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}elseif(array_key_exists($x,$S))json_row("$x-$B","?");}}}foreach($ri
as$x=>$X)json_row("sum-$x",format_number($X));json_row("");}elseif($_GET["script"]=="kill")connection()->query("KILL ".number($_POST["kill"]));else{foreach(count_tables(adminer()->databases())as$j=>$X){json_row("tables-$j",$X);json_row("size-$j",db_size($j));}json_row("");}exit;}else{$Bi=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Bi&&!$l&&!$_POST["search"]){$I=true;$if="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$if='Tables have been truncated.';}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$if='Tables have been moved.';}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$if='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$if='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$K)$if
.="<b>".h($R)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$I=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?"":" ANALYZE"),$_POST["tables"]));$if='Tables have been optimized.';}elseif(!$_POST["tables"])$if='No tables.';elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($K=$I->fetch_assoc())$if
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect(substr(ME,0,-1),$if,$I);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){echo"<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Ai=tables_list();if(!$Ai)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>","<input type='search' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=driver()->convertOperator("LIKE %%");search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);",""),'<th>'.'Table','<td>'.'Engine'.doc_link(array('sql'=>'storage-engines.html')),'<td>'.'Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')),'<td>'.'Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286')),'<td>'.'Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),'<td>'.'Data Free'.doc_link(array('sql'=>'show-table-status.html')),'<td>'.'Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),'<td>'.'Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286')),(support("comment")?'<td>'.'Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')):''),"</thead>\n";$T=0;foreach($Ai
as$B=>$U){$Fj=($U!==null&&!preg_match('~table|sequence~i',$U));$t=h("Table-".$B);echo'<tr><td>'.checkbox(($Fj?"views[]":"tables[]"),$B,in_array("$B",$Bi,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".urlencode($B)."' title='".'Show structure'."' id='$t'>".h($B).'</a>':h($B));if($Fj)echo'<td colspan="6"><a href="'.h(ME)."view=".urlencode($B).'" title="'.'Alter view'.'">'.(preg_match('~materialized~i',$U)?'Materialized view':'View').'</a>','<td align="right"><a href="'.h(ME)."select=".urlencode($B).'" title="'.'Select data'.'">?</a>';else{foreach(array("Engine"=>array(),"Collation"=>array(),"Data_length"=>array("create",'Alter table'),"Index_length"=>array("indexes",'Alter indexes'),"Data_free"=>array("edit",'New item'),"Auto_increment"=>array("auto_increment=1&create",'Alter table'),"Rows"=>array("select",'Select data'),)as$x=>$_){$t=" id='$x-".h($B)."'";echo($_?"<td align='right'>".(support("table")||$x=="Rows"||(support("indexes")&&$x!="Data_length")?"<a href='".h(ME."$_[0]=").urlencode($B)."'$t title='$_[1]'>?</a>":"<span$t>?</span>"):"<td id='$x-".h($B)."'>");}$T++;}echo(support("comment")?"<td id='Comment-".h($B)."'>":""),"\n";}echo"<tr><td><th>".sprintf('%d in total',count($Ai)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),"<td>".h(db_collation(DB,collations()));foreach(array("Data_length","Index_length","Data_free")as$x)echo"<td align='right' id='sum-$x'>";echo"\n","</table>\n","</div>\n";if(!information_schema(DB)){echo"<div class='footer'><div>\n";$_j="<input type='submit' value='".'Vacuum'."'> ".on_help("'VACUUM'");$Vf="<input type='submit' name='optimize' value='".'Optimize'."'> ".on_help(JUSH=="sql"?"'OPTIMIZE TABLE'":"'VACUUM OPTIMIZE'");echo"<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>".(JUSH=="sqlite"?$_j."<input type='submit' name='check' value='".'Check'."'> ".on_help("'PRAGMA integrity_check'"):(JUSH=="pgsql"?$_j.$Vf:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'> ".on_help("'ANALYZE TABLE'").$Vf."<input type='submit' name='check' value='".'Check'."'> ".on_help("'CHECK TABLE'")."<input type='submit' name='repair' value='".'Repair'."'> ".on_help("'REPAIR TABLE'"):"")))."<input type='submit' name='truncate' value='".'Truncate'."'> ".on_help(JUSH=="sqlite"?"'DELETE'":"'TRUNCATE".(JUSH=="pgsql"?"'":" TABLE'")).confirm()."<input type='submit' name='drop' value='".'Drop'."'>".on_help("'DROP TABLE'").confirm()."\n";$i=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($i)!=1&&JUSH!="sqlite"){$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo"<p><label>".'Move to other database'.": ",($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"\n";}echo"<input type='hidden' name='all' value=''>",script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $T);":"")." }"),input_token(),"</div></fieldset>\n","</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo"<p class='links'><a href='".h(ME)."create='>".'Create table'."</a>\n",(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":"");if(support("routine")){echo"<h3 id='routines'>".'Routines'."</h3>\n";$yh=routines();if($yh){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td></thead>\n";foreach($yh
as$K){$B=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.h($K["ROUTINE_NAME"]).'</a>','<td>'.h($K["ROUTINE_TYPE"]),'<td>'.h($K["DTD_IDENTIFIER"]),'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n";}if(support("sequence")){echo"<h3 id='sequences'>".'Sequences'."</h3>\n";$Ph=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Ph){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Ph
as$X)echo"<tr><th><a href='".h(ME)."sequence=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n";}if(support("type")){echo"<h3 id='user-types'>".'User types'."</h3>\n";$yj=types();if($yj){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($yj
as$X)echo"<tr><th><a href='".h(ME)."type=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."type='>".'Create type'."</a>\n";}if(support("event")){echo"<h3 id='events'>".'Events'."</h3>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?'At given time'."<td>".$K["Execute at"]:'Every'." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$Hc=get_val("SELECT @@event_scheduler");if($Hc&&$Hc!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Hc)."\n";}echo'<p class="links"><a href="'.h(ME).'event=">'.'Create event'."</a>\n";}if($Ai)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}}}page_footer();