<?php

namespace Kaiser\Manager;

    /**
     * Program Title : 데이타베이스 페이지 클래스(화면에 필요한 데이타만 리스트 할때 사용)
     * Author : 김명철
     * Create Date : 2014-02-18
     * Description :
     *
     * [1].사용법 정리 필요
     * [2].보다 쉬운 프로그램으로 리빌딩 필요
     * [3].https://github.com/stefangabos/Zebra_Pagination
     *
     * ---------------------------------------------------------------
     * Development / Modification History
     * ---------------------------------------------------------------
     * Date / Author / Desciption / Transport
     * ---------------------------------------------------------------
     * YYYY/MM/DD
     * ---------------------------------------------------------------
     */
    /**
     * (예시)
     * <code>
     * $page = $this->getParameter ( 'page', 1 );
     * $DB = new DBPageManager ( 'testboard' );
     * $query = "select * from board";
     * $DB->setLink ( '?link_url' );
     * $DB->setLimit ( 15 );
     * $DB->setPage ( $page );
     * $boardList = $DB->executePreparedQueryToPageMapList ( $query );
     * </code>
     */

/**
 * $params 설명 (필수옵션)
 *
 * curPageNum : 현재 페이지의 값을 넘겨 줍니다.
 * pageVar : 페이지 링크에 사용할 변수명(ex page,pagenum)
 * extraVar : 페이지 링크에 추가적으로 같이 넘길 변수 link를 기입(ex "&opt1=10&opt2=가나다")
 * totalItem : 데이타베이스에 읽어들인 글(아이템)의 총 수
 * perPage : 페이지 리스트 링크에 몇개씩 리스트를 뿌릴 것인지 (ex 5 설정하면 페이지리스트에 1 2 3 4 5가 나옴)
 * perItem : 한페이지에 뿌려지는 글(아이템)의 수(실제 리스팅은 select 쿼리에서 하시고 이옵션은 페이지 계산용)
 * prevPage : "이전" 링크에 사용할 문구나 이미지 태그 미설정시 "이전"이 출력
 * nextPage : "다음" 링크에 사용할 문구나 이미지 태그 미설정시 "다음"이 출력
 * prevPerPage : "이전10개" 링크에 사용할 문구나 이미지 태그 미설정시 출력 안됨
 * nextPerPage : "다음10개" 링크에 사용할 문구나 이미지 태그 미설정시 출력 안됨
 * firstPage : "처음" 링크에 사용할 문구나 이미지 태그 미설정시 출력 안됨
 * lastPage : "마지막" 링크에 사용할 문구나 이미지 태그 미설정시 출력 안됨
 * pageCss : 페이지 목록 링크에서 사용할 스타일 시트
 * curPageCss : 페이지 목록 링크 중 현재 페이지 번호에서 사용할 스타일 시트
 *
 * @author Administrator
 */
class DBPageManager extends DBManager
{
    public static $conf;
    // var $maxpages = 5; // 화면에 출력할 페이지 갯수
    // var $limit = 5; // 페이지당 게시물의 갯수
    // var $numrows = 0; // 쿼리한 갯수
    var $page = 1; // 선택한 페이지 번호
    var $prev_limit = 0; // 시작페이지의 시작게시물
    var $next_limit = 1; // 다음페이지의 시작게시물

    function __construct($connection, $conf = array())
    {
        parent::__construct($connection);
        self::$conf = array_merge(array(
            'link_url' => null,
            'perPage' => 5,
            'perItem' => 5,
            'page' => 1
        ), $conf);
    }

    // int $from 시작 페이지
    // int $limit 페이지당 게시물의 갯수
    // int $numrows 쿼리한 갯수
    // int $maxpages 화면에 출력할 페이지 갯수 (생략하면 모든 페이지)
    function executePreparedQueryToPage($sql, $params = null)
    {
        // TODO::다른좋은방법으로
        // 쿼리실행 후 데이타 갯수 확인
// 		$numrows = $this->getNumRows ( $sql, $params );
        $numrows = $this->getNumRowCount($sql, $params);

        // 마지막페이지&&현재페이지
        $numpages = max(1, ceil($numrows / self::$conf ['perItem']));
        $current = min(max(1, self::$conf ['page']), $numpages);

        // 시작페이지&&다음페이지
        $from = max(1, $current - 1);
        $to = min($current + 1, $numpages);

        // 시작페이지의 시작게시물&&다음페이지의 시작게시물
        $prev = max(1, $current - (($current - 1) % self::$conf ['perPage']));
        $next = min($prev + self::$conf ['perPage'] - 1, $numpages);

        // 현재페이지기준으로 시작게시물&&종료게시물
        $this->prev_limit = max(($current - 1) * self::$conf ['perItem'], 0);
        $this->next_limit = min($current * self::$conf ['perItem'], $numrows);

        $this->current_first_item = $this->prev_limit + 1;
        $this->current_last_item = $this->next_limit;

        $this->first_page = ($current === 1) ? FALSE : 1;
        $this->last_page = ($current >= $numpages) ? FALSE : $numpages;
        $this->offset = ( int )(($current - 1) * self::$conf ['perItem']);

        // 하단 페이지 번호들
        $pages = array();
        $links = array();
        for ($ii = $prev; $ii <= $next; $ii++) {
            $pages [$ii] = 'page=' . $ii;
            $links [$ii] = $ii;
        } // end for
        /*
                return array(
                    'link_url' => self::$conf ['link_url'], // 페이지 이동 url
                    'max_line' => self::$conf ['perItem'], // 보여줄 데이터수
                    'max_page' => self::$conf ['perPage'], // 보여줄 페이지 수
                    'current' => $current, // 현제 페이지
                    'numrows' => $numrows, // 쿼리한 갯수
                    'prev' => max($prev - 1, 1), // 이전 페이지 값
                    'from' => $from, // 쿼리한 시작 페이지 값
                    'to' => $to, // 쿼리한 마지막 페이지 값
                    'next' => min($next + 1, $numpages), // 다음 페이지 값
                    'remain' => 0, // 나머지 값
                    'numpages' => $numpages, // 전체 페이지 값
                    'firstpage' => 1, // 처음 페이지의 값
                    'lastpage' => $numpages, // 마지막 페이지의 값
                    'pages' => $pages
                ); // 출력할 페이지
        */
        return array(
            'link_url' => self::$conf ['link_url'], // 페이지 이동 url
            'total_items' => $numrows,
            'items_per_page' => self::$conf ['perItem'], // 보여줄 데이터수
            'total_pages' => $numpages,
            'current_page' => $current,
            'current_first_item' => $this->current_first_item,
            'current_last_item' => $this->current_last_item,
            'previous_page' => $prev,
            'next_page' => $next,
            'first_page' => $this->first_page,
            'last_page' => $this->last_page,
            'offset' => $this->offset,
            'pages' => $pages, // 출력할 페이지
            'links' => $links, // 출력할 페이지
        );
    }

    protected function getNumRowCount($sql, $params = null)
    {
        $realquery = $this->executeEmulateQuery($sql, $params);
        // TODO::다른좋은방법으로
        $sql = 'select count(*)' . $this->stristr($this->stristr($realquery, ' from '), 'order by', true);
        // TODO::mysql 5 이상이면 서브쿼리가 지원되는 가능할것 같기도 하고
        $sql = 'select count(1) from (' . PHP_EOL . $realquery . PHP_EOL . ') t1';
        // Logger::info($sql);
        /**
         * TODO::다른좋은방법으로
         * SELECT SQL_CALC_FOUND_ROWS * FROM host_tb LIMIT 1;
         * SELECT FOUND_ROWS();
         */
        return parent::executePreparedQueryOne($sql);
    }

    protected function getLastQueryRowCount($sql, $params = null)
    {
// 		$lastQuery = $this->lastQuery;
        $lastQuery = $this->executeEmulateQuery($sql, $params);

        $commandBeforeTableName = null;
        if (stripos($lastQuery, 'FROM') !== false)
            $commandBeforeTableName = 'FROM';
        if (stripos($lastQuery, 'UPDATE') !== false)
            $commandBeforeTableName = 'UPDATE';

        $after = substr($lastQuery, stripos($lastQuery, $commandBeforeTableName) + (strlen($commandBeforeTableName) + 1));
        $table = substr($after, 0, stripos($after, ' '));

        $wherePart = '';
        if (stripos($lastQuery, 'WHERE') !== false)
            $wherePart = substr($lastQuery, stripos($lastQuery, 'WHERE'));

// 		$result = parent::query ( "SELECT COUNT(*) FROM $table " . $wherePart );
//        var_dump($lastQuery);
//        var_dump($after);
//        var_dump($table);
//        var_dump($wherePart);
//        exit;

        return parent::executePreparedQueryOne("SELECT COUNT(*) FROM $table " . $wherePart);

// 		if ($result == null)
// 			return 0;
// 		return $result->fetchColumn ();
    }

    function executePreparedQueryToPageMapList($sql, $params = null)
    {
        $page = self::executePreparedQueryToPage($sql, $params);

        $sql = $sql . ' limit ' . $this->prev_limit . ',' . self::$conf ['perItem'];
        $list = self::executePreparedQueryToMapList($sql, $params);
        return array(
            'list' => $list,
            'page' => $page
        );
    }

    private function stristr($haystack, $needle, $before_needle = FALSE)
    {
        if (($pos = strpos(strtolower($haystack), strtolower($needle))) === FALSE)
            return FALSE;

        if ($before_needle)
            return substr($haystack, 0, $pos);
        else
            return substr($haystack, $pos);
    }

    function setBlockCount($value)
    {
        self::$conf ['perPage'] = $value;
    }

    function setPage($page)
    {
        self::$conf ['page'] = $page;
    }

    function setLimit($limit)
    {
        self::$conf ['perItem'] = $limit;
    }

    function getLimit()
    {
        return self::$conf ['perItem'];
    }

    function setLink($link)
    {
        self::$conf ['link_url'] = $link;
    }

    function pageLayout($pages = null)
    {
        ob_start();

        echo <<<HEAD
<div class="page_list_wrap">
	<div class="page_list">

		<div class="current_bt01">
			<a href="$pages[link_url]&page=$pages[firstpage]" page="$pages[firstpage]" class="first"> <img src="/_tpl/images/page_first.gif" alt="처음" /></a>
			<a href="$pages[link_url]&page=$pages[prev]" page="$pages[prev]" class="prev"> <img src="/_tpl/images/page_before.gif" alt="다음" /></a>
		</div>

		<ul class="current-page">
HEAD;
        foreach ($pages ['pages'] as $key => $value) {
            if ($pages ['current'] == $key) {
                echo '<li><a href="' . $pages ['link_url'] . '&' . $value . '" page="' . $key . '" class="current_page_active">' . $key . '</a>' . PHP_EOL;
                echo '<div style="display: block"></div></li>' . PHP_EOL;
            } else {
                echo '<li><a href="' . $pages ['link_url'] . '&' . $value . '" page="' . $key . '" >' . $key . '</a>' . PHP_EOL;
                echo '<div></div></li>' . PHP_EOL;
            }
        }

        echo <<<END
		</ul>

		<div class="current_bt02">
			<a href="$pages[link_url]&page=$pages[next]" page="$pages[next]" class="next"> <img src="/_tpl/images/page_next.gif" alt="다음" /></a>
			<a href="$pages[link_url]&page=$pages[lastpage]" page="$pages[lastpage]" class="end"> <img src="/_tpl/images/page_last.gif" alt="맨뒤" /></a>
		</div>

	</div>
</div>
END;

        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}
