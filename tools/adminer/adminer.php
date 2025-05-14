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
as$B=>$m){echo"<tr><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$mh))$k=$mh[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($K!==null?($K[$B]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($K[$B])?implode(",",$K[$B]):(is_bool($K[$B])?+$K[$B]:$K[$B])):(!$rj&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);$r=($_POST["save"]?idx($_POST["function"],$B,""):($rj&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$rj&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ba!==false)$Ba=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ba);if($Ba)$Ba=false;echo"\n";}if(!support("table")&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($n){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($rj?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($rj?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."…', this); };"):"");}echo($rj?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$y=80,$pi=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);return
h($A[1]).$pi.(isset($A[2])?"":"<i>…</i>");}function
icon($Pd,$B,$Od,$Ni){return"<button type='submit' name='$B' title='".h($Ni)."' class='icon icon-$Pd'><span>$Od</span></button>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}@ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M��h��g�б���\"P�i��m��cQCa��	2ó��d<��f�a��:;NB�q�R;1Lf�9��u7&)�l;3�����J/��CQX�r2M�a�i0���)��e:LuÝh�-9��23l��i7��m�Zw4���њ<-��̴�!�U,��Fé�vt2��S,��a�҇F�VX�a�Nq�)�-���ǜh�:n5���9�Y�;j��-��_�9kr��ٓ;.�tTq�o�0�����{��y��\r�Hn��GS��Zh��;�i^�ux�WΒC@����k��=��b����/A��0�+�(���l���\\��x�:\r��b8\0�0!\0F�\nB�͎�(�3�\r\\�����Ȅa���'I�|�(i�\n�\r���4O�g@�4�C��@@�!�QB��	°�c��¯�q,\r1Eh��&2PZ���iG�H9G�\"v���������4r����D�R�\n�pJ�-A�|/.�c�Du�����:,��=��R�]U5�mV�k�LLQ@-\\����@9��%�S�r���MPD��Ia\r�(YY\\�@X�p��:��p�l�LC �������O,\r�2]7�?m06�p�T��aҥC�;_˗�yȴd�>��bn���n�ܣ3�X���8\r�[ˀ-)�i>V[Y�y&L3�#�X|�	�X�\\ù`�C���#��H��2�2.#���Z�`�<��s����Ò��\0u�h־��M��_\niZeO/CӒ_�`3���1>�=��k3����R/;�/d��\0�����ڵm���7/���A�X�������q.�s�L��� :\$�F�������w�8�߾~�H�j��\"�����Գ7gS���FL�ί�Q�_��O'W��]c=�5�1X~7;��i��\r�*\n��JS1Z���������c���t��A�V�86f�d�y;Y�]��zI�p�����c�3�Y�]}@�\$.+�1�'>Z�cpd���GL��#k�8Pz�Y�Au�v�]s9���_Aq���:���\nK�hB�;���XbAHq,��CI�`����j�S[ˌ�1�V�r���;�p�B��)#鐉;4�H��/*�<�3L��;lf�\n�s\$K`�}��Ք���7�jx`d�%j]��4��Y��HbY��J`�GG��.��K��f�I�)2�Mfָ�X�RC��̱V,���~g\0���g6�:�[j�1H�:AlIq�u3\"���q��|8<9s'�Q]J�|�\0�`p���jf�O�b�����q��\$����1J�>R�H(ǔq\n#r����@�e(y�VJ�0�Q҈��6�P�[C:�G伞���4���^����PZ��\\���(\n��)�~���9R%�Sj�{��7�0�_��s	z|8�H�	\"@�#9DVL�\$H5�WJ@��z�a�J �^	�)�2\nQv��]�������j (A���BB05�6�b˰][��k�A�wvkg�ƴ���+k[jm�zc�}�MyDZi�\$5e��ʷ���	�A��CY%.W�b*뮼�.���q/%}B�X���ZV337�ʻa�������wW[�L�Q�޲�_��2`�1I�i,�曣�Mf&(s-����Aİ�*��Dw��TN�ɻ�jX\$�x�+;���F�93�JkS;���qR{>l�;B1A�I�b)��(6��r�\r�\rڇ����Z�R^SOy/��M#��9{k���v\"�KC�J��rEo\0��\\,�|�fa͚��hI��/o�4�k^p�1H�^����phǡV�vox@�`�g�&�(����;��~Ǎz�6�8�*���5����E���p����Ә���3��ņg��rD�L�)4g{���峩�L��&�>脻����Z�7�\0��̊@�����ff�RVh֝��I�ۈ���r�w)����=x^�,k��2��ݓj�b�l0u�\"�fp��1�RI��z[]�w�pN6dI�z���n.7X{;��3��-I	����7pjÝ�R�#�,�_-���[�>3�\\���Wq�q�J֘�uh���FbL�K���yVľ����ѕ�����V���f{K}S��ޝ��M���̀��.M�\\�ix�b���1�+�α?<�3�~H��\$�\\�2�\$� e�6t�Ö�\$s���x��x���C�nSkV��=z6����'æ�Na��ָh��������R�噣8g�����w:_�����ҒIRKÝ�.�nkVU+dwj��%�`#,{�醳����Y����(oվ��.�c�0g�DXOk�7��K��l��hx;�؏ ݃L��\$09*�9 �hNr�M�.>\0�rP9�\$�g	\0\$\\F�*�d'��L�:�b���4�2����9��@�Hnb�-��E #Ĝ����rPY�� t� �\n�5.�����\$op�l�X\n@`\r��	��\r���� � ���	������ �	@�@�\n � �	\0j@�Q@�1\r��@� �	\$p	 V\0�``\n\0�\n �\n@�'����\n\0`\r����	��\r���\0�r����	\0�`�	���{	,�\"��^P�0�\n��4�\n0���.0�p���\rp�\r��p���p��q�Q0�%���1Q8\n �\0�k�ȼ\0^���\0`��@���>\n�o1w�,Y	h*=����P�:іV��и.q����\r�\r�p���1��Q	��1� �`��/17����\r�^��\"y`�\n�� �#��\0�	 p\n��\n��`� �r �Q��b�1��3\n��#��#�1�\$q�\$ѱ%0�%q�%��&�&q� �&�'1�\rR}16	 �@b\r`�`�\r��	�����d���	j\n�``��\n��`dcсP��,�1R��\$�rI�O �	Q	�Y32b1�&��01��� �� f��\0�\0���f�\0j\n�f`�	 �\n`�@�\$n=`�\0��v nI�\$�P(�d'�����g�6��-��-�C7R��� �	4��-1�&��2t\r�\"\n 	H*@�	�`\n � �	��l�2�,z\r�~� �\r�F�th�������m����z�~�\0]G�F\\��I�\\��}It�C\n�T�}���IEJ\rx����>�Mp��IH�~��fht��.b��xYE��iK��oj�\n���L��tr�.�~d�H�2U4�G�\\A��4��uPt����谐����L/�P�	\"G!R��Mt�O-��<#�APuI��R�\$�c���D�Ɗ����-��G�O`Pv�^W@tH;Q��Rę�\$��gK�F<\rR*\$4���'�����[��I��Um��h:+��5@/�l�I���2���^�\0OD�����\rR'�\r�TЭ[����Ī��MC�M�Z4�E B\"�`���euN�,䙬�]��t�\r�`�@h��*\r�.V��%�!MBlPF��\"��&�/@�v\\C��:mMgn����i8�I2\rp�vj����+Z mT�ue��fv>f��C�T�M.�M�3Pv'kt�dO\rd��k���yW��߂��V6�U�ʖ����-~X��������BGd�\$i��%�qj�E��rLJ�Pr�%n=��H�\"\"\"h�_\$b@�t��\0f\"��n�H�*��Bv\$\$�B��@\"@r��(\r`� �C�X�(0&�.`�Nk9B\n&#(���@䂯��d��^�Z��� �@�`�I-u�0��\n�B�u�4sGu���tN�b�u �ub}�O~)uB��w�{��Ł5=w9[ɫs��	8=�\0\r��%`�]x&^�3sc݃.\$�̓�Z4�4��u`���Ǆ�ۅ �8;�wT�M��e��х�݇8X��zl�K�U��`�������^XO��m�@M��⋀W��X���j��߈ؽ���XG��T�E����H��x�Y��a�,�Í��Ŋ�o|��t�%��U�j���q�7w�X�=ػ�d��xU�8�\r��O������������iy��c��.&Y%�9A��k���d�Dz�9D�ċģ[���Y��<�x��X^V�ς��DG��g�wǏ����C����I�`Ƨ��=ٗzYO� 6�|x��)���ߋ8#�ل�e��ٙ8Q�x�Y��������ߛ&K�����͝��(��%�r-��ٝ��J���� @��Z ��x�����Dz!�Wǝ�ۣy���� \0�rYy|b�|�!�Y��y���9���yzC+���=�٦�ç:]�s����w���a���x��f*�qz�ӧ`[�#�s�y���+u�XT���ٌ�c����࿎C1�8U�9��5�ݭǖ`��ͬ������Z�yg�z�� ڧ�����࿰������#�e˱Z��+��Ր�p>ǡ�L)����ű�O��+�I���x�R ˯��yB�y����I{z\0\r�ᜄ�{k�^��=������:��Γ��A�2;������7��\$�;c�������;���]XX��kY�#�5vT\\�Q��:��>��ɓ�k��'����[��a�ħ�0�xI�[���;��\0[���A�Z���e���?�ec��p΄�Ճ��:�#�fCZ��SDv��.\r��#�ߔ�U ");}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M��h��g���h0�LЁ�d91�S!��	�F�!��\"-6N����bd�Gg���:;Nr�)��c7�\r�(H�b81��s9���k\r�c)�m8�O��VA��c1��c34Of*��-�P��1��r41��6��d2�ց���o���#3���B�f#	��g9Φ�،fc\r�I���b6E�C&��,�bu��m7a�V���s��#m!��h��r���v\\3\rL:SA��dk5�n������aF��3��e6fS��y���r!�L��-�K,�3L�@��J��˲�*J��쵣����	������b�c��9���9���@����H�8��\\���6>�`�Ŏ��;�A��<T�'�p&q�qE��4�\rl���h�<5#p��R �#I��%��fBI��ܲ��>�ʫ29<��C�j2��7j��8j��c(n���?(a\0�@�5*3:δ�6����0��-�A�lL��P�4@�ɰ�\$�H�4�n31��1�t�0��͙9���WO!�r��������H����9�Q��96�F���<�7�\r�-xC\n ��@�������:\$i�ضm���4�Kid��{\n6\r���xhˋ�#^'4V�@a��<�#h0�S�-�c��9�+p���a�2�cy�h�BO\$��9�w�iX�ɔ�VY9�*r�Htm	�@b��|@�/��l�\$z���+�%p2l���.�������7�;�&{��m��X�C<l9��6x9�m�������7R��0\\�4��P�)A�o��x���q�O#����f[;��6~P�\r�a��T�GT0���u�ޟ���\n3�\\ \\ʎ�J�ud�CG���PZ�>����d8�Ҩ������C?V��dL��L.(ti���>�,�֜�R+9i��ޞC\$��#\"�AC�hV�b\n��6�T2�ew�\nf��6m	!1'c��;��*eLRn\r�G\$�2S\$��0���a�'�l6�&�~A�d\$�J�\$s� �ȃB4���j�.�RC̔�Q�j�\"7\n�Xs!�6=�BȀ}");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("':�̢���i1��1��	4������Q6a&��:OAI��e:NF�D|�!���Cy��m2��\"���r<�̱���/C�#����:DbqSe�J�˦Cܺ\n\n��ǱS\rZ��H\$RAܞS+XKvtd�g:��6��EvXŞ�j��mҩej�2�M�����B��&ʮ�L�C�3���Q0�L��-x�\n��D���yNa�Pn:�����s��͐�(�cL��/���(�5{���Qy4��g-�����i4ڃf��(��bU���k��o7�&�ä�*ACb����`.����\r����������\n��Ch�<\r)`�إ`�7�Cʒ���Z���X�<�Q�1X���@�0dp9EQ�f����F�\r��!���(h��)��\np'#Č��H�(i*�r��&<#��7K��~�# ��A:N6�����l�,�\r��JP�3�!@�2>Cr���h�N��]�(a0M3�2��6��U��E2'!<��#3R�<�����X���CH�7�#n�+��a\$!��2��P�0�.�wd�r:Y����E��!]�<��j��@�\\�pl�_\r�Z���ғ�TͩZ�s�3\"�~9���j��P�)Q�YbݕD�Yc��`��z�c��Ѩ��'�#t�BOh�*2��<ŒO�fg-Z����#��8a�^��+r2b��\\��~0�������W����n��p!#�`��Z��6�1�2��@�ky��9\r��B3�pޅ�6��<�!p�G�9�n�o�6s��#F�3���bA��6�9���Z�#��6��%?�s��\"��|؂�)�b�Jc\r����N�s��ih8����ݟ�:�;��H�ތ�u�I5�@�1��A�PaH^\$H�v��@ÛL~���b9�'�����S?P�-���0�C�\nR�m�4���ȓ:���Ը�2��4��h(k\njI��6\"�EY�#��W�r�\r��G8�@t���Xԓ��BS\nc0�k�C I\rʰ<u`A!�)��2��C�\0=��� ���P�1�ӢK!�!��p�Is�,6�d���i1+����k���<��^�	�\n��20�Fԉ_\$�)f\0��C8E^��/3W!א)�u�*���&\$�2�Y\n�]��Ek�DV�\$�J���xTse!�RY� R��`=L���ޫ\nl_.!�V!�\r\nH�k��\$א`{1	|�����i<jRrPTG|��w�4b�\r���4d�,�E��6���<�h[N�q@Oi�>'ѩ\r����;�]#��}�0�ASI�Jd�A/Q����⸵�@t\r�UG��_G�<��<y-I�z򄤝�\"�P��B\0������q`��vA��a̡J�R�ʮ)��JB.�T��L��y����Cpp�\0(7�cYY�a��M��1�em4�c��r��S)o����p�C!I���Sb�0m��(d�EH����߳�X���/���P���y�X��85��\$+�֖���gd�����y��ϝ�J��� �lE��ur�,dCX�}e������m�]��2�̽�(-z����Z��;I��\\�) ,�\n�>�)����\rVS\njx*w`ⴷSFi��d��,���Z�JFM}Њ ��\\Z�P��`�z�Z�E]�d��ɟO�cmԁ]� ������%�\"w4��\n\$��zV�SQD�:�6���G�wM��S0B�-s��)�Z�cǁ2��δA;��n�Wz/A�Zh�G~�c�c%�[�D�&l�FR�77|�I���3��g0�L���a��c�0RJ�2��%���F� S� �L�^� tr���t����ʩ;��.喚Ł�>����[�a�N���^�(!g�@1����N�z�<b�ݖ�����O,��Cu��D�tj޹I;)�݀�\nn�c��Ȃ�W<s�	�\0�hN�P�9��{ue��ut뵕������3��=��g�����J����WQ�0���w9p-���	�������'5��\nO��e)M�)_k�z\0V�����;j�l��\n����x�Pf�-�`C�.@&]#\0ڶp�y͖ƛ�t�d�� ��b}�	G1�m�ru���*�_�xD�3�q��B�sQ��u��s%�\n�5s�ut���{�s�y���N��4�,J{4@��\0��P���^��=��l���`�e~F١h3o�\"��q�R<iUT�[Q��U��M�6�T. ��0'�pe\\�����5����pCe	ٕ�\"*�M	����D���?�h��2���zU�@7�C�4�a��iE!f�\$�B��<�9o*\$��lH�\$ �@����P\rN�Y�n<\$�	�Q�=�F&��*@]\0��� W'd� z\$��j�P[��\$���0#&��_�`+�B)�w�v%	����LcJ��RS��i`�Ů	�F�W	��\nBP\n�\r\0}	瑩0�Z���/`j\$�: �8ie���φx�����a ���Gn�sgO��U%VU��@�N��ϐ�d+�(oJ�@X���zM'F٣�WhV�I^٢�1>�@�\"���� ��Q�R!�\\�`[������.�0fb�F;���Fp�p/t`����(��V���b�Ȳ�(��H�l����ԯ1v�����H��1T�3�q���1�Ѫf�\nT\$���Nq+��`ލv�ǜ�\r�Vm���r���'ϸ��g%�\"L�m����(�(CLz��\"h�X�m=�\\H\n0U�� f&M\$�g\$�U`a\rP�>`�#g��h��`�R4H��'�����GK;\"M�ۨT�h�BE�n\"b>���\r���#�\0�N:�#_	QQ1{	f:B���R�&���)J��Br�+�K.\$�Pq�-r�S%TIT&Q���{#2o(*P��5�`�1H���'	<T�d����s��,N�� ����^\r%�3��\r&��4�B�/\0�kLH\$�4d�>���/�ඵ�H���*���3J�А�<�Hh��p�'��O/&�2I.�x3V.�s5�e3�ێZ�(�9E�g�;R�;�J��Q�@��vgz@������'dZ&�,U���F��b*�D��H! �\r�;%�x'G#��͠w��#�֠�2;#�Bv�X��a�\nb�{4K�G��%���GuE`\\\rB\r\0�-mW\rM\"��#E�cFbF�nz���@4J��[\$��%2V��%�&T�V��d�4hemN�-;Eľ%E�E�r�<\"@�F�P�L �߭�4E����z`�u�7�N�4��\0�F:h�K�h/:�\"�M�Z��\r+P4\r?��S��O;B��0\$FCEp��M\"�%H4D�|��LN�FtE��g���5�=J\r\"��޼5��4�K�P\rbZ�\r\"pEQ'DwK�W0��g'�l\"h�QF�C,�Cc���IH�P�hF]5�& f�T��iSTUS�����[4�[u�Ne�\$o�K��O ��b\" 5�\0�D�)E�%\"�]��/���ЌJ�6U�d��`��a)V-0��DӔbM�)���������`��%�ELt��+��6C7j�d��:�V4ơ3� -�R\rG�IT��#�<4-CgCP{V�\$'����g��R@�'��S=%���F�k:��k��9����e]aO��G9�;��-6��8W��*�x\"U��YlB���������	��\n��p���l����Z�m\0�5����Oq̨��b�W1s@��K�-p���E�Spw\nGWoQ�qG}vp�w}q��q�\\�7�RZ�@��t��t�;pG}w׀/%\"L�E\0t�h�)�\r��J�\\W@�	�|D#S��ƃV��R�z�2���v�����	�}�����(�\0y<�X\r��x���q�<��Isk1S�-Q4Yq8�#��v���d.ֹS;q�!,'(���<.�J7H�\"��.����u�����#�Q�\re�r�Xv[�h\$�{-�Y���JBg��iM8��'�\nƘtDZ~/�b���8��\$��DbR�O�O��`O5S>����[�D�ꔸ����_3X�)��'��Jd\r�X����UD�U�X8�x�-旅�P�N`�	�\n�Z���@Ra48��:���\0�x���N�\\�0%��f��\\��>\"@^\0Zx�Z�\0ZaBr#�X��\r��{��˕�flFb\0[�ވ\0[�6���	��� �=��\n��WB��\$'�kG�(\$y�e9�(8�& h��Rܔ��o�ȼ Ǉ���Y��4��7_��d��9�'���������z\r���  ����v�G��O8���MOh'��X�S0�\0\0�	��9�s?���I�MY�8� 9����HO��,4	��xs��P�*G����c8��Qɠ��wB|�z	@�	���9c�K��QG�bFj�X��oS�\$��dFHĂP�@ѧ<嶴�,�}�m��r��\"�'k�`��c�x��e�C��C��:���:X� �T���^�d�Æqh��s���Lv�Ү0\r,4�\r_v�L�j�jM��b[  ��ls���Z�@�����;f��`2Yc�e�'�Mer��F\$�!��\n��	*0\r�AN�LP��jٓ����;ƣV�Q|(��3����[p��8���|�^\r�Bf/�D���Ҟ B��_�N5M�� \$�\naZЦ���~�Ule�rŧr��Z�aZ�����գs8R�G�Z��w���N�_Ʊ�Yϣ�m����]��;ƚL�����c������Ű��I�Q3��O��|�y*`� �5��4�;&v8�#�R�8+`X�bV�6�ƫi�3F��E���oc82�M�\"����G�Wb\rO�C�Vd�ӭ�w\\�ͯ*cSi�Qү��R`�d7}	���)�ϴ�,�+bd�۹�FN�3��L\\��eRn\$&\\r��+d��]O5kq,&\"D�CU6j�p���\\'�@o�~�5N=�|�&�!��B�w�H�yyz7��(Ǎ���b5(3փ_\0`z�b�Уr��8	�Z�v�8L˓�)��S�M<�*7\$��\rR�b���B%��ƴDs�z�R>[�Q����&Q������'\r�pp�z�/<��}L�#��Ε���Z��\"t��\n��.4�g�P��p�D�n�ʹN��F�d\0`^����\rnȂ׳#_�� w(�2�<7-��X޹\0��s��,^�hC,�!:�\rK��.��Ӣ�Ţ���\\��+v�Z��\0�Q9eʛ˞E�w?>�\$}��D#���c�0MV3�%Y���\r��tj5��7��{ŝ�Lz=�<��8I�M�����G����L�\$��2��{(�pe?u�,R�d*X�4�����\0\"@���}<.@��	��N��\$�XU�js�/��<>\"* �#\$����&CPI	��t������?� ��	�O��\\��_��Q5Y�H@���b��c�h����뱖��O0T�'�8�w�����j+H�v_#�����06�w֎�X��d+�ܓ\\��\n\0	\\�>s��A	PF�d8m'@�\nH�\0�c�OwS�����Y�`�����R��Dna\"��~�?�m���|@6��+�GxV��\0��W�Ӱ�nw���.�؃b��9Í��E�|E���\rЈr�\"��x���-���\rN6�n�\$Ҭ�-B�H�^�)��y&��ךW�ǧ�bv�R�	���N\0��n�	T��`8X��A\r:{O�@\" �!��\$K�qo��jY֪J�����h}d<1I�xd����TT4NeeC0䥿�:D�F�5L�*::H�jZ��F�R�MրnS\n>PO�[�\$V8;#�K\\'�B��R�د��R�_�8�j��*Ej�\\~v���v��p@T�X�\0002dE	�H�V���D�\"Q'EDJB~A��A�Il*'\n�Y��.�+�9��pg���/�\"�1�8�0�IA�FCȨ�V*a��P�d�У5H\"�A��6�s�Y��;訞�/��0��v}y�\r����ץ1�u\"ˋ�m��_�0焄`���\\B1^\nk\r]lh�}]HBW`��0�꨹rFf�)�W,�ҧ]sm9'O�xԽ�,�9J8��?�4�����\"҅�۽�<�-S����M�;�v��6y|�Z����%�a�#8��TC�!�p��\n��CZ(�w��a������?9|��0<BL\r�\n�]�PB0�&�+t�H���օ�Dx^��,�L�}[��B�x}��ru��\0��\0005��S@\"Uؔ@��\0�\$��ސ\"Ҡ��]l/	��I�B4��.�6���d7��\r@=���߬���*G j����f`��:Hn��bĀ71��)C<@A�Y#�����e�o��Y!��I�DM�\nlt����/)�\\43)��2��ɸ�)���f[ ppp1���#��Ð�p\0��œl��^{��A��TH�6�����\n\0P�H�.\r���|�T�FD0��S�y����'1���K���d�����B���C�&�)�W�s Hee+@4� r���ۚ*Lp1<�f�N�Y'�-	XKVa��L���\"���\"�l��q��.YJH�m HV�/�lC�&��H)o�&\\2���%���z\n^Q(6�D� ����Jq���\00a#�6\0vr,�M��&A�������9%Yd��B�h��!W\0�b\r{���@�1��I�22�A��)�H�a@r�0G��7Dd.�LM�<��2���,k/��Me����}Ғ3�=\0�&��B��\nPd.\"��F3X��Sd(*�J6 ���F:��)1�1�?lQ&����h<J͋�f�d�Eպ*�x\n\0��.\"B -�#��Ηt�IΫ���	I8 ��8dh	��x���~��	L!K(�BX��-��h��c/�r��P�I���N�2�|��׶��|\"�M�'��K,\\H��e5*o]4��FP	2��<)�T���o��\n���I�ڢ�!�(���_8Xr�;u�����NJ�����[r��DC:�@�ͳ�l�\0�e\\*x@Aȡ&�(�5��,����#1x� �!T�D���(Q���DJ|D D:\0�A�й� �baE�?rn��Wkx��X=i��,\$3�[�r�9B�Ʊ�d��\0��H��4���<(z���?�sIbJ�g U�\n(}���J\"��A��B�19�~�I�#�\$��%d  e\"�`���t���'O=���@\$��O�\nmT�o+�Z����-�����PF?�_�I�J�X ģ2���-V�;�?2���0�*P3����_T<E�J�\\(�2����)�IQ���鬩���R��L&��!ȯK�iц�t����K�HRl�ȬEs�������D��xǴ�i���!faB���F��e>�V����-Qj�I��7���\"%Rh� g��M������-�b�58R����*��9��ꊰ���9�2Q0���IR[�Z��N\0���20�����\\[@�Q\0��Jx�����EC{���\$lp1=\0�Rо�>E~�������:0���%��R+)\0�	ƑQ�@(\"�_j�T�X\0����\r1�\0P�9#\0����H;B�|���L�Z�����6�/B��\nB�{���|H�,�	*;��(�`�2@6�>�	�?P\0/���\0|\\�eB�`��jq�U/\rc�����҆�6(N\0�/\$�\n8�j*U�\$��y*�=�;���\$�f��8X�BCE��r\"/�����kځ%\\9k���B���0�F��(��'�U���Ʈm�@k�T\0��E��sEhy�e\n�)�)��b7��(W%,�J�r��2D�rhE��\n0Q�3� U�9TPO������8j|�}�R<0���Zl ��T�������*�\$��U\r�\"�.� Ts~�~(�3�a���@��+���l�`:�`�:O�i��BX�?ʄ��7��Lj|�:n�K:ز}�\0��UMc`P%nn\n,�4�Q'%+H.�\"#G��3`�����\n1fg\0�М'�k��qxD<\"��,a|{~���C<S�i�B�\nkN���G�}���k:��������g�)�JD���hÛf�\"�kV~��mM`HO�kD��^�0/tj�l�\r�!�f<��G��T���v�#@�ek@2�w���0�ܭt���į1�u�yvː%8�?1���l��xt��mp��fK3Z�J�=\0@�^p��ۑ����]Ҳ'�t١@C�b��\r[��V��-���o�-��ݠe�}��Y��	-�-m�I\0+��V�D�[B+��(�-�4�>�q��i>=��/0-�cL�pJ b\nd��)�#��G�s����\"�Q�N����`.�ȍ�yȐEtP�q�I]��J8���rWT��I���f�aG�.떄7y��l��A��7'�1�	�S�-�xI��m���L:e�ΉA�W��ζEI��Wz��3W���)*/)C���x*c]�%�}����_��IvͲ�'�\$U��S4k�5WʏJC���7*�b%<WC@�	����c{޴���3)X�&&��eL�I���,N� 2k#p5���f4���Ǻ�z�#��\\����N�b�U��oy���S�4�`q�~1�=�8厉�*�OOJ�C�����'Dd,@kL�������\\�j2ͩ����<�@_q�2�\0�ձ�)`�������s���F\0�����\n���F��<*�x*����`����-��\r���|@����7�H@w����H]��\0�����_w��h0!�s�1Ϗ��Ǭ�hW��.��=W��R*�A_���EDԷ�?1,Ub�9=t�4è��W��^���;����@��(1<D�ÊHx�T()0z�`�_�;��AL��)\n�K[f�H���Wo�@bBK�iM���d+�>�vI�(z:��.݀��9uiѤDY����O`���]I\0��R�Ć,K,���6L��\"\"�1g�(���|T.,�9vb+\rk]u�&�|��b�S��d[�,g��aJ�(C��k��\rF�+	��9��L��))UA�B�U�h�g��c3x�-n9�����x��2��q�ib�rY7�k�y�f�,������)�٪�J:�N�8�Rcly\n��2�W�;�.>�v6Q#A0��{έi��7~@VX���^��11-�+�v|��]Vf���.�{	���\r��;�1lp�/��uF��d�\$PЮ0=@kS�0h��Ɉ@��/*(O�V.��G>�(r��!�6�����Y=XZ@�:�'&0�6kE|���'|H;���N�g�%�W�+��4�;̓��'x|�f�9���(O��d���w%9]��f}��G���s���¾�����XM0����gQ���8̄�+O}�͝0}�9�������Nh�/mgD���s������\n�74勳P~}O)�Ug�9���j�8P��ݸ�(�%����j�7oAB��i)��K��u�� �}s�1�=od�V[Ĵ\n��zl�Mзr:F#{��*#�x��ܰ�<Ds��k/mw :^����1��ύD��2�z*��n��%�����i�Ù *�!8-��tH�'����\r�к�4����8`��\"�����i]�ZZ�>Z\0ަ9����+䟂~��\$ޭ��L�P\\쇁�XA�������i���z�h�\$�SM�T'���1���D��	��5E�\0Ğ\$�ttԮ��:\rMƷS��Ӗ�ls��Af�K�k,N�l�D^zz�dS��/rt�N�>��o%i��\0J�B�po��R����/֘٫x\ny�+��,e4��q5Q'JD�]�B@m����R�Ski~����t0�[ 1�z	���&��^�\nO����V����GV@T*�H9�ωG0\0'�`�Ѱ\r���bQKsLd�*;\n����.ĔUNp�,L�@TR�e��b��F���y�n> IK��rG�	@��?cI�ݓu%G�O�1���C�h�5T�y��I��:\\0��X��>�ʊ�0�޾�QB���EI/-LBT�!b��6���k`jp\0K���>k�d���/���ISk.+*���R�|gR���W\\w���t�.)�^Zc8�Z�~F��Sǵ�S�m̕;b>\0jz=�T'�>��q�y}:�u��&��W�DQ��c-����6<[��e�x�ؠ���[���L�\0wm�l�t�z��<S�&��db�x��oi�gK�\r`�µ�?D5u@b���N��O�𤷤���Y�[�����{�Nr鉞�t���\0��tMs�cBW?�*D�.p���'2��Ge\rp*#�e�����C���\"�QI\n��hi�Q�@���\rl	����_.���t*�^��s�9���Whq���~,��Yθ��dQs¦\r�Bj��D�ǡ��<<T)C�\n�����&�D{\r�l���-R��\r@rk��Ϣ��+Z���P������u8Ȩ����s�و���o�#��g��u\$F�&\n-v\"P����j�nnt�1��V������Awbx߄�D�5��-�0�a�\0\r�/!�I����|/����h��n�Gf-Mdna�^(e�a��¨�Y��Z,�S�E�N��\\�����=�4~Mʹ�\r����Ft�Ŧ�u\"|`��E��R�z��D�`�{��@�k/K�Y����3sJ�䃿5XGͪ�%�9)Q�� �Q���1t�h��!TR���H���Q�\r�C��E�0�#w�G2��/���/��=^ �/Ժ�ΐ����E��\0{+���t�+��q�б��I�t�|����v��q��Ԉƌ&�\r\\�Vߠ=���Eb��nO�rn��X({�ɹuzK��`=:�\n����\0����[�%�:p���q+��R�ldY��\"��[V�u{H-��H�_��8j��V��5����\"\0\"N?E;+�O~�wN�];L�'���SOF����䁻��D�-�!#sN�<��� ¯��mu����G�8���Tn]�����:�zIMn� O�8���z5���o\\5�7�<��Ų#8���?sN�L��	}�x��&4�?�[�z���󳷶����<*W������e}{HZ���,(<o�o�xW�t�2���#�A*�����o\\�R�}xH>NP�|Qɚ|x�'�-� ��2\0��?ƾ2*\r|]t��p�\"�ڲJuuXyb�D\n�Z|�H7�_�W���GuXyH>T\r�G����Ql�������n!�u'�*�C5��>U�2!b	��9Pw��4�����}y�W�|���a\$�g������T�U��&~9(\\*�!b_����w�7\\����]=�\\*���@�#N7ͪ��5QN`@<\0�6!�9��l��\$�wI\$4���2��\$�&���.RZ����Y��uyᤳ�p�&SI��@�EJiL�c���V�1F�1��Z\r\r���h��k���HH��˿�����K���?x��-0\n��d�N3K��C�59)ľ:B#���dN5A1�Ɖ����Od[3ڠ��h�[s~)�9�DN�y����>���X��'Ƚ�ϐH���,��)ڂ�\"�e�0;\0�qeo>��=�|�2�G+B�@z�������@]}��rQ��� k/�|�G�:ѯ�W\0�a4>��^|���g�o�XE�9p���Lrg�A��6��p�e����1�*����7��[�>]�#�?jB�~�/�}�3�:��U\$�?�<��G��a���\n>0#!i�>.{A}'hQ�Lw�~�W_��Th#d��û��d��FQ�����*{��\"�\"�P{���}�4�N���i��\r_����e?l4�2�?\n�F��	��q�U��Ľ�_��`_�����j��{_k_�o�~��c*#�(�/�!Dn�F�`��?@s�B�!�?;�E��������\0k�	�*N��D;���+d\nZZdB��� ��`B5�P\n8�������c#ou��k�ˊM�ݯw�.��F�J���!|�Ĉ2Fc�Y).����XHy�[��~����#/�&����[�����Y@���(|\r\0,O��0Yb��βŬ�\$0���aˑ����� �A\$��0,�@�Ӱ>>9��\\t�i�<�\0�q\0�}@`�\0fVj����dߠ'(����	!_�n��0+c���iig8a]'=-�B!(��8�_���x�j�����)\rH5H�Yn	,f�r��}-d\$��H��2n鴆ܛ�=�-�d���FE-d��a��N_z4@��[�n��\$x!!i0T����u�8�ɸ����\0PZ8Z����c����+Њ�AAF(����`mg*�vS, ǆ��KcA�۬ &��9������c�0w�+�n��=��)\$���Q�~A��a�\0004\0u�{�(��\$���y	!��B�� A<�a��Az ���ZA4\$ZY9.aX\r��d�A�L�v|oOz|�Z�(�e�Z�Ć�");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("v0��F����==��FS	��_6MƳ���r:�E�CI��o:�C��Xc��\r�؄J(:=�E���a28�x�?�'�i�SANN���xs�NB��Vl0���S	��Ul�(D|҄��P��>�E�㩶yHch��-3Eb�� �b��pE�p�9.����~\n�?Kb�iw|�`��d.�x8EN��!��2��3���\r���Y���y6GFmY�8o7\n\r�0�<d4�E'�\n#�\r���.�C!�^t�(��bqH��.���s���2�N�q٤�9��#{�c�����3nӸ2��r�:<�+�9�CȨ���\n<�\r`��/b�\\���!�H�2SڙF#8Ј�I�78�K��*ں�!���鎑��+��:+���&�2|�:��9���:��N���pA/#�� �0D�\\�'�1����2�a@��+J�.�c,�����1��@^.B��ь�`OK=�`B��P�6����>(�eK%! ^!Ϭ�B��HS�s8^9�3�O1��.Xj+���M	#+�F�:�7�S�\$0�V(�FQ�\r!I��*�X�/̊���67=�۪X3݆؇���^��gf#W��g��8ߋ�h�7��E�k\r�ŹG�)��t�We4�V؝����&7�\0R��N!0�1W���y�CP��!��i|�gn��.\r�0�9�Aݸ���۶�^�8v�l\"�b�|�yHY�2�9�0�߅�.��:y���6�:�ؿ�n�\0Q�7��bk�<\0��湸�-�B�{��;�����W����&�/n�w��2A׵�����A�0yu)���kLƹtk�\0�;�d�=%m.��ŏc5�f���*�@4�� ���c�Ƹ܆|�\"맳�h�\\�f�P�N��q����s�f�~P��pHp\n~���>T_��QOQ�\$�V��S�pn1�ʚ��}=���L��Jeuc�����aA|;��ȓN��-��Z�@R��ͳ� �	��.��2�����`RE���^iP1&��ވ(���\$�C�Y�5�؃��axh@��=Ʋ�+>`��ע���\r!�b���r��2p�(=����!�es�X4G�Hhc �M�S.��|YjH��zB�SV��0�j�\nf\r�����D�o��%��\\1���MI`(�:�!�-�3=0������S���gW�e5��z�(h��d�r�ӫ�Ki�@Y.�����\$@�s�ѱEI&��Df�SR}��rڽ?�x\"�@ng����PI\\U��<�5X\"E0��t8��Y�=�`=��>�Q�4B�k���+p`�(8/N�qSK�r����i�O*[J��RJY�&u���7������#�>���Xû�?AP���CD�D���\$�����Y��<���X[�d�d��:��a\$�����Π��W�/ɂ�!+eYIw=9���i�;q\r\n���1��x�0]Q�<�zI9~W��9RD�KI6��L���C�z�\"0NW�WzH4��x�g�ת�x&�F�aӃ��\\�x��=�^ԓ���KH��x��ٓ0�EÝ҂ɚ�X�k,��R���~	��̛�Ny��Sz���6\0D	���؏�hs|.��=I�x}/�uN���'R���n'�|so8r��t����a�\0�5�P�֠dẘ��̕q����5(X�Hp|K�2`�]FU�~!��=� �|�,up�\\���C�o�T�e╙C�}*��f�#�shp��5����mZ�x��fn~v)DH4�e��v��V��by�T��̥,���<�y,̫֞�2���z^����K��2�xo	� ���2� I��a�h�~��c�ej�6��)�]����5�͍dG׊E�t�'N�=V��ɜ@����b^����p:k��1�StTԙ�F�F��`��`��{{���4��7�pcP�ط��V��9�ىLt�	M�����{�C�l��n47s�PL��!�9{l a�������!pG%��)�<��2*�<�9rV���\\����]�W�tn\r<ė�0�vJ�栱I�i��1��Ys{uHհ?�ۖ�����U�o�Aߒr`S��Cc���v�˳J�c�����=��-H/���q'E���w|��N�{\r};��>�x�r���u5�B�*\0����M�����a��\0�{HU����C�W�廳�yB'�<�6�[��s���y����@��{�Q����>?/<�K@� ���B�|aH\"�� R	�@>~@�BhE�L\$�[��Sa \"�Ђ0�Fe`b\0����@�\n`�=���n��.*̔�O��Ϙ���n��<jO�lM�\"mR���/��*�&T肙�T� _E4����8���|R0*�o��Bo>S%\$�� �N�<�|��ξ����y�7\n����޴�,鯢������쐬�Pt��\"l&T�o��E05n����o��r��v�������֣Bp��p��\n���P���.-,��q����3\r/�p��P���b����%m���P2?P����@��0(�/gpz�0�`��gυ��ϑ�\\嬳q��>��p�@\\��u��@���\$Ne�Q������0(A(�mc�L'`Bh\r-�!�b`��k`�������`N�0�	�ЯnN�`��D\0�@~����`K���] �\r�|���ʾ�A#��i�Y�xf�\r�4 ,v�\0ދQ�ɠN��Xo����� q�'��t�r\$��np�6%�%lyMb�ʕ(�S)L')��ޯL�M�I�s {&� KH�@d�l�wf0��x��6��~3�X�h0\"��D�+�A�\$��`b�\$��%2V�L�� Q\"��%���R�FV�Ny+F\n��	 �%fz���+1Z���Mɾ�R�%@ڝ6\"�bN�5.��\0�W���d��4�'l|9.#`��e憀�أj6�Τ�v����vڥ�\rh\r�s7��\"@�\\DŰi8cq8Ğ	�\0ֶbL. �\rdTb@E � \\2`P( B'�����0��/��|�3����&R.Ss+-���cAi4K�}�:�����\0O9,�B��@�CC��A'B@N=�;��7S�<3�DI��MW7��ED�\rŨ�v��@�DȺ�9 �l~\r�d���5�z^�r!�}I������sB��\0eT�K�!�K�UH���/����2�i%<=���^ ��g�8�r7s���%N��E@v�sl5\rp��\$�@����P��\r�\$=�%4��nX\\Xd��z٬~O��x�:��m\"�&��g5Qn�(ൕ5&rs� N\r9���.I�Y63g6�]Qsv�b/O �|��@�y��^ur\"UvI�{V-MVuOD�h`�5�t����\0��T�,	(��q�R�G�.l6[S0@�%���C}T7�85mY��)�8�C��r�;�ئ)�M+�4	� ��4��|�Ϊ1�ZJ`׉5X,�L\0�7T\rx��H��dR*����JЦ\r����52����-Cm1S�R��T`N�e@'Ʀ*�*`�>���\0|��I!�E,�ag�.��cup�Ý9�`B��aa���p�`�m�6��R~�\0���g-cmO��1�\reIN�QN�qo\r��nq����R6�n�Sn�t�w�æ\r�]a���-�a*���\\5Wpv^ OV`AF����3#82p�H'J'nM('M=j�9k�ZbBn�<�@�<� \0�fe�:\0�K(��N���v����-!�1�ލH(�Qg��µ���y�<�� �d�\\�c\\�s,u�˃q0�~�i~���e�Ѷ��*�~��Ƞ�~��M�m���}Wؘ\r�� �@�\"i�\$B���c�g��5b?�6!w��+xl1�`��`��	s�� ��������.�vCnhEd Q��id\"6���`�\"&f�x�(\"�2�Qz��\$�[0%�0lw u��>w�%ر��%�w�Z�\"-���u�%�����Y�g��>x\\�-��פ���-v�\\��x^'M	�P���Y�P����)�8�%�C���@�DF ��\r@�\0�\\�0N��.��S\$��YI��C�I�� �i�>xP͸͒�:ͷ�=��T,�'L��ٞ�q�Q2͌�\r�����d���Δ���@�ђ���9F���`�O��f�O�w�\\h�=�}S�jGGW����A��L��RJ\$JP+�7���L�v,ә(̵���ZP�g����&z+����j���˘7�ͷ��-�vA��w��h �^9�T�OD���Z��C���m����`O��R�yӒ��!�Gvzs��G�\$IhY�����58��xF������Y9����i�ݍ8��U��C��[���e���Zq�uA��1���?���و�9!����:�ړ����b0��{\r�Qh`Md7�{2� ۲8�H`%Ƹ��{-�l�C�Xk�H��Ӟ��|\0�}�X`ShխX�ց��\r���O�y��X��� �:w7�����n�鲌�#�/�:4�(M��;���c�D��z;�Z3��������]��砛�?�.�����\r�o���bO�^`Ϻ�|��������/��X׎�]�|�����^�!%Xٽ��8���\$�;��z�T�xK��-~� 8X)<!��y�x�9�����:�� ىF�������x�z+U຃�����A�E�;��'�%c����Yߪ����w�<{��9���V:��`��ʇ<����G�ء�Y��\0�Z��U�Zq\nmx�)_�}�YǏ�_�z�����y\r�Y��,ۚ3�L��٪�Y��ٸϻ>�M��	�M���	�)��P\0u8 S!Z{Y����9θ���fV3�o�Oϼ�E��`CЭ��࿿XU���}�lw��0�}�����͙�7�Y3ӬӔ��4��G��J��&�ä��ͭ(���-A��V=f|���@E/�%\0r}�ޮnn�\0��Ly�����<+���_��|��#�A��\"C[y��EW���rW��f(\0��Л�>�)�����_�U��,U�\\�#��e���*r�`�N�Y� �*�=a�\\��&�^g4�mü����e#��^�|ނ�QXN����I�>��\0rƉ���4��^Y�V#�)��k�>�׾���Ι�ԚF�W^��%�ݒ\$+�ՍP�kY*u�~��,��M��W͂hhG��K��\\C��7HmZ���S�Z_U��%�\r��b)��gg	q�������@@�����΅t�\rJ���۔��7s������U�K_1��t�j&S�Bi\0��� &\r���`�:�j�F�~=T̪��g�侑��!���^h�^�ו�������/[{�B����(�/�|���g���j/�d\\ޖSɗ�9���G`��u��1�M��?���3}�Q\$q�I�m~����G=��oVz�\0_p���!t�r{��^Z&��	��u�X��1@��G{���Ь�	NI������\$=0�Bu82�S\"�6��Qpj�ov\r<��ɶU�\0.�����E�M\n8�V�oQ\\�`?��L6���=\r�l�����������\"�����B2pu�&\0���5�\r�j��0V�A�����;v\0eH;��ʇTJ��6pH?/\\�H�@!pp�C��+5�\\+a�8;�\r(*��T�Ƣ;�O�|���^Ld�&/��NI�T��|#���G�`j%Ǘ��D����Z�ġ4�ni�i��4��]@t��#5c�ľ��	�Z��R�yR`@ँ\$I{z����胇��4|� ��׉�܀@=hCE��H��, ,Z����i���K��àP�|,g�z*�����E)AjknK\n��C\"J79�}4�f����*�4�65����׏���Q\\���c��M��\r�{��*�1j����lF��m�4��M�*�`�X�G�D�A-qqab���1�9R�H���b��g8�+�l/������ń� (�ʀL\" 8���0(Dc��#ihc���`��8���A1�\\�uK(�4!�������d��3�8����ш�Ʈ4�j�;�#��Ø���s8���5,ucnc�F�N��pPa8�G8�r�K��������ύ�k�i�˕4�A�	�8TҨ�26 ;*i��X����2%M�BJG� &�C*1T\n4	-#�.�%���'z�#�8�A+�@S.0�׀���II`U�Q��U�dd\$)��*]���T����C��9M*��	\$b+��ѽΑ�ydt�\0-��L��8\$��e\$���<Aɍ!�d�\$�p@]�d����&��M+2E��y߈((_|�Md�vU�9!�eD	�(��W=���#���_�'�bN�;��'��\0�O�<�L�iA�� Р�T�����\0�Q�J# }Ba(�/�u�GB���%-)��h��u����~\0�I�U��Pr�+1�������%51��ɒL`ܞE'(�/��Q�Ô��%T�)9�Or��T��],�?�<�a�	����/|�\$O�@Z��I�XN�|�%�,�SK:]ha��%��)k��P\0,���'�0J�:��	��&����V�0����jهJM�*�x���P)���j�K��R ��\\\r�u\r�(ÐW���F: k�����\0��NJ��P!�Q2 'H� *\0�g�T|����~g`D,�Ͼ\0#�	�;(\0�� ��L����f�5�'��`'���&t(��L�g�A���\0��'��ksi���&����d�m���P\"�Ng`O�&����� X@�	��%sh��g_�sb��f�5��M>s3�@T��77�+��nS��dӧ5'�6s\0\\���\0O:��NLS@ P�{;9�Ͷp�F��@�78_�l�9��\n���)�Rg�9�@a�:i�\0�vS�D�g��\0�S����\0�s�M\0B�\0�+O�q�`��>��4	 T9��7=�M�v�=q�'y;�'L�f�F��f�)�ϖwP��T�f�>\0�O�|��?0)O�~�|�����`#N���\0��>�'Ϫ}�ՠ���>�~��e	\0�?�*P��3��\\��@�͌��5\r'�C��P�� O�E\n�MB�#к�T;���=jPރ49����Ez#NƉ٢���FY���\\��\0CA�QJ�TV�������7 \n��v�0@��_��Q�L�RRc!��V�|�z��6��KKю��eS������4��\$�aI��|P��A+��.qKD-�S �EvbCO�>��H����<��\r#��LPܘ�s⥺P֭2�0� =�*��WL��2d�t� \0�!��<	�b�q�\\pa@�Rd o�fKM���p ���\0}����z�\0�����2�Ձ��3\"� �)@\\*g�r�M#!�8����dP4�%>K�mA��\$C�jtqP�9��Ƹ�Y�jP:vTu� 䆀��T��`=�p��cj��*�x���d�m\0���MJjFm�p���A�F�QR����6�F�Q�lDj���E�MSȖ�4\"\"m�@J�Q�H����@�(���h`O��f�8�>P8	�{;57�,)�䌆mSv�g�Aᓋ����|���P��d���O�x2��.�S�,᦯8���6q��,�Nz:��L����\n(%>��O� N%'��>\0�����U�9��aÏ����P�I�OH	�9\ne�5@\0���ALS!m����qv���(�N�7=�=Y�AN������R��)ϊ���4��X�JgSmZu\"N:�*4�*�,�:�	��	р������L���5Q2����VXR�5ש%��a@�v�J�򋈵a��Xv�(�u�j��T��6\$�X胙V�ا&�����H8z~�y�^�k`��?l� wuuz�@�lS~�@.\"�E�S��*���ebM5��Z{l{a�/��X���U�1�֡��񎅦a�X�Ul1�ʢ�\\����6s�����:�§��Y}��ި���e<,��s9�.!�SV��\n�b��\nhK#���l%g[;̤��X��>˙gQ�\0��ӳ�l��v�D���Au���X=B�*�d����s�aڊԕEZ�Fl~�b��{\$_�r�\0Mkw�/�~�y��|C�j���^�5�D�2%�[D��u�xo{Dڶݶg��1\0Ƭ���>���/����Z�ҙa\n�!�E� �A�d*�e�@�}U�0 7}h\\�+��1��U5��\0��9R��V�anh���m b	��  =�έb4��IO_��[@J����u�`}��N@�ܳ()���xS\0 �z���\r\\j�W 'M��w>�[��.KN��x��v\0� \$)�� z}(�Z]b��Ẽ�+Xz־�G��h?E�Q��b����vK�WQR�KqE~�I�T�5�)��n��\n�T-yD�K{��`P/��V�:��I]�n�i���3�X���^~�����L�\"(S���2���k��\r?�c��l��U,;�M�\n7ꦖR�f���R�y�ԔzV.�ko� >�b��s��(!ۋ^�=���F\0.סMJI.�Hiً8A3����� `(\$ړ\0Uр�?�(��\$�~�D/�p�a�����Tp\0�C�Z2�.,.�}�� �Ѱu��D4�X�	�p�3��x+��i��\r���Ex�	��l����ј2)0�pr\$�����>% �z3��!P�(1P�p��L\"\r�s\$��	�7%Ɍ���6��12����B��l0��|.Z(?�Dr��Z@<���m��{��fC�,a�n#>���2���A8` N��\0�U��f1�<A+8�Zq�ja?}�Fp�:�\"8ɇ9�`�0݄a��\0nB7=n\"�o��.���i��J0��G�����b�\\l:b�kdXP��ˊ<)�\r\"��K;1Udb��8��L��V�� ���7�0H�Q�*c*˙WY��X��ḳ�-ѱ�7|R��8K�ܓWtL2<{����5ٍ�>���e���{�ʟ3��\"Jā*�R�BcлV��q<�+�q̅'##>�2F0��\"X��fE�{�|c�t��M�5��ȷ>D\\X��Mg�c��	g���U'���5�\r9%�Q�W�����'�d�NOW�����#���~�u��e���Wߧ�;���\\S��&����,��}��_|���� ��!�vx� ��]m�1�|��Dx.�Bo�,Y�	�tø ��]�Y�l�/�;�YA[�˄u�`7��q���?�F�\r�v-�@٘��\n3��h�j#���K 6N�^H���\$��(|��\$����e�'�H��%�p�ğ%	l\n�K �����cg����B����\0%�{E�z���Yjic&�5�nG�g��/��Z���} 7G�\\K�K��-�Qf���pl�����㘾�w�8�a�L�*��}�]�@�R�}JQ�qg����^a<�\\ �g��C�[���PR�*4_Y�Vv�\"eq�0?Yw�V\n�ݛ2�Z����&m�2��@hn��\"&�@���H��4�O�U�,;����˘#L[��(T�����].���v��{|�\$\06{+\0\n�/\0�tI��T��Ŷ�1d�I��	����@��*96�t�Szi��Mr�숁�(���\r�ӱX�\":�A��ބO����\0(	M�wӆ�4��J��K�V���9�2jf��~��@�L\rCjyz�\n�����^�KR��԰����WVڸ�7���W�Ћ�F�5��c�GP�=n������;�&�8�+-C�\r�z�qf��T���@SАC�ͅ�5kyk/�bEŠ8}t餎f���o�H�{內����I%����a����o�d\$��qzJ����N!oP6�dF�H��\rq��iv������c���E�P�E��	����4�p)�}�+f���wI͓U�	f���{����Y0�u=�Mw�`U*���Nci�4+�. ���m+lL!�\0ro}��;a!���'��l�]bY��v��%�����\r����Z�}m�\r�'p�˷N�i��O�IW�Uay��\r�sܨv����n+��+m����dL��p\nU�\nC\r�wqc>�Q7c�\r���ۙ����BU�Ϛ�v[�7�Ŵ`�Q\$gY�T9�PU��@�v[�ܦ�����ōL�PNξ�n7p&\r/w�=~�혃rp<@e-�K��/e�V�=��*��_�a�;ڈ5�e5�y%��JB��b-08 .֦V	�� Dg�\$U�5�4�Am?@��\$���\"���'{���h�85���c���׶B�P\0����&L+C�\0P0'�0�P?&��ϐ���#͒\n�B�\r�X'��s�`!rNhx1d�B�d�ɿ�;����rJa�+�C�F��.�6)����},&����Q��/L�?�	�ƬW�k�p�`�Q,UF�5�=h�{ZO��e�W���f�����W�e���\0�ѯoPi<�Q���&\$0��7{�����J��ebw�4�,��[j?Y�\\� ]�#K�.���Z����=�\0Z�%�&Q2�]r��B����m�|<��>���/k��/J��Vk�l�\"������A�a��o���۫�s�u���o99�c���G��\\�Ξ��m�p�_R��V���E� �p���R���@\\^b�mҸ&�����g����_���.A(�4��0w���N~�K�|�x�����r(r|��'#�G���D���k���W�B�\\���[�=��ScRc���[�7�8i�1�/�m�^���\"�w}��z�y\n��p`�6(�m��\0u�#�Ne�c��T��\0x�GZ:�87W������V!��ˮ��hB�E�B�eے�/�&��,Gڍ�Bf��t����쎓��耷z&�b2֐}a׏B���ގ��܀Կ ��|\nt�\0t(��k��Pp���Wi	Ͻ;�4��v�Nt@�q�ʣI\\՟?;��=Wt��V��H)\0'B�q[1�v���3��z�n��#��N�c}I�S99�|#��3�\"43��p6�8F�'��9X�}�^�ov����e�6����C뗎�6��B[�9�B��1�ŵqN��y&�rY\\\"�\"�v;���p��=�&�a\nT��\na����C\\\0002�D�H�0�<R�������\"����R�c��=.�7��U��|FS>i�Z1Lƾ���_O��!!�D4�.f�e�����౷,��Z���@�\0<F.�����c>_}�������ʠp�C<�I�=�Z05ь��)ގ������Nz,u�����\\p6ޟy�-�=5�!T#G�\0*m�����Inʩ��������:�*���i�`7�h�����罈���EB.֜Xʉ��`U��q��s����sl[S+{��	��0���{�7�<p��n�͹��.����]x�m��b�}���<�[ݻ��W����Vh��kY���>�e�5�\raa�F��Sx̶�OcÐ����[L�e�𗂤 7����>@����+|���;������.��@Y'������>E�@0��@@��H=C��`ր�b��-Oz�m���t>=�=��3n!�����8DM&��J�w���.]\0�O?�[�4D#~<�O�����\"�	o�t���BcLK1�	�YĉI��x���8Q��e)���`���ǐL�6�+�جB���wbo�L]+VP����B�E��幊�_V���x�r�|[���\"_~����HG�sp��C�\n\$��R`��Ǯ` �\"�@F?�	Q��q���^��`�W\"�;�\"IPD���ǆ�2������oC�n���}s��x�2��i�wc%������\r��N�\0I�����C�8�p����YP!�)�F5� ��	���c�.��o�7dZ��`<*7��������4w(�O�r��0n�������9��'���t�X�6g�6�\"�V:������V�`\rZ�Z��A��&!�_O\r�O�D�	���� ,��34/��o��m)���#}����yĤ҈[n�4\0���<D��.���� B��>\"\r�C����;��@�������\0��D0�Z�b\r��V%\nRRA}����\"�.��P��>n1�4�=&:>�\$�^RMpQ���\$�L\0�1��𥀔���}���m�H�B��	��	���Ah1�V�=&�L�	A���T�!�\\)^�2è.�W�p.�C�Qؔ��\0�\n`(�.	9�@���`(�|\0ʦ�>����#�@2D@֒�\$�E\0f��r�v\n�*`��.�W�s�\\�|�>�H��*��Ar��Q�j���P�;�D<|�I�N��0UAX=)�k���X0��v1�ݐ�A�U,#�DAc@(P�AZt��p[A��/����	0�0_B5Ik��4�d��bQ�g�T1��%{ѩ�=\$�PI=KH���\"0\\A;	aҢ��]�&7A�\n:Y���\$)��\n�.�k§	�Ps\$�\n�Rp�����+Ј/�'�O��#\$,𡥰*dЀB`� �B	����AHTD1)I�QtLN�C�=\0���e��\n{,���<4B��Ii%���\"P�\$BW�h%S�P���30MAE\n(�p�A�|c�)���\n�#Ǯ'\r8�X'�[����5C���0��B<�BS0iBf�L�x	�1f	��@�ДC	�;��C�U,8�b\0ޖt)\"2A��iRC�\n�/������:��ì�;	_�X(��\rC�\r�;��C_(��B�,-�dC�\n\\9P�C��.��=B���-�t�]P��o�\0��N	��ֳH�h��^���F@�/�<��\nDj�`����れl����|/ !��*���G�3�^�\n@��'�	C�����(��H��'&��\"���\rLF���2��Dx���!jD�I@<�vQ��0�� ��C��XH\0002�^;i�~���TH�6�5  C��H5(��49�I �,SF�_TS�F�B5\0Cq!�`�����\0O�P�DEW�Q�PEE�?�Y��<S1]EY�Q�_�H��KX8|X�\\�7�QqO�x�����,G�J�\\I2��0SQ#���a\n�,<\\� �=\\]�+�%�I\n���\\��\n�\\FiTĪ+�,D��K�/���1�\$'�Z1T���U�PE |R@/�({Q`0��\\W�E9�Uщ�+�c <�9DX�^E�|R!lF,� q�\"�H,d�bŧ<eq�F]�ɎFk�UQ�FClRq��(	���D�g�F!De��Fw0ܱ]��le1�F%\$e�Ÿm�M�]ğ���ۓ���ۀ�x��x\0�����)�Xm`�[�K`����]mP�:i�SM=@9�pSFĘx�B.�t��\0�p\0�U��\rNA;�����h��x~� �w���҅x�8����s`�!_�k�\0P��1@�\$4��w�����1��nª��*\0�#R��0?�	�'\0d� 	��(Hx�G��\$��\0�H�`(����x�GD�tH�!�\nɁ �Ǟ�FJJ|�6�>?�XBQF��Q�\$`�\0>06�T�\r��Z\\�:	(+ �FJ�Q���df`>\0��d1��h��P��pK�\0�T�@0�B��H�>5 �i 6Ɩ��j��B8��Z �?�����4 7꼀���D*�<l���H1 ����7�5�%�?leQFȝ�\n`<�bĊmr���d���\0�\"����ȼ9��1+�V��G�\0ɒ1�?#l���H���Al\0����̈'�3i����BD���٦o��c��ZX��MJ\\�#2�mbJ�\n�k@�G�ikQf)(&\\~�k���������!*E(�B�&�T�-�`�R݃\n��\"i�H�\n���\n=��W��<����@ *Jz�7,�z��!�&��\$C.�H��\r��F�`+?�������t��֟����u��]-����~�b%x�d)�\"|^BW����L3S(\nb��#�A�����R\"��&H�CTؙi�W�f��IX!q����D��	^H�T�	9(̚��\0��҇�����4)) �\$��~0�6#\$�*�t��#;<�D��ڤ��Rx�}*#��3ʐ�g�C��c4������`�@�&������[��#�da?��Y�n�;��x�:L����F-�C�_���rk� 	�\$��2��#\\K\nH�+����\r�T��;�+�TR�2����\0��r���*D<r�p��!@I��Z�ҙ��(��o���ChO��넚`�����2�\0����,]��\rx�@:@����\"��(H+R���-ܡ�텱+�b �Ib(��aHH�# I@)�ҙ\0��X��.@Ɍ����\\�i���̵�3-`���������<��&��� �Ҟ\r�#�`ҍ*�H\0�L ��K�.	i\nv\0�/Xe��0��\0��/T�@6K�)���\0��́�!��c�힬����\n5��E�v\0��\0L�Ҹ')�Vҍ;:&X��@�,�\0�Jo,ळ��1L�b�ʎ��s�(�ǓK:ɴ�b#I�-؉R�J�\"@��J�W�s�:��)Lt�ʁ6̬��*����\\������;�!F�9	0�V�G;�,�S����o]L�3�4�:�53�GL�1h`���h'��ÿK�02Cjo\$N|�HY�1��#\$��3��I�\"�Ԛ�KJq1Ժ�J�0�BW��1\$0���伮��\\�\$&�T��Հ�L_5\"p4�5H��H�0�ҌMR���[��6\0i7�~	\0iB͇6@#�͎�����@`��M*��M�K�LF	%A�Yh\r�!j��sp,)-(`�,M��}�#>�¤Q��\r�Ԍ�WTҐ\n(�M��A����i�l><�ދs�{(H\"�M�萤������i�SN�� �M�\r�+�������6zZ@? 7M�9\0B�����K�����J��Ijl��q�*��RM#T��'�n�쇒k)�9��!���\"��D��������/�JO�/�+3�\r˗.P[�H���\n�\r%#X8�m����1�ZX6��6z�xn\0�HT�|�J4ms���)� �K<�̜����l�C�����P��.�\0006@\$���\rKH�>�K�����;p5�D\0�;t��-#\$���I�0��f�8���3�;���3ʦ/=@�?g-kA�\"�i4H82a��%R2+�F����pa��@��pm�I�>\$��*?�����(\0���|�\r�ϩ5 1�(\r��2�˶`�OqmMJ�\\��	�>|���O�8��,:�8��,���c��r_����Y���c4D�&�\0���d��B�e0\$�T���~S�&Z\n��R�\n	� �K��r�:�<�����%|�����9��oPM95 6PQ=������:�5����1�@9�m�\r�E�`,��\rT�m��F	P2Q��+}�3�^f� )�Lnd�`�\0�+Ь���\$D�R\0���'��J\rd~�j\0���қ��:\\��\nJ�[�?: ��L����:�/+�>(!%1��f�g�/�;;3���v%|�t<M�C���DLC������Jo:���� �\n@9P�+Ȯ`��ջ���/P5t1J0 O�Ȍu5H�R�MR��ղ���:D�*�ȞD��T�oD\$�3�6z�=�T��FT졵�R\r\\��F\r@�<�)�o<S\$GL	�\$t�4-�XZ]Έ��f��2i�s\$Q��%6���+G�cSFz��4+LlA=�W�.lG�,��\re��R\0�˔�)�3e�Qf�P��V���.�x�y��H�bDI�H�x�{��G��4��G��ԎR\0�� T�R\n�%@�\nKI}%T��S3\0��M��A��EI\$���Q��M't��\"E'A��I�(��;JH�b��wH䂡��9I�T�G�J�T�\0�J5%��QgJ�)s.R�Ju#t�RE�%Q��q.��c��Kt�4����`���˂	U�NS\r'Ն�F�E�6I�K�(���\\\n@%H<��IL��<�s\"(%@(L	�Pq�\nC:aR��L�3��&rC�&SFZ�Y�0�>�ĸ@:53Md�Ի���R-4x 2I9G9iaGQ�g����)(�T�\0�FQt�K�	LDeӔD�kq�A�rAN@w�DB&5l;�ۉ8�sA\$�,h'�!U��\nA��\r�`(\$#@N���	��\n�Jx�04����O|�o��\$�Y����D�\r)Jƌ�Ϡׇ�\"(5����B��D���'9F�0��`�I��5��N��c�?E/���`=����K�0!c0\0Ҽ|��\$�H́F��0I�FJG&�\">��\0�u!�p�4m��\0F�r�L%HO��D)A�\0@�bX	b��d�/A�kl)А��OU�@6cM�b`�:���U:�ӲO������8�I���\ng#�T�� )����O\0�\0�`	�\0�(�i���� \\��D���\nu>'�T�� �?j��b~`&\0�`4�IBu	L�SAz�C�����i~�8z`!��?R�K-%��;53T�eL@�T�,9qU\\�� )=\0�D��A���`ɏ�<���&�@Tzc���(\n�;�\0��4Q�U5ԩU��+Բ�Wu.�KV-M5��`'��IV�s5(��e	�alUd?p�U_X\nZ�YyU�J�t�sWeK�^��[\0B�	�_f�MWJU~Cp��_5T��2\0 ���yX�;iPB̕�'qD�W-cQ9�*�LF�^��>p<\0�Ć�6�f3\\H,���YQ��}��<Ju��Y�f���%Y�g`6V{LM�M4�)0�C����j`9F갡�լ�Z�x�YV�Z�i�<�oZڜ5��NH�j�OV��m����[a�HP�Tmm���:j'\0<V�[�^ռ��0��!?\\ou���[�g���^mmUƀ�[C�u��Dmj��V��R\$��zga��Z�sࢫ��n�\r��&����+\0�.��]x�̆�ɽvc@Wk[�u��\0[[�P݃�]�v׉]���Z�:+�V�ey���������^�O�hV��<U�L�^�tM�\0O��	%\\���\\`�Q����[T��5���|5���]Aj�2%[S���\0�m��T[P8�o�+u\n�MV�UA�4)\\x<5�W�&�Z����)4�U��O�\0�]��,��`�C��Tppd��? ��7\0�\\x�͢\n�\r����X4>}jJoXf���W�S���J�it����b\r�v�n����ɀ�a{4@�Ta=vf�\0���E�a5�j�X�a\r���س`틑u�gaE��jX�Ռ�mX�`ŋ�Y�v�h]��XI`͍-��aѓD�\0�݊��X�c�V5�~2@\r\r&��d�VA��c݅�Ɔ�c�6G�]\0�5��D�vF4�aE�VLW�b��JX�aU�6���gi^�MdX���H��o��YGd଱�ȷe����X�e�vY�%[UgC�\n�N!%6\0G�0e�aM	�(�g�d�UvS�9d0\"QyN�_���g�Sf�]a�W�3�+��af ��r��)�M�Y�[�xa���!������zR����AY�\n�v+4Y�g�9zL�ݠv���6\0��n�mVx�iwu�/�\r�U���b���P�#cꝖ?�af�v�X��U����Ag��)�h����He���Y\rfxCR���i����Y\reM�`2F�eͧ��V��*�Y�i�]aTړbͨ���j}�v�XY���fکj]��uٲ��6�X�j���\"@\n��B\n��E���X�	����j�gv1F�}� 7J��+͢,\0�Q\0�;`B��Z�m�k��Z�~��bŮ����m��&��l�V�Y�l=}���c���5k���5g��`�Y�8�O6���lղ���-d\\b��Z�l�]v��mv5��m�����ؐ�\r�#?	g�V����T��@�)�]��\rx��k��>�k%\0;��d����ٮ�M65bL��X֭��SshE�֛\n`S%h'��Z ���kZ=��5�jmf���Z|o�Q<|`U߂(�76,ق�:��֛]�dpV�a�����mm�!��l��\$�E�p��\0�֨6E�Ad�������/�5u�79�,mi�Z�pzȗ	Y�:�Ģ>����b\\ip�77�f�{�q���܃p\\����0�����c5���4��6��_c��v�\\;d5ė+\\.���!�۟r��5�ܧoʷ\"9�h�	XQr� 70\\�q5���3Ir�9�M������ �W�A]\r��\\�t|�o\\_r�ʗ!��r��WF�7s���6]%tS�W>\0�s��wWOEj���\\�m��hZ�Qʣs�WS�9q���]_r`CW'�^u�7\\�]�ժ]qn-y�(Y�t�\"�^�a�]���aVgrmط^]�sUή����O��ݣZ��w]W���wl]2����XZ�tu�\0�	�tl6�օA�NQ�VcpͿv��3pj �P�vd�]�w%�# \\���<^v�{����v5���]U;q��Xi�j��^���~W@��l1�S�R�v����>�\n@���`ɞ��`�,PS�f�X*�\"��V��#��?�@��B��W^|�E�\0\"��y�	)���z2��\0�@:f�^Oy�3r��:n)���h,��D�\n��.�yr�����8�C���!^�z=귕�ƻ����f\n� ���Qc��q\"�\n�,���嗗�\n�'���!r��(��«f>� -\0�y�n\0/��L���/���I����\\�D\0�>����z�0��ս|��7���y�����y���_\\�U��^^\r��Ԁ�{\ng \$�	}����_c{����	S}�*�\0���8{}m�#��{y��ס(�z-�\0�{	 (�z�`&^v!r��TzW�^�Zg��\0�~E������n  ����7�ލ~��7��[}���_�}Q2�#��|f\0��_�\n�n!r�Ț����ެe��^�{��`�^�}���^��(���^�z6W�`~m�7�`'{���^�|��@��}���'_|]�_|���~�	ע߽z%����ꚨkX#\0��7����a�U� kC��������\0�|��\"�=��8\$ǩz���ˀkX��yM�7��6������}\r����zv §Ky�P�D��z@)����uI�}T���'ʞ�	�U/��*ɛ�n`kC��y��8N_���	�*��������y�z7���}MZ�#�\\%VXU_�	�uar���	U�N���C}M�W�a�Bz8`aW{��EaTv7ԧ�?2��ea@��X,^ܜ�fU�v�Ea��o���W���W�an�I\0���?����Hy�\nW�ǩyjf�r��?e�xz���������~�@_� �O�%���&c�T�?�w�M�}=�U<�g{@\\��'�y}�J����	x�&oS�\$\0)�M��Wݏ��Y\0�U����� �	�P�{\0���&��a�?�8���? 8�__�E��>bS���i��Р���?��HC�D�?@�@(bY{n���?��*���oy��8��\n��j��/�\\�bߪL�*X��m��I�ق�؀��Fm⋈>.��`�~/\0�\r��/��� �jɬ&z��R��=Ox��ޭ�f����`�������w�bӅ.J�.�X	8��\rTw��*{x��yDzx�ˇf68��鈖6�r���2�����XLǩ2����b}��%���a��8���QV�N��c?�b?\"t��b7��p)�_ܚ���m���~��&�?&9��`�z�* �~�P5A�B?�˴�0?�\nS,a=T�*�4����w�����x=ǩ�����(�w�U-�Ⳙ�����H��X*�.�L�T�&�V?��i����ⷉ�B��G��\$y	d3�FR/���ND��aߑ89_n����\0V?\0�X��i��2 *du��5�^����!�}V��aϒrj�*�C�8n\0���f��d���p���ݒ^I�\0��'������M1ᩒ�\0Y7d���/��d����''��\0�w���)^i�c����b�{�M\n��������_��1w�.�&0a�UV�f�B*�yB�	��K�\0\$�� �	�eE��[�oe����Ĺ~&X�AɄ��Ab�T��b���Iب�?nB�\0�^�B_��X�e��\r��'�ݖ2�	����e���/@�;��*������DU=d��'�i(u�����_O�X*��_ƺ�T	���	�J�˒�	��嗖[�jT�{�@8���7�����\n���0�\0&�T�7�d<�zc���kI�*�S�[�leZF* *\0�-Z�\0��^i�fG�.1i�fP\n�g��\0��l���ޥ��0����2�e�ۀ���kY��/��B���:�����dJq�Kf�?�g�L��j ��{Bj�m��)�f�.e͜+h����8o�v\n9-����\n�֓(?p\n�/�\$?m��_<��%9�����Nק���\"�/ǯ�oi��H�nJ�g�x�!���oٮ\n�(�_\$����B��~��|��͜L�@Hy�U��*zX��5�rfND�	��N?�5��'�>A�=��?#�a��TE[Ra-�N�S+q�����-��z5=呐ҹW��5�\n�8�bx~Y�c���ձ�fF�,\n����)��|��Ǟ�g	�+k���\0�H�*@ ��@Vy�����|ז\0���[���~�)c�b�yF~J�,?F�X�h��zq.����V?(�yRU�V�y�+j�\r��n�?��� #�V���h3�h	��\0i�f4�*\0q�f{��\0��>��\"���bjX�f<>����(b?b��'�zH	\0/�z��)�(b�~\$�՟�}�')��~c��͢�������n+�bp��.鞨b�\n�i�g��\nhs���zh`������f����h���L瓢2�j ��z-��(������h�L�����?\0�c���TY�⁤���͏�J܅@\$��\"f�dg埦�9��{*g�TiW!S�9QH���_�C��.��_E�^~x�������䡦�\$�C��B�d'>�	����:~y�ã>�n&p��B�-���c�_�~�{Xޏ匎��k�68�򀉃&m�R?v�m�3V�9�q��0	)�c�#:�#�d������  �	��[��iS��}����Vu8�US�<����N���ynt���{�j��^ِ���i�Ϩ�O�'���@#���ƀ�\"��gY����c�{撣�_թzp:��?�V��(?�jk #�����`P�~I�+kV���U'�*~Q.^��U��`F�����e�]�8�\n���قj�����aK�����ǫn?��'p�µ��ga����������(e��v�Շ��<�1>���9P\"@<ш����\$`d�9���g�\0��F&�\rq�\r1b\r���\$;@ڃ�-@��I�L܂�TP#�;%��aj��\r}2rM��eL�aR��������Ӄ��L��3I�����k�ard�k��zs�#L�IS��V�z�\0ۮ�i��k�B&��ψ#X.����l���f�{F :�S��0�3Ζj�`�W�濺������k�Ce��f*8�i|��أ}�oY�㧘��`\"\0�!P)��!P����d�\n͜hnFKc�^݈H����́>�)���	�����R�&����zXK����Plq�UR���F\\��u�BxR�O�F��H�v*}��lo��;'l������l�@H	)���J)�^{�%���D�O\0Ø��>�HJ�0�>�;���hUTBP�#��{8��\0?�:kqIa��]��Hк��H�{4�Jj]�\"��&���F�)\$E54�!e	��\0�\$����J��ޕKx�0���C,�RAK/˿�0��%�Q6y���r���P���،���\\���%��R	01��Έ]/(8�d�F���\0ƀd�ؙ�^�TۘL)5s�L\n���m�G#�����#\n+ 4�kM�D��9]N	�{R��*��>\0뵈5{U��kA��ƿĳA�9�����T�����T~\0�O�\0asπ��y���*ȕ����\"|r��Oc�y�:G�	��rz><�D&�\r��2H\rC\$������rWm�)�R]��eI���k�Z��a)����L��0���Tؤ�&;�8��mI�.�}��I�E����n�+4@�Je����MY��򯄔P��1݂Y����\0��IT�/����*�Mha4z�\r��[�낉���R��!on�/v�`4TDQ��VW��T����M����)��&�ۖn��Z��ϼ��Ik��}��Vg�Ƹ3��#9^��D�Q9k���k��f����N�\0�o9JD����g���Y������G*�՛�n���5�����nú�����k�kB���E�{�RHkZǌ��<�F�Z�=����j�K�/%|�3}��'R���o�')�Sۖ��i@6������f8n����VZY^�\0�?�8��o׷����W�\r��M0�\r�� ��Xb�ށ��LL\r��7�0�rDW�l���L�T8��0�k;����K\"+Q����\n��\$��u��y�=v�C[��1��� �%L�m���_�� ��6�	!.���[[ʵvL��,�}��	8�raxg������%��\$v�#�5a�����6R\"��!pO��6z�}I\"��>�ҿ�ܙA��O�q�9M��l�k�TԢ@\$��ȋ�tF�D��mb&|IDl��@�Ipo��W9uرF�5A��[�۴�&�Z��n�I{��8����5qe4��Yl�¬h�����@�qb%[y���C�c6�T'�6t�\\Zk�����0�����q�)/��q����:��;��AE\0�֫|qőƶ��Ηf�@�F�6�:\0��3#\\F��Lcn�m���������b���(�-�Q�l�`2k�#Tm�G%{�}=��k2�'\"�9S���2\"��+7!!,�.�|�#\"W�����U	�\$7�>�frP�&6�k�m��{�k�l�'Z������a����r��\0<��5�����8@2/�g)\\���ʏ)��k�l�'6�qO��\r��r���+�5Љ����r��E}ܵr���%|��ˏ-|�\0���*<��\r�t�|�r���&�<��pd�Hs�����S��D�����0:�I��)<���`kB�\n;@��WO-;/�x�N��5y(�N�E���õ�y\\	�P&��3H^���w?P�6f��uC�.)�v�ͼ��c���#�Y�u�'��!Z���\0�¡�(��p8������`0��,o�sy��QE��':������������W�r�qO���r�s�!_�[�Vaz#���:	zM�q	�9��kT������� ���w��R�2a	�W��������u� 4]�\rf������\\��\ntC�\0I�����!���Ϗ=P(�'��C\\�s���ӕ��M~#�D�(!�+�����X�\r��]��gI��I�|5��s��_J����GK;\\vN�¶�t����lG@Wm�D����OA7m�8 \"\0���Q��|,t�� 5=3��Գ��iU��>�}E7A#)D=�a	���\r��{O��BOQ2+s��:���g��=J����6u�M�O=��t�	�>��i������Ѽ�\0�b`�Y����[�Y �\$&��i��~���\0�e��\r`1bX�����g�5� ��bG)�GD{���0o`7S\r�?8�%����ѝf\0����qu�X����ͿY%5��y6�pe�8�脀�0`�e�:8xO����9d\n!.��~F�=jo��%����O֣�;PP�֧V��ᬐ+F;V�d*�rX�Յ���u��Ѕt3��Go\rQ�g\\��u�b�>�qs��1m�5�LrCt�҇`��vֺ=%�rK�vo\rgK`���?[Q�K4��9�|��m��Uڀ<���0/Z�%�5�8`���5ïY}b]+�Y�FW�\$\\C�Q5\0q�bp�pj�u��dн�u���i}p��#'m�W�8�oFW���qQ|1T���otd�AG�\\�&&07�Eќ��t�}�f�)Uҡ���,l�=ʂ.\n�uv��n�=UW��p�y�!�s�%wu�������+�ek�t�vvFW�]���wU�<���É��u_ek\rk��	���P\"\n�Y=qwy�`J�4Pj�T���5���qG)gp���R��gnbp�&җzP(п=��7w���I\$��!��X��v\\Y]�{G�ߠn��]��]�����;'X�4Hy߸Ϣ�J8'nrvg�'p��^({�>	\0�bW\\E�v�H�qV�,�y�b����^P�\rЇ�W���m�f��l�k�A>�(�3��7R|E�s�#Q��;Fr��2xresQ8A�@PtR	��]bmq �uժ�ޕw]qpL��Li\nEօ>3G���y������}�����N�lw��<��3w�ݵ�jF�0p�*u��,}~,�h��W���XT��s�8��}qs�/wU�Y��HR]�\r�Op���LQ��v�C@֘��s^<�[3��T#.��A|^��j~�� �\0����`i\0�Ǟ�i�+l�d�xMm�I�<�H���OI{C%��h�`t��wu%�Q:4��\\8ݔH�u>)�/u>�Z3��@�oS�b�@��/Ѽr����ןx!��v_\$H����(��I]��3�=��Ii�O�q��Y\\�}BtG6�\r��p]Ҷ�=,lf�\\����+�P�ݺ�v��7��αpDv���/�y��oe��e��Y�&�ba�9�#�O{S��i�wo���TL(�ot�^7_T�[�Ηi�N�k�]�����D>�	�謑A.k) ����Yi�K�m&W����Ɠ��xz������M4̂[�I�%M�g���wX1�7J�؛�N��mz��Z]��t�f����X�-���ui����͞��pd�<�ə.I��d�M.�66(bUv���\r��z��p���)���O�(�N������M�Uz�h�Y2����g��;�?�<��M��<�Z�?>J�q짴>�\0��D՝�TEߗ{b��-t�2g������w��߹�P����%/�B���o��T�{�Ӄ\\{�x��Z�Y��K�Y��[!.�:�ހY�(��.���h+�)�]|��Kv��.{�������=��t�?gK��z���4w��>�e�l�W>�ƾ��?P����v��b��냵吤�\0����i�>�>wu�;a6���k�����)�?Ž�mdm6��S��U���anC�z^�!�4�=���|~M��?���K�?#����O�O�M�8�3�ۯc�mN6bgI&tO�\"�,\r�9=�Ap#�'��^w�M����)�8T%w�v�^���<��Ŷ�\\O���}��\n��3�?Яh?E�Yє�!�����EaQ���C�3�o��^���gA,��WE���1�0%�XK4L����.�	^�|�)�o�auS�o�=��i��?@�y4w�4>\n��M���[�lF�cx~տ���E���}��2���:�4	#����,|���tqa�^�!��̘i\"�..��r��bg\\�����K��|�#g�}v�YX]!w,r�^�v���ݝI��H!�;��q��J�긒?z�Wח��{����������`I���7D�}��\0�u�Ә+�2~U�,~��~]�O 7ܜ�3E�W�_K����:����^s���_��P~=�ʵr��������`6��-T�a�H���	t��OS�1Hyn��\ntw�K���=���p��&��?�vu���>��M����2n\$��?LO9_�2A���ⲓi��I2�|��WK�уw]eϿ����u_�]?�������=�]�Jv����\$��E�U�#����ܷ?�Lu�f��i������\n�l����pm��X�>t�f\rx�������s����.�*O�(A�P?4��]�x\r^�E8nJRo\0¦�%[�\0004��w%_�K�[SJ^����n�OK�VU�TP��ޅ9܁����?uF��S�x �D��W��&Cx��r���;��.8KyUB�\0CJ	��!���HeW�J�If����\0���o��/��V ���5Z\0����U�K�Y;E��j�wD����%`n@��4FI*o\0`��� �V��q)8c5Wz�EF�\0��B��~��DF���ŧP�A�Y��A]�f��*�-+Yf<��ŷ���/�_�°:DP�y�A,�m��b��q��8��X�Hڷ���\0�@u\\��!��d�^Oڄ}%�l�>�׊�V��mY�\rڽ21P\$�5�rH�D����H������h�	E�e�!@�[Z��4��(�`QF5�\\(`O�*@9\0f�D�~k� \"J�Q��BD�\0@\$�DN���\$��k1�	�}`(�R�|`P�����6\"KX��5�*��\0Pԙ�[(\"f����y,�1\$��;\"8\nL��YX7��ƞV9�gX\ni�HapSI��Y�,��!���V�3�����\0�C8|�����,�N!�Iq�j���)�����҈ZX9�+jȬZ��s,FI��g�Ÿ�K9�Q�I˜9c٫;M�[����0�U*�����D��r�0h����R���c�a��)�L�&pG�F�C_\0�@94��̄�F3�.��}�{5f^�T׿0�l������A���2��l���(+0Y�����ь�2�,���x��ȉ��B��]��vd-ݟ�0���ނ8�m��%�g=ٓ�&�-�#�,Z�:����c:x/�e��9d��͍Xe�`@(*݃-F��E�����e��A���.�`��X(<�\r�<U���UD�J&���#I��\rF	�/�cl��~�����Aw_�˨�l��Z��A�\0�����20Q��A�'�W*��xY2'���٘a\\�<��	��A��ԙ�P6tpg�\0B�&U��>��A�W���3v/pz�7���\0�V� :k�C�A�'�2�O��[\0Eex�Q)[;�	pQY��bR�9���b��Y_&8pA��#\$0]!x�@�͞�\"�xP^���2'��[\"qPh�3��T��|��a�[b�	\n�F���H Բ�B\0�\rXH���&�τ���<'pH0� �ma�T#�]����0惊�U|s#(LLO��5g��x�hE,P����i�	��Lv]P�!<V��	���8PrڃA ��<(f�l�!A�h!�x>�`��5��}�2�@S`�0�\n	�C`	��!B�\0�&	 ��0�Q핈�9��94ƙ����8g��I�c\"z����Sc\n��D\"�)�J1�c͋;X/�~Y1���~\$\$1laa!������\$�G,aa*��^���f00�aLBㄱ6#�0�X��_�pŵ��\$��qcBhdb�B	|h9��!bBq\0�O��+�N��a=A�.���0����a�B�c\n�(rpz���\"�.�>(X�}aIc�9�,)� мC¡&�\n� hd0��1I^���d �)�aXð[c��?95�1En��'�VͣB��n0VarCc4��K2V4�\"��Kl8��?�/�<,˚L�2j&�ؚ�8	k�y�݅�P~F�,v`1�_Ӎ���@�ACU��ETiCf=�{X��Zd\"¨�(&V�װ�DfJV�����]��胓�; �k\"��B;`)!��!�ql:��m��)T\nz�\n\0��C�z��c���)˔ضK���[| ��ټ�\0�N��i���Zv��陑[Vl\$�Y��*fHU�+5���ҶZe����Ws\\��k2��-K�ֆ��DG�7@l��ˊ�f���5�Xf��Н��c���f��_����:6��@,�'<����2�Xس�l'���<��z,I�B�g����\nFx̯J�i��C<�'�����N\0������f��3s�(���Avop�\"\r3qgu�{8`���KhL��yc\$mYҳ�h �9�\n�Ƅ�	��46hi\r��{C�d��J���h�]�)A���K�3�i�O��#FR�,��ߴxf4OA��R������\$i�Oɤ	x��C�3xg\0���+9��-R��.������G��ݗ���(B�ݣ<G \$�\0(&X`0Oɦ�����`�4�i��,��P(npW�'\0_�JO��?��p�C��Zj;�?�Hf)MS�O�&�����Uu�ڮ��d,Յ<BR����r�g�Ε�/E[\r^W��j�١B#f�|�%b�n��2\\�IV����z���%�TkT\r7!5�(t��F�)q��ˈ�F�%����̔��c�6����6��G��r?&�)����2<����H�7�����> m��)�K��`܏�6�R���4�a��m�[o��G�ߵ:�Mt���Z\0	D�tF�o�M�9=�KL	q:�]W������(tP�7�@�B��Mq��k�M�Ts\\Y�2%��?58��6��\r\n�M���ὁ��·ok��N�]��o�T߾��t���J�Ms����J�!{�8u-�]�-4��*Xh��1Iۤ����Ӏ0�c4�8�\0�9���% �&�R�v+#n�7*�\re^:��ۣ����?���ޱ,�]��wL]�5ȠY9��Io��(}> ��(���&`����	^�Ŭ�_�0\"{��HN'��_�2���m6��Bb�\$~���\$�v�����Ցj���|�T���'\r��E�k�Z�g����Q���丈�s�G�=�*SS�r0��1q_2<1��\\8��p]�����X=�R���K\"�%�p�����}��Q�;(d����1�D��\\E��G�Y���ĽN�:��\r�d(�`�~b�vp���TC�1\\]�VQ�TM�<����F^�:0@0�#�yI��@g��g��\0:8�N�	�f�A�\\O�rn��/�����n^�=���e���	1�>�2tr�)-�x�A�c�t`2���c�	��R>�Qm5Y�%7kq�\\�lCX�Xl�\"Wc��-9���r-K�d�����L��\r��1x�p_��Pmn�^3χ�VB�?S���3\\dŧ/:#1�����z3|gh��H��y~[632h��Hk�<�\\��Y�]1ԈkF����(jѶԡ��DƐCY�ti0�q��E���f4���E���J�\r[9�4�X�6�Ձ��]�x�t�Hk��}�_5�zxǲ�#\\8J�T��P�GQ�_��\0W\r�5���K�c�č6B�6C⤹�C�6��YPBt���P�'lW^S�6�m�f���w�8������`��l�tep>'�D�V�x����jDoↅo��pM5�oӯ��5�v��exK�����\0<:Kmt�\"8\\9�ᮔ�@� ��8�wB�A��W�3m-\$p���\"��.�I��J���N�ĺ����\rdg�c����T���n�r�\"O�EE\r~;�� dP��&>O��R: H��*�ɿ4o��TtU�Q�E\rE��*:\\\\���\n�GI��ݭ�Jbx��ԟ6�LQ���:�h�qң��f��? ����:�\\ѓ\r|��W3����7�6�t���wתK�ջ�!�|	c��M������a���7cǁ,��A�߇#��#�E������w�����G��-V1��v��4_ǜ�f<��x�.���)�G�<!�?1�m\$)�qB=0d�JOJc��������C��g��_i:\0Kܨ�Ia�jg��F�@&�Q�fu��2�'�-	M�����c�G�wI�������V�=�[?\n+s�W���c�\0e�d�y!LD�Q��\$)SY<�*��.��Mqآ�Nʇ�o�l��%l1�6�_��q/]Ѷ�Ip����1DR�\ny���(��V��f�\nY\\�鵄9�����P-��	�;uV�KD�<��0��N�@@�&-�z���+����Dd�+nl����\n@J��*l���7DM���=q�\r�!sqQ�¦��8^xP�T�a���I��\r�7h��5�ȣt�`yN9MA#�d��ң�@�Nܢ)�/\"T&�A��`�\rig�!H�Nҋ(�]������\0�ϟS�.����� J��� ��O��BD��w	N�n>�y0�)2�u3)1��\n����0�0�/y�ہ3H�}�Ԋ�n��ȯ����PLI`�dZ�}�iO�6����S=�{B+##_��Kg�~?���2�Q`�S��'l\0��![�Ƕ�R�	�ב���Q����@ݸWI��l�Ċ��p愤ǧ]v�|�(�O�_��Zu<�E�`Q6.�h,�&N�]3K��n�[ȅ�LM���Nj�:�Q���*��YxG3}@�6���{\0hbQ`�	����kNS�Ƕp>��4c�0\r�c��v��=R8�e�@I#L�=�k֨�(�G��\"ҁdav�`|�s��ԒU��	^��+TD�.7��D�2T�=t��.*�]�ϲTco+3~��!���g����MD���5*��G��u\$�t��Kl�J\$R�� 0Q%1\rc��!���>~9R�D�Nϝ��xT��D���IgD\$D_��	�J#���i#��u��������a㠫��%b2[���R�[7�&�U���f�~���)z��Κu�����|�\$�*������Wm+}����Os��R@�A��#i�Y+�##��U�ڂN�d�\0�_�5�	0�6�F���A����)'ln�Y;Oە��w���m���<Rw߷�u4��N����vd�I�Qm'����;0*�\0=���,JS��?�*�:�SJ0�~IIA<A�d&-	&����R��j@�59���+�C@�}�\n=cC��A�(��<�P���OX�Ɏ�Nkmn���M�Do��[�0!q�\0��E\0���9���R���=�N�ݘ2Z'�5\"E���z)-��o�����a&g�����2����Ǿ<�TқV���QF�,x����`%G���I�w�\$���)��_��uS!P뺈��d�5ϏQ\"O��F.�?�||\"�)d�PJO�5�~�;*FjLt!c�J��&����M�ޥI��2�=M�IB�g\\ۂBuI1՚J��3�?*�c���aU��!u攎U��&�Aw��F�JH��R��i\$�ց���~�	C�l�E\n3��Fs���P����8\"ǣ.��'.T�|BQ�٥>��s�2�4S����I�N���\\�W���%r�u��&��U}��k��>���:޷��=J�sn�������v�W�\$Ri��RJH0Ɂ��CFK��Hj�3?�\$Kpz\"�\0		.?�1,�������y?�I\0�����¥�@��X�uV���Uc,EXD�Ea걐�\"%D\n�<��Yed� @(D�\0�Xȇg��ѕ�@�V�,�Z 4�e�B��D�R/�E��`\"�0��-TC�u� zA\"�+W�-�H�`���Ae�\\wJ&[<�`ê�%�Kh:��aĶInF���+���0긥mK�ƲXP��[h0�9�qeì,�ڲ�\$��p��d�ˑXܻ���j00e��_V�\"Z�C0+��eˮ��Dz��\$��M�楥�E.v<�	t���+�Y�-�]Q)0���;@?_.��0U��_�#����T�|��\r�\0T^��f<0W-؇������eP�!����\r #�2�?Z1�c����[�Á\0��+�,�\n��\\\"\"��Q�&2ˇ�i{�J�DW�K��2���J�%�l�\0000�a/ŀ+5�s�*�{`��6��f\n���CB�-06J��P\\\n �h6pҌ��@�.�3�3�gJ���#1P���pgZP����s�P��^�Jg�N K�I�\"Dl\"�=��ApQ���=�q�>�Bh�LCb�h�9�y\\�g)���6�b���C?��lxZ1��oݡ�C���X��A*�ʍ��>b���Y�xavVԙ�De�Q\0'sc8}�CG8���'&XcF��\"�G���?�[tf��#LFr���)��i��H��f����Dyi��1��<枅�\">4��e<Q#�~�Hi��Nt!���\n&Dz.��H���\"��)	���(%\"�0��m�u�Y����z�g&���E�6~[2�b\0U	�\nFE�J�VbJ6�2k�0�Pȑ<]\$KZAs[C�l�3	��g�\r���8.5��H���8�����\$�D�r&}�.k�ܭ�|L�QE&FLI�\"���0kox�#��7D��!V9\rq�����\n�y�[���Z��*�mȔÚ���\\�K�	��dzd�o��*�\"�����y�Q��ٻ0-#��+�'h�]��/��Ȧ�F���Ċ7y(S�\$aM�I\$�\$uMI�+93��/��ʎ��\r9�N��L�!�ː�* ���B�����y�!�4�c��\\��X�8 u�_��6�0��;����O�&:�&.���S�����d�0����0�eE�ѵȗp>��Fa�Gz�	���mP�=t��i���V}��|�}MP(�%��e8�u=6�2�I��q��f�f�^MK����>��В\r����^�7d�vR�m	�I�H�9��>>]s�R� `�s�gU�;��԰q��%�!��8�����r�D�+�,�nx͔5�Ȁ�]�:J�Դ�I�v����Vr�!ͦ�3����Vm)��\$@E`��,�\0�\0���II��؜\0��@s�f��8\0p\0���󁍷\0005\0d\0�pd�\0�\0\0005�2\0�pD�	��\0007�2m���	��\0N%�38������\r�N&��8p|����ۀ\09�f\0���)��NB��\0��`���\0d\0�k4��ȓ��&��;8����󒍷N;\0o8�p�T0s�'	�R\0g8�q��3���\n\0s9Fp<�P���d�)8��9�3��N4�d�9�s����}9�i��g2N��\0�r������\0NH\0i9�s,����L�1\0b\0�t<��3��;Nf��94����S�@\0005��:�p��Ӓg%���\0�p��	��\0Υ�q8�t���3�g�Y�^\0���I�3�g&N��i9:p��`\r p�c� \0�rL����Μ�g8p��I�S��oN\r��8:q��y��f���\0�u�	��g[N��:�q|���S�g\rΔ��:2q����Ӯ��^\0s:�q��I޳�'E\0006��:t	,�������\0�\0�	\$�Y���N�\0i:�w,��s�g\n\09\0i:�q���eP��G\0i8*wl��ls��O2��9q���S�g�N��8�y0i��\0O!�Q8Fq|��ӓ@Ω�=;�u\\���3�gy�Q�\0�q��Y�S��N���:�s4�)��'�4��8�s�	��g��\0i8s	������;�y��	�3�g\\N���9 ��9��'�?\0i;�u�)�3�gi�V��:^{T���3�g\"�c��\0�y����3�'�N��+=\"w\\�yɳ��S΢��=�r|����g�κ�;�q�ީ�g5�o�=2zl������N�q:\"w��	���%OB��;rq��	�s�e�5��:vvL�����f��#:Fu�i峥g�N���8�{4�9�S�'IN �:�x��9���'/�Ü8�|\$�9���J�@��<�x<���3�'DNn!=jq,���bOr��m�t��)��'0+p��8�{d�����%Ͻ�I>nu����s�h\0P��:�	,���s�g��&w>�v�����P\0�7=v���(�9�,\0�xt�y�f���Ϛ�<��y�3������;6�\\�Y�ӣ�O�a9Vut�����(!N��	>{,�ͤq�\$P�Y<vs��i�3Ч�N㞱>r���S�'�ϫ��@~x��	�s�g�π#�A\ns\\�Z�'����?J}녒4��N��9*{��I�s�'Tϛ*�;�rl�y�t�N�!A�vt�9�3�g�N��IBZx��*��EP}�,m��\\��Ӗ(U�L��@�qL�����VΝ��>*����s����r�;^xt���3��3�.��AV�\$�tg�Τ�\rB�L�:�gг�;>�u����΂��9.�|�iȳ�'OZ�Q@�tD�Y��@1P��>���i�s�'iи�8�~u�(eл�)Bʅ����f��'�|��>�vt�Z���K�B��?uE)��h:����:����Ô8g��H��@fx��3�� Ψ*�:����YƓʧ*�_��9�pL���%�P�oCZ�-*��OTn!>.z��y�)��Oh�3:fqD�����=��7C*w���!���O�_DvrT�y���Ӝw9�����s�hM�7�Q;�u����gNʝ;8�M�\$���ZP\0�h\0ƀ��(��'����[>Rt4�	��'����>���-��'�т��<|��z�ԧdO��9<�r���(�\$g^�a��DV~u��3g\\Q@�%@R����S�(,Q�YF�}�T:�[��O��B�rhY�M(��i�)>�{�	��HH�P.�GF&s�i�TXg�OC�F��sب�N�)=�X����Q͠8um�;3��N��?�pL���3�'�Q;��9B���0�g~QJ�5<V���s�h��;�|\\�:����N���E����>�6��\n��>�r}����֫k\0�C����S�'O��9.�����gi�	��Fp\$�y��g�OC��<J�j��@ϋ�/=v����0S��4��:�~t���T&'R��:	abm���gxN[IC4�5Y��h�NO��H�w���;���N���9���**s��;Β�e=&s}�K����I�':\n`I㳓�0��I�zl�:��N��I֎�JI�|�yOϞ@�5��3�'�N�EV�����(�N4��=�{���+3�h�ғ��>:�<������\\QW��<�u��Z�b'�5�Fn��:�v�Ѡ��?	\$���49�%�p�?=�\$��s��jN,�:�������Q��Am��4��Pģ?*q�,�B�0gP���@V|��\nW�R)^�؜I���t�i^��=�t�%�\"4��)Oo�\0��m\n�G��'^O❟>�|�J\n��h�Ѯ��Bړ��zW��'�O��-��9���Q�=8p��yC\r&O̿��/:��Vљ�\0	~�BY�S#e��-��7f�L��P2˦Q	���.V��ɘ�2���&Y��I6\\��ِBfc/ޙ�%Yj�[#��f�͚_�.�8��!ޱ�cfW%��?F6�R%�2a\nښ4�@f)��v����;�k@R\0�L�\0���{6��,�%���e�\r@��y\nV鱁���\0�L������/��=M��c�24��\nanN\$d>�2�̚=4�_�N&\$u8Ə���y.���+.iPVi�1��nŞ��9��l���g�ʁeL�9��E��@o<(B�l���n��\"��_�\nҝ@���mn��`�\n�	�;�pM	�H~͘�i9�T���iU*�n+\0����0Z�OA��-zz���������\rH�4DKc�M��3��D�I�Su\0uO�\nt3��W�0�ftɬ�3��r�\$�����?�jNMѶ 	\0В��5��Vi��4b���T6�0��	�T\nd�PY�c�kQA�1X0����K�T4��\"eB��<T\$�I�S�]V--��]p)�!*�g&M�?��qA�1�fp\0���0	��!�5����R�Xҳ\0jL�(��&i���6+e@!��w!䭒TCRb}P5���v��^����f�̝�<[v�mmL؁���P��}8&#��*8��� ����ډu��\\�5Q�H�#X��7*�`+e�l�V��d�Lŗi8ꑌy�1@�qQʙ�I*�\$��T*c�MٕC�0�Ya�eq�����h���l��[,U��)�GԨaǵ��2\n�칃�)D�^�UE���(j35�YO4l���	f@§+���{6����Î�0\rje�5���Fd�%w^\0���M�^�����'\$��3)P0�9Y��Y\0�P,���-���^���Pd`̷����j�O)b�%R�%Y%���A�9y�5���e��Z\n�OZ��\"���n�MTN��8��5E�\0BԾ.;I�̃)�TF���3;�sP���/8��T��|*rh���(6\$\0�py?~�L\0�Ϫ\r������`��\0�L��LuO���eTB�\$#�0�\0*�0[��U)��-P\\�a�T*��M����(jt2�%M2��Ƈ��`�����U�c&0�m�*��ff#T.`9�L��U<_GL���3�I5&j�B�T�U�, ����&�_�]Q��K���X�Ջ��	�eS�a��*�3fORU�Iv?��aՑf�N�z�F-,�Y�� ��Vv#�Y�<uO!BU���X�TJ��]�rUC'���V�Tz��]�EU�`3V�q=(>Um*��7�7W0��36��i�8UԘ3WReS��ui�!�<c�.]@�d�`X��_�ȝ�L7*���\0G'�F%P�Ot�jׁ��TPmQj��`�ӈ��NF�}_hX�G����h����(v�����S˅��B�U`�A�KK��/�W�r��z����d����=:���V��Ȗ�[����j��H� ��T����J�y�F�Ϊ�}��̏��UW�aW��-�d�����Ĭ���\$%c���[*�2���X��=d��,�*��7b�Uª+(j�������_�Yr�ueʯ�\n���:�mYB	#��L���\\��F�}dZ������Y��Y�˕f�;���=	���Z��em*��h��V�L�h&K5k+���\rj�%e�oU=a��8�����[�\\���۱���W��U��u�k>��6���(�c���C��Y�x��0�k�խ�j��<�{�kG�ۭWv��]��,�*V\\F\rYՉ�^�l�]������lڿ~��A���W���`5�a�4ƪ�[m�%`J��W�V��F�]b�T_+\n/��r��S:ÕM��V!f��3x25S+OV+^�X��e���+V3��~��z�̴k}1���&�nv#���Ug\0�\\5�%qI��+�W��UȬ���Z��W�gYV�&45�*LVg��V�Z��אs�*6`�IRڹK%��e���9Va~�s�ϕ�+C���RZ�S�����ևHZ%���� \$�+{�ޭ�݉�����*��>��Vƺj�����kLU��T˚}i�,��V��\\��t��̄+�W-����}wx3�����׮sZ��l\n�5��D��g%\\�8���Y\nV�_��i�P�t��C��J�Y��/��U�᦯�g!^l��:+ԃ�\0I\0�^�����3W�A�_^�iZ&b���U�+E^���Ba\0�\n��#�^�Z�c2g�ښS{\0�Ę��7D��P/�hum}ѯ2Ȟ\0���|�Ҋ�����5!�����_\"��V��`S��Ђ�59f5�	�Ua*|��A����Z�ף�10��ez����)Će�^ѐ�@X-��k�Y&�á���z���*�3\0�β�-|ʅ��,Th�1Lb�-1����*�\0_^���.f�����fOF���9�QbKX*f�Z� u<z����(�AiON�}�5}����k1:�%q���X��y2�������XGU���Sc��	�X;`�\n��\n����S¬hP�emzg��+x��az��>�d�,/�j'0��m{j�T�l2�Y+Ea��=_k\r�aS���j���X,7,=uj^VΥs<��)YP4��f��;�,<�5+�b>��5��@0���͡��@�n5�&ءd���TsV�V(�(3��X�٭�Ƒ����X��d )���6)ړX��&ò�xv'�a����c��W1,\\�bX�h�ae��@���X�&ocT��)vlШ��W0a_P\0?%P\\��lqV��(ˬ>�=K�+RXب}��<���W�S����ҝ5VʁD�z��'���';k��5T<�&Nx���5*�{�	�r�\n��5�f�,ܙa3��6 %�P~\0	��I�X�e@�DA>���:53q���-��c5	�6�V�٘����l�+�0��eca�͓�\"V>��бQb��5��7�ٚ�/�f3_��;��l�YG�P��3*\nv+���\ni>��t:��V,��@'d�	w\\��YKVO_F��+\n���f�&V��EP+��³�l`L��\"�U��N@+�8����0��j���/����z�R�k��+\n����ʞ�p(7��Y�/sl��v̑6k\n�P��Xɳ8��%��VfI��س?aˡC�J;+��X���V���	���+�*���A3���ڙ�U�=f��z�`����\0��\$�;�aZ��(�Mc��[d���s�X��`?�'�Qv2@(\0W\0�f�c{;�&�s/�?c%S��V\"I��?d��ĭ�{F25+�sT�eP�Q�-�rp��`lWA��WCR��*O��`�2��:�`��0�b�͚ͥ1vok�k��@���u�=�k462C�AʯgT���v[�HĒ�ah��%�:����N�~a:(����AZ06�k��mB����!��jy��:��	���Gn���+@���l���\0�M0�	�I�ͫóʹ�T�C	�RMV��0�aMih����:vh��Za'�^��+IK*vq�A������Gʜ��N�r'�\nӅ�P	��ت׹��ęHͧ{N� �HX��}d�}2�����W0�н��5N���WI�Zs�i�tZ�L�MM���µX�4Ub�l/�8pM2�5�ʜ���V���ev������+,����I'8C��T�l���F@��m��\n�r�8X�Sj� ����X�nh�� ����XB�R�p��MՖg7]��ĮW��%�M�hU<`���4p���1�\\��*�]�k^�cGi]��|	\$�*p0��!Qx�\0ؖHy�w���\0002\0^���Yk���\$�F�K���3�n���,�3lR�ݱ`�@\rm����*�,kd�_�[\0ol��=���-��(J\0�٢��c�I\0�[9^:���g���:�%!^Z���b��Q,[H�Ml��-��i��-�[A�ImNٍ�eV�-�[A�_mr�U�\0\$���[?��l��-�C��m��-����m�Y��š[I��m��m�[i��@/΂��m�ص��n��-��f��m��յKb�������n�͸KqA��À4��l��=�{q6�m��[�7mB�=��s���ۗ�9n�E�;pӲ\0[��-J��pc���m�[D�)n����r��-�R��q<����+f3�-�[¶�<��%��v����۬�����}�Kp��([p��l��e�+r`�[��m��e��z��([���m�e�{��(۸�Yo&�=�y��-�[η�Hn޵�}��-�P��>����k}v�'d[�kl�p��W-�[��oz�=�ˁ6����)o���k���(�\\��l����K�6Ũ�\\�#p�	-�k���l[��)o��c�\"��n\n�(��o���������.��prvE�+�`�O\\&�Wp��U�����n�D��p������\\G�y9���S�.!\\V��:��ěf3��&\\C��p��\n�����T[ڶcB�����\$�n6[���B���\nn/P���q�ߝÀ�f���5�;\"�]�+��܂\0�r�ȫ��gO۸�	q������\r.&��[r0�țj`�\\M�Iq��Y�v�.R\\��Sq��u�K�6�.R\\|X�pn���{�vӮ]\\]�uq��«�W-�,[��ys�m�[��!`F���s*�-Ȼ�74n\\˹}sF���W4nb,���s����3�)��4�>��{�9�/τ��sw\r���.v'�	sn��ۜ��u\\4\0F�z���7�	�~�~��=bp�	��-�A\\�>b�u�v�Dnv�)�)s�v��p7s��\0s��s��}ϫ�W?.b�ƺ;8����;����χ�[s��]�{�w6n�� ��=�m�K�w'���*�t�p���3��@�iu����7P.�]D�yt�,䫤�Ю��ҹo:��+�Ӈn��I��:*��ۜ�.����s��,���P�s�_��u�m��s�.��b��=6�<��7Z.oO��u��m��7\\.��r�=8����k��ۮ��c�u��t���`.{O&�v��j�H�vΣ�u=cX+���.�τ�KvP\r�K�F�i'ݥ����2zW7in�,XO�t��%�z=n��һsvڑ��;�o&��ɻ�v�p�+�Wonڬ���v��m�˔7h�u�*Y=b�D���7q(��Ի_9>�ܻ�Wsn��\r��tB�=�k��E��ݫ4�H���ڙ�h�3����v�{5�;��w.�]���>��u�)��~���8��w�}e�{��O��O�Gu�m�����n�����x>�(0>���P���w:�X,[�N��3^�Cv��*;�w�)��Qx\"x��;�Ww.�^4��w:���K�7jn�^4�_u������w.�^D�!v��U�K��[�\"^��v���K�^.�]~��w���[�Ӈ.�݈��v:���{��d�/���-y~�u��wg�/�\n&Om�����#N�]�;x�5L���3ܯ=ݱvv���ן�f]=Yr������tM/ܡ��w��7�nP݆�Cy�5L��78�Hލ��zv�}�;���o9ޒ�y������=/U��/z�����3�m��S�k^^EoD]��%���%��qפ�\0%����Yƻ��5�X\\Պ��(��\"��0p�3C_�B��|&X^�q�k�6��Y�=��#�+��\0�b��-g��0t�*2����b�ME�{� �2���R��������Y���{����B�k�W|����5�Z���k����r�������G��W\$L�8j���*�\"_sN��Zi�Lv�X�|��K	V'O/_U�N��})3ڕ�H/`A��|ѧ�6f���/b^ƫM{\"��\"��p��^ΫM{@��W�*�^Խ�ٌ3#+鷶��^܅�ȅ}S���*�4g��|���,;��/sW1d\np�-Y{�ui��U��ƿJ�]�\n�U#�E�C�aTJT��Yl\r�}���V֮COxbv�!C\0P��\n��8J֕u&�~Uk��O/�_`����E]�E�w/�Xz']O9T\n�6��+U�ʭ[Xb��k��>o�֏�[:��e�0�Y�BJ��~�?!3��u�\"^�b�Y*���z����u�E�x��E�(]�����;��}V��x*̷�!3�`_!{z�����L��X��}������U�k��)��u�ᕤ�{_�U{��������_�Z�ˌ2�����C#�k]�Z��ְ�/�\0D����3K��ɯ��d�ڟ�8�\$0Ro�ؠ���r������*�k��R����V̨	�V�lnӉ�E������T��bR�}�j��ܙm����^\n�-��u�P1�`R��}MJe��j�#�K4��,���t\r֜:� �k .lO�l�(�8�q���%rY��FO��tN��2W�WT\"uj����}T�@���7k�S�Vw�ڀ姮\\��G��<�I\r��p:/��'�0��s{M�%\"G%:�ڔu��Z�j��Yg���J�>��,��W��T|�&�5�����bX���3���X��Jv�s�TS��I6�M FI&�h�Y��q蒨PY&Ƽx�!I6\n4!��)&�07�Q10� n�ъ7�X�3H�ru\00053��Cڊ��`��@<S_p<�j��\\@��T�Ĩ���t�*�����G�+�P@M{�8C�t����GMV&����ڭ��z�0�@��;�:Ł?�4�Q���a{�\r��H�7˓H��أ`|�uL\"���s��\"\0f���PTlF�G.��\n3�u.X+��TvND��\r�\\̇*tb}�݀�3 l[�����\0c��!a��g�u�e)mF,��Y=Du�-�Ӭq��M����yͅ��7\0�0<��w�nEF'K��>ab]���\n�&䮘Y�|�Zv�p����Z��c�uT���[�.Mx�7����O�+N�k	Ki�:��05��b�\r��w'�2<�A3\\��%60��V�u7�ͦjk�����|w1�r'��um��%@I:d��l\\R��	�5�@֦�p��i���)�5��W�;H���`�Ä0����pǁ��Z�d3���s��*mV�_��:�s���R��i)oDp���ol��qf��H�a�Qbt�x�� �5\0rG���/�_����`����4X��-Ť�9�����<@s;���{� \"�jj�£0�a��h6LFy�%���\n����孾p����8ی^k�:c��V\$�2V'�\r�A?8��X���=���CG����ζ#�c_��='�������@Vc�bB�,��(�\$�I���(J�WF��%\\+����`:�n���#�Gч�1��\"g��m�ba�x{q5�mm����S��؉�8�u��Bh&POxV��p��ݬ&&&��`V�<��Q��*�;�����E]݊N'� �6��T�H�Xv	Ȫ-��aEBv���Q{�������>Ciڝ7�Top�f��#LV�0��H�u�>)�E�����]�4��4���uB:[�p�R*T:0)x��M���=������J��b�#*�쪠8�(@=;V1cSk�P\n`a]g`P��Sh��.�U�q�����X\\:�`��\0v���|��M��`ֱE+����>�_�J�v�Ť\n�V�0l`m����N��*�H��F�H�T���\0��?,b%ұ��-�6\n-ld+ʬ���T/:��^R�f�K:LXBm���r�IvKV,�R��e�pq���D�^&\0Ea&�.�\0��B]#b�������\$�@�6��E���f��o�9�L��,	����M\0SJ���gx҉�cYY_�{P)�\n�y\"�Y-�r]�#8ۖ�cd�4�^3�6!\\�(ˑ��w�א\\�ۀ�捰\rjf�Ɨk`�\0�3l䙅���kI/݇��n�N@:��'�p��\0");}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo"�PNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6��\0\0\0000PLTE\0\0\0���+NvYt�s���������������su�IJ����/.�������C��\0\0\0tRNS\0@��f\0\0\0	pHYs\0\0\0\0\0��\0\0�IDAT8�Ք�N�@��E��l϶��p6�G.\$=���>��	w5r}�z7�>��P�#\$��K�j�7��ݶ����?4m�����t&�~�3!0�0��^��Af0�\"��,��*��4���o�E���X(*Y��	6	�PcOW���܊m��r�0�~/��L�\rXj#�m���j�C�]G�m�\0�}���ߑu�A9�X�\n��8�V�Y�+�D#�iq�nKQ8J�1Q6��Y0�`��P�bQ�\\h�~>�:pSɀ������GE�Q=�I�{�*�3�2�7�\ne�L�B�~�/R(\$�)�� ��HQn�i�6J�	<��-.�w�ɪj�Vm���m�?S�H��v����Ʃ��\0��^�q��)���]��U�92�,;�Ǎ�'p���!X˃����L�D.�tæ��/w����R��	w�d��r2�Ƥ�4[=�E5�S+�c\0\0\0\0IEND�B`�";}exit;}if($_GET["script"]=="version"){$o=get_temp_dir()."/adminer.version";@unlink($o);$q=file_open_lock($o);if($q)file_write_unlock($q,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));@ini_set("session.use_trans_sid",'0');if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",HTTPS,true);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$ad);if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("precision",'15');function
lang($u,$Ff=null){$ua=func_get_args();$ua[0]=$u;return
call_user_func_array('Adminer\lang_format',$ua);}function
lang_format($Yi,$Ff=null){if(is_array($Yi)){$Jg=($Ff==1?0:1);$Yi=$Yi[$Jg];}$Yi=str_replace("'",'’',$Yi);$ua=func_get_args();array_shift($ua);$md=str_replace("%d","%s",$Yi);if($md!=$Yi)$ua[0]=format_number($Ff);return
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
messageQuery($H,$Ki,$Tc=false){restart_session();$Jd=&get_session("queries");if(!idx($Jd,$_GET["db"]))$Jd[$_GET["db"]]=array();if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n…";$Jd[$_GET["db"]][]=array($H,time(),$Ki);$fi="sql-".count($Jd[$_GET["db"]]);$J="<a href='#$fi' class='toggle'>".'SQL command'."</a>\n";if(!$Tc&&($Jj=driver()->warnings())){$t="warnings-".count($Jd[$_GET["db"]]);$J="<a href='#$t' class='toggle'>".'Warnings'."</a>, $J<div id='$t' class='hidden'>\n$Jj</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $J<div id='$fi' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($H,1000)."</code></pre>".($Ki?" <span class='time'>($Ki)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Jd[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
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
const thousandsSeparator = '".js_escape(',')."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon("move","","menu","")."</span>".script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");if($Ma!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ma===false)echo"$N\n";else{echo"<a href='".h($_)."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ma)))echo'<a href="'.h($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> » ';if(is_array($Ma)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> » ';foreach($Ma
as$x=>$X){$Yb=(is_array($X)?$X[1]:h($X));if($Yb!="")echo"<a href='".h(ME."$x=").urlencode(is_array($X)?$X[0]:$X)."'>$Yb</a> » ";}}echo"$Ni\n";}}echo"<h2>$Pi</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);}function
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
input_hidden("fields[$s][orig]",$eg);edit_type("fields[$s]",$m,$jb,$ld);if($U=="TABLE")echo"<td>".checkbox("fields[$s][null]",1,$m["null"],"","","block","label-null"),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Tb>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default")),"<input name='fields[$s][default]' value='".h($m["default"])."' aria-labelledby='label-default'>",(support("comment")?"<td$pb><input name='fields[$s][comment]' value='".h($m["comment"])."' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'>":"");echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ".icon("up","up[$s]","↑",'Move up')." ".icon("down","down[$s]","↓",'Move down')." ":""),($eg==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
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
as$x=>$X){$Hh[]=idf_escape($x);if($xd)$xd[]=idf_escape($x);}}$I=driver()->select($a,$Hh,$Z,$xd,$Zf,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(JUSH=="mssql"&&$D)$I->seek($z*$D);$wc=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&JUSH=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z&&$wd&&$te&&JUSH=="sql")$od=get_val(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".'No rows.'."\n";else{$Ea=adminer()->backwardKeys($a,$vi);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$wd&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$wf=array();$rd=array();reset($M);$eh=1;foreach($L[0]as$x=>$X){if(!isset($pj[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$B=($m?adminer()->fieldName($m,$eh):($X["fun"]?"*":h($x)));if($B!=""){$eh++;$wf[$x]=$B;$d=idf_escape($x);$Nd=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($x);$Yb="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($x))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$qd=apply_sql_function($X["fun"],$B);$Zh=isset($m["privileges"]["order"])||$qd;echo($Zh?"<a href='".h($Nd.($Zf[0]==$d||$Zf[0]==$x||(!$Zf&&$te&&$wd[0]==$d)?$Yb:''))."'>$qd</a>":$qd),"<span class='column hidden'>";if($Zh)echo"<a href='".h($Nd.$Yb)."' title='".'descending'."' class='text'> ↓</a>";if(!$X["fun"]&&isset($m["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($x)."');");echo"</span>";}$rd[$x]=$X["fun"];next($M);}}$Ne=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$x=>$X)$Ne[$x]=max($Ne[$x],min(40,strlen(utf8_decode($X))));}}echo($Ea?"<th>".'Relations':"")."</thead>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($L,$ld)as$uf=>$K){$mj=unique_array($L[$uf],$w);if(!$mj){$mj=array();reset($M);foreach($L[$uf]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$mj[$x]=$X;next($M);}}$nj="";foreach($mj
as$x=>$X){$m=(array)$n[$x];if((JUSH=="sql"||JUSH=="pgsql")&&preg_match('~char|text|enum|set~',$m["type"])&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".(JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($X);}$nj
.="&".($X!==null?urlencode("where[".bracket_escape($x)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($x));}echo"<tr>".(!$wd&&$M?"":"<td>".checkbox("check[]",substr($nj,1),in_array(substr($nj,1),(array)$_POST["check"])).($te||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$nj)."' class='edit'>".'edit'."</a>"));reset($M);foreach($K
as$x=>$X){if(isset($wf[$x])){$d=current($M);$m=(array)$n[$x];$X=driver()->value($X,$m);if($X!=""&&(!isset($wc[$x])||$wc[$x]!=""))$wc[$x]=(is_mail($X)?$wf[$x]:"");$_="";if(preg_match('~blob|bytea|raw|file~',$m["type"])&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($x).$nj;if(!$_&&$X!==null){foreach((array)$ld[$x]as$p){if(count($ld[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$ai)$_
.=where_link($s,$p["target"][$s],$L[$uf][$ai]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$mj))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($mj
as$_e=>$W)$_
.=where_link($s++,$_e,$W);}$Od=select_value($X,$_,$m,$Ji);$t=h("val[$nj][".bracket_escape($x)."]");$Ng=idx(idx($_POST["val"],$nj),bracket_escape($x));$rc=!is_array($K[$x])&&is_utf8($Od)&&$L[$uf][$x]==$K[$x]&&!$rd[$x]&&!$m["generated"];$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$Hi=preg_match('~text|json|lob~',$U);$ue=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($ue&&($X===null||is_numeric(strip_tags($Od))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$rc&&$X!==null)||$Ng!==null){$Ad=h($Ng!==null?$Ng:$K[$x]);echo">".($Hi?"<textarea name='$t' cols='30' rows='".(substr_count($K[$x],"\n")+1)."'>$Ad</textarea>":"<input name='$t' value='$Ad' size='$Ne[$x]'>");}else{$Te=strpos($Od,"<i>…</i>");echo" data-text='".($Te?2:($Hi?1:0))."'".($rc?"":" data-warning='".h('Use edit link to modify this value.')."'").">$Od";}}next($M);}if($Ea)echo"<td>";adminer()->backwardKeysPrint($Ea,$L[$uf]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Jc=true;if($_GET["page"]!="last"){if(!$z||(count($L)<$z&&($L||!$D)))$od=($D?$D*$z:0)+count($L);elseif(JUSH!="sql"||!$te){$od=($te?false:found_rows($S,$Z));if(intval($od)<max(1e4,2*($D+1)*$z))$od=first(slow_query(count_rows($a,$Z,$te,$wd)));else$Jc=false;}}$pg=($z&&($od===false||$od>$z||$D));if($pg)echo(($od===false?count($L)+1:$od-$D*$z)>$z?'<p><a href="'.h(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $z, '".'Loading'."…');",""):''),"\n";echo"<div class='footer'><div>\n";if($pg){$bf=($od===false?$D+(count($L)>=$z?2:1):floor(($od-1)/$z));echo"<fieldset>";if(JUSH!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($bf,$D+5);$s++)echo
pagination($s,$D);if($bf>0)echo($D+5<$bf?" …":""),($Jc&&$od!==false?pagination($bf,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$bf'>".'last'."</a>");}else
echo"<legend>".'Page'."</legend>",pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($bf>$D?pagination($D+1,$D).($bf>$D+1?" …":""):"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$fc=($Jc?"":"~ ").$od;$Sf="const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$fc' : checked); selectCount('selected2', this.checked || !checked ? '$fc' : checked);";echo
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