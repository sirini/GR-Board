<?php
// 데이터베이스 인터페이스 클래스; 사용 전 DB 연결 필요함; PHP4 & PHP5 호환; @sirini
class DATABASE
{
	// 필드값 가져오기 @sirini
	function getField($table)
	{
		$resultQue = mysql_query('show fields from '.$table);
		$que = "";
		while($fieldData = mysql_fetch_array($resultQue))
		{
			$fieldData['Null'] = ($fieldData['Null'] == "YES")?' null':' not null';
			$fieldData['Default'] = $fieldData['Default']?' default \''.$fieldData['Default'].'\'':'';
			$fieldData['Key'] = ($fieldData['Key'] == "PRI")?' primary key':'';
			if($fieldData['Extra']) $fieldData['Extra'] = " ".$fieldData['Extra'];
			$que .= ' '.$fieldData['Field']." ".$fieldData['Type'].$fieldData['Null'].$fieldData['Default'].$fieldData['Extra'].$fieldData['Key'].',';
		}
		return $que;
	}

	// 키 값 가져오기 @sirini
	function getKey($table)
	{
		$resultQue = mysql_query("show keys from {$table}");
		$que = '';
		$hiddenName = '';
		$primaryQue = '';
		while($keyData = mysql_fetch_array($resultQue))
		{
			if($keyData['Key_name'] != 'PRIMARY')
			{
				if($hiddenName != $keyData['Key_name'])
				{
					if($hiddenName) $que .= "),";
					$que .= ' KEY '.$keyData['Key_name'].' ('.$keyData['Column_name'];
					$hiddenName = $keyData['Key_name'];
				}
				else
				{
					if($hiddenName)
					{
						$que .= ','.$keyData['Column_name'];
					}
				}
			}
		}
		if($hiddenName && ($hiddenName==$keyData['Key_name'])) $que .= '),';
		return $que;
	}

	// 스키마 가져오기 @sirini
	function getSchema($table)
	{
		$field = $this->getField($table);
		$key = $this->getKey($table);
		$schema = $field.$key;
		$schema = 'create table '.$table.' ('.$schema.')) TYPE=MyISAM;'."\n";
		echo $schema;
	}

	// 와일드카드 처리 @sirini
	function sqlWildString($str)
	{
		$str = str_replace('\\', '\\\\', $str);
		$str = str_replace("\n", '\n', $str);
		$str = str_replace("\r", '\r', $str);
		$str = str_replace("\t", '\t', $str);
		return $str;
	}

	// 데이터 가져오기 @sirini
	function getData($table)
	{
		$resultQue = mysql_query('show fields from '.$table);
		while($fieldData = mysql_fetch_array($resultQue))
		{
			if(!$field) $field = '';
			$field .= $fieldData['Field'].',';
		}		
		$field = substr($field, 0, strlen($field)-1);
		$tmpArr = explode(',', $field);
		$tmpArrSize = count($tmpArr);
		$resultQueTable = mysql_query('select '.$field.' from '.$table);
		while($saveData = mysql_fetch_array($resultQueTable))
		{
			if(!$value) $value = '';
			for($i=0; $i<$tmpArrSize; $i++)
			{
				$value .= "'".$saveData[$tmpArr[$i]]."',";
			}
			$value = substr($value, 0, strlen($value)-1);
			$value = $this->sqlWildString($value);
			echo 'insert into '.$table.' values('.$value.');'."\n";
			unset($value);
		}
	}

	// 모두 받기 @sirini
	function allDown($dbName)
	{
		$resultQue = mysql_query("show table status from {$dbName} like '{$dbFIX}%'");
		while($dbData = mysql_fetch_array($resultQue))
		{
			$this->getSchema($dbData['Name']);
			$this->getData($dbData['Name']);
		}
	}

	// 다운로드 헤더설정 @sirini
	function dbHeader($file)
	{
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$file.'"');
		header('Expires: 0');
		if(preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT'])) header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	}
	
	// MySQL 41바이트 암호화를 php로 구현 php.net
	function password($password,$option=''){
    	if($password===null)return null;
    	if(strlen($password)==0)return '';
    	$r=sha1(sha1($password,true));
    	if($option) return $r; else return '*'.strtoupper($r);
	}
	
	// MySQL 16바이트 암호화를 php로 구현 phpschool.com
	function old_password($password) {
    	$nr = 1345345333;
    	$add = 7;
    	$nr2 = 0x12345671;
    	$size = strlen($password);
    	for($i=0;$i<$size;$i++) {
    		if($password[$i] == ' ' || $password[$i] == '\t') continue; /* skipp space in password */
    		$tmp = ord($password[$i]);
    		$nr ^= ((($nr & 63)+$add)*$tmp) + ($nr << 8);
    		$nr2 += ($nr2 << 8) ^ $nr;
    		$add += $tmp;
    		}
    	$result1=$nr & ((1 << 31) -1); /* Don't use sign bit (str2int) */
    	$result2=$nr2 & ((1 << 31) -1);
    	$result = sprintf("%08x%08x",$result1,$result2);
    	return $result;
    }
}
?>