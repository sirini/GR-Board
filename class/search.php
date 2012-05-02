<?php
class SEARCH
{
	// 검색하고 결과값 반환.
	function totalSearch($searchText, $target, $fromRecord, $endRecord, $searchTarget)
	{
		global $dbFIX;
		//여기 변수들은 GPC로 넘어오는 것이므로 escape 안 함
		$getMyInfo = @mysql_fetch_array(mysql_query('select level from '.$dbFIX.'member_list where no = '.$_SESSION['no']));
		$ml = (!$getMyInfo['level']) ? 1 : $getMyInfo['level'];
		$loopQue = @mysql_query("select id, enter_level, view_level from {$dbFIX}board_list");
		$result = array(array());
		while($list = mysql_fetch_array($loopQue))
		{
			if( $ml < $list['enter_level'] || $ml < $list['view_level'] ) continue;

			if($target != 'subject' && $target != 'content' && $target != 'name')
			{
				$que1 = "b.subject like '%$searchText%' or b.content like '%$searchText%'";
				$que2 = "c.subject like '%$searchText%' or c.content like '%$searchText%'";
				if($searchTarget == 'b') $whereQue = $que1;
				elseif($searchTarget == 'c') $whereQue = $que2;
				else $whereQue = $que1.' or '.$que2;
			}
			else
			{
				if($searchTarget == 'b') $whereQue = "b.{$target} like '%$searchText%'";
				elseif($searchTarget == 'c') $whereQue = "c.{$target} like '%$searchText%'";
				else $whereQue = "b.{$target} like '%$searchText%' or c.{$target} like '%$searchText%'";
			}
			$searchQue = "select b.no, b.subject, b.signdate, c.no as c_no, c.board_no as c_board_no, c.subject as c_subject, c.signdate as c_signdate, '{$list[0]}' as id from {$dbFIX}bbs_{$list[0]} as b";
			$searchQue .= " left join {$dbFIX}comment_{$list[0]} as c on b.no = c.board_no where b.is_secret != 1 and {$whereQue} order by b.signdate desc limit {$fromRecord}, {$endRecord}";
			$queResult = @mysql_query($searchQue);
			if(!$i) $i = 0;
			$targetNo = 0;
			while($data = mysql_fetch_array($queResult))
			{
				if($targetNo == $data['no']) continue;
				$result[$i]['no'] = $data['no'];
				$result[$i]['subject'] = $data['subject'];
				$result[$i]['signdate'] = $data['signdate'];
				$result[$i]['c_no'] = $data['c_no'];
				$result[$i]['c_board_no'] = $data['c_board_no'];
				$result[$i]['c_subject'] = $data['c_subject'];
				$result[$i]['c_signdate'] = $data['c_signdate'];
				$result[$i]['id'] = $data['id'];
				$i++;
				$targetNo = $data['no'];
			}			
		}
		return $result;
	}

	// 총 검색결과값 반환.
	function totalCount($searchText, $target, $searchTarget)
	{
		global $dbFIX;
		/*if(!ini_get('magic_quotes_gpc'))
		{
			$searchText = m.ysql_real_escape_string($searchText);
			$target = m.ysql_real_escape_string($target);
			$searchTarget = m.ysql_real_escape_string($searchText);
		}
		//이 변수들은 GPC로 넘어오는 것이므로 ESCAPE 해제함
		*/
		if($target != 'subject' && $target != 'content' && $target != 'name')
		{
			$que1 = "b.subject like '%$searchText%' or b.content like '%$searchText%'";
			$que2 = "c.subject like '%$searchText%' or c.content like '%$searchText%'";
			if($searchTarget == 'b') $whereQue = $que1;
			elseif($searchTarget == 'c') $whereQue = $que2;
			else $whereQue = $que1.' or '.$que2;
		}
		else
		{
			if($searchTarget == 'b') $whereQue = "b.{$target} like '%$searchText%'";
			elseif($searchTarget == 'c') $whereQue = "c.{$target} like '%$searchText%'";
			else $whereQue = "b.{$target} like '%$searchText%' or c.{$target} like '%$searchText%'";
		}
		$loopQue = @mysql_query("select id from {$dbFIX}board_list");
		$i=0;
		while($list = mysql_fetch_array($loopQue))
		{
			$searchQue = "select b.no from {$dbFIX}bbs_{$list[0]} as b";
			$searchQue .= " left join {$dbFIX}comment_{$list[0]} as c on b.no = c.board_no where (b.is_secret != 1) and {$whereQue}";
			$total = @mysql_num_rows(mysql_query($searchQue));
			$i += $total;
		}
		return $i;
	}

	// 페이징 처리 함수 (검색결과를 페이징, COMMON 용을 개조)
	function getPaging($writePages, $currentPage, $totalPage, $goUrl, $searchOption, $searchText, $searchTarget)
	{
		$str = '';
		$addSearchQue = '&amp;viewCount='.$writePages.'&amp;totalSearchOption='.
			$searchOption.'&amp;totalSearchText='.urlencode($searchText).'&amp;totalSearchTarget='.$searchTarget;
		if($currentPage > 1) $str .= '<a href="'.$goUrl.'1'.$addSearchQue.'" title="처음 페이지로 이동합니다" class="page">First</a>';
		$startPage = (((int)(($currentPage - 1 ) / $writePages )) * $writePages) + 1;
		$endPage = $startPage + $writePages - 1;
		if($endPage >= $totalPage) $endPage = $totalPage;
		if($startPage > 1) $str .= ' &nbsp;<a href="'.$goUrl.($startPage-1).$addSearchQue.'" title="이전 페이지로 이동합니다" class="page">prev</a>';
		if($totalPage > 1)
		{
			for($i=$startPage;$i<=$endPage;$i++)
			{
				if($currentPage != $i) $str .= ' &nbsp;<a href="'.$goUrl.$i.$addSearchQue.'" class="page">'.$i.'</a>';
				else $str .= " &nbsp;<b>{$i}</b> ";
			}
		}
		if($totalPage > $endPage) $str .= ' &nbsp;<a href="'.$goUrl.($endPage+1).$addSearchQue.'" title="다음 페이지로 넘어갑니다" class="page">next</a>';
		if ($currentPage < $totalPage)	 $str .= ' &nbsp;<a href="'.$goUrl.$totalPage.$addSearchQue.'" title="맨 끝 페이지로 이동합니다" class="page">Last</a>';
		$str .= "";
		return $str;
	}
}
?>
