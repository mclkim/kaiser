<?php
/**
 * Program Title : 데이타베이스 페이지 클래스(화면에 필요한 데이타만 리스트 할때 사용)
 * Author : 김명철
 * Create Date : 2014-02-18
 * Description :
 *
 * [1].사용법 정리 필요
 * [2].보다 쉬운 프로그램으로 리빌딩 필요
 * [3].https://github.com/shadowhand/pagination
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
namespace Kaiser\Manager;

// require_once 'DBManager.php';
// require_once 'Pagination.php';
class DBPageManager extends DBManager
{
    private $page_in_url; // 페이지 URL
    private $current_page;
    private $items_per_page;

    public function getLink()
    {
        return $this->page_in_url;
    }

    public function getPage()
    {
        return $this->current_page;
    }

    public function getLimit()
    {
        return $this->items_per_page;
    }

    public function setLink($page_in_url)
    {
        $this->page_in_url = $page_in_url;
    }

    public function setPage($page)
    {
        $this->current_page = $page;
    }

    public function setLimit($limit)
    {
        $this->items_per_page = $limit;
    }

    protected function getNumRowCount($sql, $params = array())
    {
        $realquery = $this->executeEmulateQuery($sql, $params);
        // TODO::다른좋은방법으로
        $sql = 'select count(*)' . $this->stristr($this->stristr($realquery, ' from '), 'order by', true);
        // TODO::mysql 5 이상이면 서브쿼리가 지원되는 가능할것 같기도 하고
        $sql = 'select count(1) from (' . PHP_EOL . $realquery . PHP_EOL . ') t1';
        // $this->info($sql);
        /**
         * TODO::다른좋은방법으로
         * SELECT SQL_CALC_FOUND_ROWS * FROM host_tb LIMIT 1;
         * SELECT FOUND_ROWS();
         */
        return parent::executePreparedQueryOne($sql);
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

    function executePreparedQueryToPage($sql, $params = array())
    {
        try {
            $config = array(
                'total_items' => $this->getNumRowCount($sql, $params),
                'items_per_page' => $this->items_per_page,
                'current_page' => array(
                    'page' => $this->current_page
                )
            );

            // (Before) Doing...
            $paging = new Pagination ($config);
            // var_dump($paging);exit;
            // (After) Doing...
        } catch (Exception $e) {
            throw $e;
        }

        // 하단 페이지 번호들
        $pages = array();
        for ($ii = 1; $ii <= $paging->total_pages; $ii++) {
            $pages [$ii] = 'page=' . $ii;
        } // end for

        // Number of page links in the begin and end of whole range
        $count_out = (!empty ($config ['count_out'])) ? ( int )$config ['count_out'] : 1;
        // Number of page links on each side of current page
        $count_in = (!empty ($config ['count_in'])) ? ( int )$config ['count_in'] : 1;

        // Beginning group of pages: $n1...$n2
        $n1 = 1;
        $n2 = min($count_out, $paging->total_pages);

        // Ending group of pages: $n7...$n8
        $n7 = max(1, $paging->total_pages - $count_out + 1);
        $n8 = $paging->total_pages;

        // Middle group of pages: $n4...$n5
        $n4 = max($n2 + 1, $paging->current_page - $count_in);
        $n5 = min($n7 - 1, $paging->current_page + $count_in);
        $use_middle = ($n5 >= $n4);

        // Point $n3 between $n2 and $n4
        $n3 = ( int )(($n2 + $n4) / 2);
        $use_n3 = ($use_middle && (($n4 - $n2) > 1));

        // Point $n6 between $n5 and $n7
        $n6 = ( int )(($n5 + $n7) / 2);
        $use_n6 = ($use_middle && (($n7 - $n5) > 1));

        // Links to display as array(page => content)
        $links = array();

        // Generate links data in accordance with calculated numbers
        for ($i = $n1; $i <= $n2; $i++) {
            $links [$i] = $i;
        }
        if ($use_n3) {
            $links [$n3] = '&hellip;';
        }
        for ($i = $n4; $i <= $n5; $i++) {
            $links [$i] = $i;
        }
        if ($use_n6) {
            $links [$n6] = '&hellip;';
        }
        for ($i = $n7; $i <= $n8; $i++) {
            $links [$i] = $i;
        }
        // var_dump($links);exit;
        return array(
            'link_url' => $this->page_in_url, // 페이지 이동 url
            'total_items' => $paging->total_items,
            'items_per_page' => $paging->items_per_page,
            'total_pages' => $paging->total_pages,
            'current_page' => $paging->current_page,
            'current_first_item' => $paging->current_first_item,
            'current_last_item' => $paging->current_last_item,
            'previous_page' => $paging->previous_page,
            'next_page' => $paging->next_page,
            'first_page' => $paging->first_page,
            'last_page' => $paging->last_page,
            'offset' => $paging->offset,
            'pages' => $paging, // 출력할 페이지
            'links' => $links  // 출력할 페이지
        );
    }

    function executePreparedQueryToPageMapList($sql, $params = array())
    {

        $page = self::executePreparedQueryToPage($sql, $params);

        $sql = $sql . ' limit ' . $page ['current_first_item'] . ',' . $page ['items_per_page'];
        $list = self::executePreparedQueryToMapList($sql, $params);

        return array(
            'list' => $list,
            'page' => $page
        );
    }
}